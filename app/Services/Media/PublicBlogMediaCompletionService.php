<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\BlogPost;
use App\Models\Media;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Read-only evidence collection for published blog media. It never treats a
 * branded fallback, a candidate, or a local file as public verification.
 */
final class PublicBlogMediaCompletionService
{
    /** @return array<string, mixed> */
    public function audit(bool $probePublicUrls = false): array
    {
        $posts = BlogPost::query()->publiclyVisible()
            ->with(['category', 'author', 'recipes', 'heroMedia'])
            ->orderBy('id')
            ->get();
        $duplicateKeys = $this->duplicateKeys($posts->pluck('heroMedia')->filter());
        $rows = $posts->map(fn (BlogPost $post): array => $this->row($post, $duplicateKeys, $probePublicUrls));

        return [
            'generated_at' => now()->toIso8601String(),
            'published_blogs_audited' => $rows->count(),
            'classifications' => $rows->countBy('classification')->all(),
            'approved_media' => $rows->where('classification', 'READY')->count(),
            'gradient_placeholders' => $rows->where('classification', 'GRADIENT_PLACEHOLDER')->count(),
            'broken_public_urls' => $rows->where('public_url_broken', true)->count(),
            'auto_approve' => false,
            'auto_publish' => false,
            'human_review_required' => true,
            'blogs' => $rows->values()->all(),
        ];
    }

    /** @param iterable<Media> $media @return array<string, true> */
    private function duplicateKeys(iterable $media): array
    {
        $counts = [];
        foreach ($media as $item) {
            $key = $item->checksum_sha256 ?: ($item->path ? 'path:'.$item->disk.':'.$item->path : null);
            if ($key !== null) {
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        return collect($counts)->filter(fn (int $count): bool => $count > 1)->mapWithKeys(fn (int $count, string $key): array => [$key => true])->all();
    }

    /** @param array<string, true> $duplicateKeys @return array<string, mixed> */
    private function row(BlogPost $post, array $duplicateKeys, bool $probePublicUrls): array
    {
        $media = $post->heroMedia;
        $approved = $media?->isApprovedRealMedia() === true;
        $fileExists = $media instanceof Media ? $this->fileExists($media) : false;
        $publicUrl = $approved ? $media?->publicUrl() : null;
        $httpStatus = $probePublicUrls && $publicUrl ? $this->safeHttpStatus($publicUrl) : null;
        $variants = $this->variantSummary($media);
        $duplicateKey = $media?->checksum_sha256 ?: ($media?->path ? 'path:'.$media->disk.':'.$media->path : null);
        $classification = match (true) {
            ! $media instanceof Media => 'NO_MEDIA',
            (bool) $media->is_placeholder || $media->verification_status === 'branded_fallback' => 'GRADIENT_PLACEHOLDER',
            ! $approved => 'UNAPPROVED_MEDIA',
            $media->kind !== 'external_hotlinked_media' && ! $fileExists => 'FILE_MISSING',
            $publicUrl === null => 'URL_BROKEN',
            $httpStatus !== null && ($httpStatus < 200 || $httpStatus >= 400) => 'URL_BROKEN',
            $duplicateKey !== null && isset($duplicateKeys[$duplicateKey]) => 'DUPLICATE_IMAGE',
            ! $variants['complete'] => 'VARIANTS_MISSING',
            ! filled($media->alt_text) => 'ALT_TEXT_MISSING',
            default => 'READY',
        };
        $wordCount = str_word_count(strip_tags($post->body));

        return [
            'blog_id' => $post->getKey(),
            'blog_public_id' => $post->public_id,
            'title' => $post->title,
            'slug' => $post->slug,
            'publication_status' => $post->status,
            'language' => 'tr',
            'category' => $post->category?->name,
            'summary' => $post->excerpt,
            'word_count' => $wordCount,
            'reading_minutes' => max(1, (int) ceil($wordCount / 180)),
            'heading_count' => preg_match_all('/^#{2,3}\s+/m', $post->body),
            'related_recipe_count' => $post->recipes->count(),
            'fact_check_status' => $post->fact_check_status,
            'editorial_status' => $post->editorial_status,
            'author_state' => $post->author_id !== null ? 'AUTHOR_RECORDED' : 'EDITORIAL_TEAM_DEFAULT',
            'seo' => [
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
                'canonical_url' => $post->canonical_url,
                'canonical_matches_slug' => filled($post->canonical_url) && str_ends_with((string) $post->canonical_url, '/blog/'.$post->slug),
            ],
            'identity_confirmation' => [
                'state' => 'NOT_RECORDED',
                'automatic_confirmation' => false,
                'reason' => 'Yayın ve editoryal durumları, konu-görsel eşleşmesi için ayrı insan kararının yerine geçmez.',
            ],
            'hero_media_public_id' => $media?->public_id,
            'media_approval_status' => $media?->media_status,
            'media_provenance' => $media instanceof Media ? [
                'source_name' => $media->source_name,
                'license' => $media->license,
                'generated_by_ai' => (bool) $media->generated_by_ai,
                'synthetic_approval_state' => $media->ai_review_status,
                'checksum_sha256' => $media->checksum_sha256,
                'perceptual_hash' => $media->perceptual_hash,
            ] : null,
            'source' => $media instanceof Media ? [
                'disk' => $media->disk,
                'path' => $media->path,
                'file_exists' => $fileExists,
                'mime_type' => $media->mime_type,
                'width' => $media->width,
                'height' => $media->height,
            ] : null,
            'variants' => $variants,
            'public_url' => $publicUrl,
            'http_status' => $httpStatus,
            'public_url_broken' => $httpStatus !== null && ($httpStatus < 200 || $httpStatus >= 400),
            'alt_text' => $media?->alt_text,
            'duplicate_image' => $duplicateKey !== null && isset($duplicateKeys[$duplicateKey]),
            'classification' => $classification,
        ];
    }

    private function fileExists(Media $media): bool
    {
        if ($media->kind === 'external_hotlinked_media') {
            return $media->publicUrl() !== null;
        }

        return is_string($media->path) && $media->path !== ''
            && array_key_exists((string) $media->disk, (array) config('filesystems.disks'))
            && Storage::disk((string) $media->disk)->exists($media->path);
    }

    /** @return array{required:list<string>,present:list<string>,missing:list<string>,complete:bool} */
    private function variantSummary(?Media $media): array
    {
        $required = ['hero', 'card', 'square', 'portrait', 'open_graph', 'thumbnail'];
        if (! $media instanceof Media) {
            return ['required' => $required, 'present' => [], 'missing' => $required, 'complete' => false];
        }
        $variants = (array) $media->responsive_variants;
        $present = [];
        foreach ($required as $profile) {
            $variant = $variants[$profile] ?? null;
            if (is_array($variant) && is_string($variant['jpeg'] ?? null) && is_string($variant['webp'] ?? null)) {
                $present[] = $profile;
            }
        }
        $missing = array_values(array_diff($required, $present));

        return ['required' => $required, 'present' => $present, 'missing' => $missing, 'complete' => $missing === []];
    }

    private function safeHttpStatus(string $url): ?int
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true) || isset($parts['user'], $parts['pass'])) {
            return null;
        }
        try {
            return Http::timeout(8)->withOptions(['allow_redirects' => ['max' => 3]])->head($url)->status();
        } catch (\Throwable) {
            return 0;
        }
    }
}
