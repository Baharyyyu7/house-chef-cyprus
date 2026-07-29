<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AiImageGeneration;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Stores validated provider bytes privately; it never makes generated media public. */
final class PrivateAiImageMediaStore
{
    public function store(AiImageGeneration $generation, ProviderImageResult $result): Media
    {
        if (! $result->hasImage()) {
            throw new \LogicException('Only validated provider image bytes can be stored.');
        }
        $image = @imagecreatefromstring($result->bytes);
        if ($image === false) {
            throw new \RuntimeException('IMAGE_PROVIDER_REENCODE_FAILED');
        }
        $filename = (string) Str::uuid().'.webp';
        ob_start();
        imagewebp($image, null, 88);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);
        if ($bytes === '' || @getimagesizefromstring($bytes) === false) {
            throw new \RuntimeException('IMAGE_PROVIDER_REENCODE_FAILED');
        }
        $dimensions = getimagesizefromstring($bytes);
        $path = 'media/ai-pilot/'.$generation->recipe->public_id.'/'.$filename;
        Storage::disk('local')->put($path, $bytes);

        return $generation->recipe->media()->create([
            'public_id' => (string) Str::ulid(), 'kind' => 'image', 'disk' => 'local', 'path' => $path,
            'mime_type' => 'image/webp', 'size' => strlen($bytes), 'sort_order' => 99, 'visibility' => 'private',
            'alt_text' => $generation->recipe->title.' için inceleme bekleyen AI görsel taslağı',
            'width' => $dimensions[0], 'height' => $dimensions[1], 'processing_status' => 'ready', 'thumbnail_status' => 'not_requested',
            'source_name' => ucfirst($generation->provider).' controlled comparison', 'license' => 'Provider output; House Chef Cyprus internal review required',
            'attribution' => 'AI-generated; manual review required', 'verification_status' => 'pending_review', 'confidence' => 0,
            'generated_by_ai' => true, 'ai_provider' => $generation->provider, 'ai_model' => $generation->model, 'ai_prompt_version' => $generation->prompt_version,
            'ai_review_status' => 'pending_review', 'checksum_sha256' => hash('sha256', $bytes), 'perceptual_hash' => $this->perceptualHash($bytes),
            'provider_metadata' => ['storage' => 'private_pending_review', 'reencoded' => true, 'source_mime_type' => $result->mime],
        ]);
    }

    private function perceptualHash(string $bytes): string
    {
        $source = imagecreatefromstring($bytes);
        $thumb = imagecreatetruecolor(8, 8);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, 8, 8, imagesx($source), imagesy($source));
        $values = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $rgb = imagecolorat($thumb, $x, $y);
                $values[] = (($rgb >> 16) & 0xFF) + (($rgb >> 8) & 0xFF) + ($rgb & 0xFF);
            }
        }
        imagedestroy($source);
        imagedestroy($thumb);
        $average = array_sum($values) / count($values);

        return implode('', array_map(fn (int $value): string => $value >= $average ? '1' : '0', $values));
    }
}
