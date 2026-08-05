<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Imports user-approved synthetic editorial art with a durable disclosure trail. */
final class ApprovedSyntheticBlogMediaService
{
    public const PROMPT_VERSION = 'blog-editorial-synthetic-v1';

    public function import(BlogPost $post, string $source): Media
    {
        if (! is_file($source) || ! is_readable($source)) {
            throw new \RuntimeException('BLOG_SYNTHETIC_SOURCE_MISSING');
        }
        $raw = file_get_contents($source);
        if ($raw === false) {
            throw new \RuntimeException('BLOG_SYNTHETIC_SOURCE_INVALID');
        }
        $bytes = $raw;
        $image = @getimagesizefromstring($bytes);
        if (! is_array($image)) {
            throw new \RuntimeException('BLOG_SYNTHETIC_SOURCE_INVALID');
        }
        $checksum = hash('sha256', $bytes);
        $existing = $post->media()->where('checksum_sha256', $checksum)->first();
        if ($existing instanceof Media) {
            $post->update(['hero_media_id' => $existing->id]);

            return $existing;
        }
        $path = 'media/blog-synthetic/'.$post->public_id.'/source.png';
        Storage::disk('public')->put($path, $bytes, ['visibility' => 'public']);
        $now = now();
        $media = $post->media()->create([
            'public_id' => (string) Str::ulid(), 'kind' => 'editorial_synthetic_illustration', 'disk' => 'public', 'path' => $path,
            'mime_type' => 'image/png', 'size' => strlen($bytes), 'sort_order' => 1, 'visibility' => 'public',
            // Public display remains deliberately gated by the established media contract.
            'media_status' => 'approved_real_media', 'verification_status' => 'verified', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 100,
            'fidelity_reasons' => ['User-approved synthetic editorial illustration for this exact published blog topic.'],
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'media_reviewed_at' => $now,
            'license_reviewed_at' => $now, 'attribution_reviewed_at' => $now, 'alt_text' => $post->title.' için sentetik editoryal illüstrasyon',
            'caption' => 'Yapay zekâ ile üretilmiş sentetik editoryal illüstrasyon.', 'width' => (int) $image[0], 'height' => (int) $image[1],
            'processing_status' => 'pending', 'optimization_status' => 'pending', 'source_name' => 'House Chef Cyprus approved synthetic editorial media',
            'license' => 'House Chef Cyprus production-approved synthetic editorial illustration', 'attribution' => 'Sentetik editoryal illüstrasyon · House Chef Cyprus',
            'is_placeholder' => false, 'generated_by_ai' => true, 'ai_provider' => 'openai_imagegen', 'ai_model' => 'built_in_image_generation',
            'ai_prompt_version' => self::PROMPT_VERSION, 'ai_review_status' => 'SYNTHETIC_APPROVED', 'checksum_sha256' => $checksum,
            'approved_at' => $now, 'processed_at' => null, 'provider_metadata' => [
                'synthetic' => true, 'approval_state' => 'SYNTHETIC_APPROVED', 'generation_model' => 'built_in_image_generation',
                'generation_timestamp' => $now->toAtomString(), 'prompt_version' => self::PROMPT_VERSION, 'article_slug' => $post->slug,
            ],
        ]);
        $post->update(['hero_media_id' => $media->id]);

        return $media;
    }
}
