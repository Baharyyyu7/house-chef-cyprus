<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class ThumbnailGenerator
{
    /** @return array<string, string> */
    public function generate(Media $media): array
    {
        if ($media->path === null || ! in_array($media->mime_type, config('security.allowed_media_mimes'), true)) {
            throw new RuntimeException('MEDIA_SOURCE_UNAVAILABLE');
        }
        $disk = Storage::disk($media->disk);
        $contents = $disk->get($media->path);
        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('MEDIA_SOURCE_INVALID');
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $paths = [];
        foreach (['small' => 320, 'medium' => 768, 'large' => 1440] as $name => $maxWidth) {
            $targetWidth = min($width, $maxWidth);
            $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            ob_start();
            imagejpeg($canvas, null, 84);
            $encoded = ob_get_clean();
            imagedestroy($canvas);
            if ($encoded === '') {
                imagedestroy($source);
                throw new RuntimeException('MEDIA_THUMBNAIL_FAILED');
            }
            $path = 'media/thumbnails/'.$media->public_id.'/'.$name.'.jpg';
            $disk->put($path, $encoded, ['visibility' => $media->visibility]);
            $paths[$name] = $path;
        }
        imagedestroy($source);

        return $paths;
    }
}
