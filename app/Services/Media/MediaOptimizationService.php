<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

/** Generates only deterministic derivatives for approved local media. */
final class MediaOptimizationService
{
    /** @return array{processed:int,skipped:int,failed:int} */
    public function optimize(int $limit = 10): array
    {
        $media = Media::query()->where('media_status', 'approved_real_media')->where('visibility', 'public')
            ->whereNotIn('optimization_status', ['ready', 'provider_managed'])->orderBy('id')->limit($limit)->get();
        $result = ['processed' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($media as $item) {
            if ($item->kind === 'external_hotlinked_media') {
                $item->update(['optimization_status' => 'provider_managed']);
                $result['skipped']++;

                continue;
            }
            try {
                $this->generate($item);
                $result['processed']++;
            } catch (\Throwable) {
                $item->update(['optimization_status' => 'failed', 'processing_error_code' => 'MEDIA_OPTIMIZATION_FAILED', 'processing_error_message' => 'Görsel varyasyonları güvenli biçimde üretilemedi.']);
                $result['failed']++;
            }
        }

        return $result;
    }

    private function generate(Media $media): void
    {
        if ($media->path === null || ! Storage::disk($media->disk)->exists($media->path)) {
            throw new \RuntimeException('MEDIA_SOURCE_UNAVAILABLE');
        }
        $source = @imagecreatefromstring(Storage::disk($media->disk)->get($media->path));
        if ($source === false) {
            throw new \RuntimeException('MEDIA_SOURCE_INVALID');
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $variants = [];
        foreach ((array) config('media_excellence.recipe_profiles') as $name => $dimensions) {
            [$targetWidth, $targetHeight] = array_map('intval', (array) $dimensions);
            if ($targetWidth < 1 || $targetHeight < 1) {
                continue;
            }
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
            [$sourceX, $sourceY, $cropWidth, $cropHeight] = $this->crop($media, $width, $height, $targetWidth, $targetHeight);
            imagecopyresampled($canvas, $source, 0, 0, $sourceX, $sourceY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);
            $base = 'media/optimized/'.$media->public_id.'/'.$targetWidth;
            $jpeg = $base.'.jpg';
            ob_start();
            imagejpeg($canvas, null, (int) config('media_excellence.quality.jpeg'));
            $jpegBytes = ob_get_clean();
            if (! is_string($jpegBytes) || $jpegBytes === '') {
                imagedestroy($canvas);
                imagedestroy($source);

                throw new \RuntimeException('MEDIA_JPEG_FAILED');
            }
            Storage::disk('public')->put($jpeg, $jpegBytes, ['visibility' => 'public']);
            $variants[(string) $name] = ['name' => (string) $name, 'jpeg' => $jpeg, 'width' => $targetWidth, 'height' => $targetHeight];
            if (function_exists('imagewebp')) {
                $webp = $base.'.webp';
                ob_start();
                imagewebp($canvas, null, (int) config('media_excellence.quality.webp'));
                $webpBytes = ob_get_clean();
                if (is_string($webpBytes) && $webpBytes !== '') {
                    Storage::disk('public')->put($webp, $webpBytes, ['visibility' => 'public']);
                    $variants[(string) $name]['webp'] = $webp;
                }
            }
            if (function_exists('imageavif')) {
                $avif = $base.'.avif';
                ob_start();
                imageavif($canvas, null, (int) config('media_excellence.quality.avif'));
                $avifBytes = ob_get_clean();
                if (is_string($avifBytes) && $avifBytes !== '') {
                    Storage::disk('public')->put($avif, $avifBytes, ['visibility' => 'public']);
                    $variants[(string) $name]['avif'] = $avif;
                }
            }
            imagedestroy($canvas);
        }
        imagedestroy($source);
        $media->update(['responsive_variants' => $variants, 'optimization_status' => 'ready', 'processing_status' => 'ready', 'health_scanned_at' => now(), 'health_rule_version' => config('media_excellence.health_rule_version')]);
    }

    /** @return array{int,int,int,int} */
    private function crop(Media $media, int $width, int $height, int $targetWidth, int $targetHeight): array
    {
        $sourceRatio = $width / max(1, $height);
        $targetRatio = $targetWidth / max(1, $targetHeight);
        if ($sourceRatio > $targetRatio) {
            $cropHeight = $height;
            $cropWidth = max(1, (int) round($height * $targetRatio));
        } else {
            $cropWidth = $width;
            $cropHeight = max(1, (int) round($width / $targetRatio));
        }
        $focalX = max(0.0, min(1.0, (float) ($media->focal_point_x ?? 0.5)));
        $focalY = max(0.0, min(1.0, (float) ($media->focal_point_y ?? 0.5)));
        $sourceX = max(0, min($width - $cropWidth, (int) round(($width * $focalX) - ($cropWidth / 2))));
        $sourceY = max(0, min($height - $cropHeight, (int) round(($height * $focalY) - ($cropHeight / 2))));

        return [$sourceX, $sourceY, $cropWidth, $cropHeight];
    }
}
