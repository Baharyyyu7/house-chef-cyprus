<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/** Read-only production preflight for every currently public recipe image. */
final class PublicRecipeMediaCompletionService
{
    /** @return array<string, mixed> */
    public function audit(bool $probePublicUrls = false): array
    {
        $recipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            ->with(['media.approvedBy', 'media.reviewedBy'])->orderBy('title')->get();
        $duplicateKeys = $this->duplicateKeys($recipes->pluck('media')->flatten(1));
        $rows = $recipes->map(fn (Recipe $recipe): array => $this->row($recipe, $duplicateKeys, $probePublicUrls));

        return [
            'generated_at' => now()->toIso8601String(),
            'published_recipes_audited' => $rows->count(),
            'classifications' => $rows->countBy('classification')->all(),
            'approved_media' => $rows->where('classification', 'READY')->count(),
            'broken_public_urls' => $rows->where('public_url_broken', true)->count(),
            'placeholder_cards_remaining' => $rows->where('classification', 'PLACEHOLDER_ONLY')->count(),
            'human_approval_required' => true,
            'auto_approve' => false,
            'auto_attach' => false,
            'recipes' => $rows->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function applyManifest(): array
    {
        $recipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            ->with(['media.approvedBy', 'media.reviewedBy'])->orderBy('title')->get();
        $entries = [];
        foreach ($recipes as $recipe) {
            $approved = $recipe->media->first(fn (Media $media): bool => $media->isApprovedRealMedia());
            if (! $approved instanceof Media || ! $this->variantsReady($approved)) {
                continue;
            }
            $previous = $recipe->media->sortBy('sort_order')->first();
            $reviewerPublicId = $approved->approved_by_user_id !== null
                ? $approved->approvedBy?->public_id
                : $approved->reviewedBy?->public_id;
            $entries[] = [
                'recipe_public_id' => $recipe->public_id,
                'approved_media_public_id' => $approved->public_id,
                'expected_prior_media_public_id' => $previous?->public_id,
                'target_media_public_id' => $approved->public_id,
                'approval_record' => [
                    'reviewer_public_id' => $reviewerPublicId,
                    'approved_at' => $approved->approved_at !== null ? Carbon::parse($approved->approved_at)->toIso8601String() : null,
                    'media_reviewed_at' => $approved->media_reviewed_at !== null ? Carbon::parse($approved->media_reviewed_at)->toIso8601String() : null,
                ],
                'checksum_sha256' => $approved->checksum_sha256,
                'variants_ready' => true,
                'alt_text' => $approved->alt_text,
                'expected_recipe_version' => $recipe->updated_at?->toIso8601String(),
            ];
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'commit_sha' => trim((string) config('app.commit_sha')) ?: null,
            'entries' => $entries,
            'entry_count' => count($entries),
            'optimistic_concurrency_required' => true,
            'database_restore_automatic' => false,
        ];
    }

    /** @param Collection<int, Media> $media @return array<string, true> */
    private function duplicateKeys(Collection $media): array
    {
        return $media->map(fn (Media $item): ?string => $item->checksum_sha256 ?: ($item->path ? 'path:'.$item->disk.':'.$item->path : null))
            ->filter()->countBy()->filter(fn (int $count): bool => $count > 1)->mapWithKeys(fn (int $count, string $key): array => [$key => true])->all();
    }

    /** @param array<string, true> $duplicateKeys @return array<string, mixed> */
    private function row(Recipe $recipe, array $duplicateKeys, bool $probePublicUrls): array
    {
        $media = $recipe->media->sortBy('sort_order')->first();
        $approved = $recipe->media->first(fn (Media $item): bool => $item->isApprovedRealMedia());
        $active = $approved ?? $media;
        $fileExists = $active instanceof Media ? $this->fileExists($active) : false;
        $variantsReady = $approved instanceof Media && $this->variantsReady($approved);
        $publicUrl = $approved?->publicUrl();
        $httpStatus = $probePublicUrls && $publicUrl ? $this->safeHttpStatus($publicUrl) : null;
        $duplicateKey = $active instanceof Media ? ($active->checksum_sha256 ?: ($active->path ? 'path:'.$active->disk.':'.$active->path : null)) : null;
        $duplicate = $duplicateKey !== null && isset($duplicateKeys[$duplicateKey]);
        $classification = match (true) {
            ! $active instanceof Media => 'NO_MEDIA_RECORD',
            (bool) $active->is_placeholder || $active->verification_status === 'branded_fallback' => 'PLACEHOLDER_ONLY',
            $active->kind !== 'external_hotlinked_media' && ! $fileExists => 'FILE_MISSING',
            $approved instanceof Media && $publicUrl === null => 'STORAGE_PATH_BROKEN',
            $approved instanceof Media && ! $variantsReady => 'VARIANTS_MISSING',
            $approved instanceof Media && $duplicate => 'DUPLICATE_MEDIA',
            $approved instanceof Media && $httpStatus !== null && ($httpStatus < 200 || $httpStatus >= 400) => 'PUBLIC_URL_BROKEN',
            ! $approved instanceof Media => 'UNAPPROVED_MEDIA',
            ! in_array($approved->fidelity_status, ['strong_match', 'approved'], true) => 'WRONG_DISH_MEDIA',
            default => 'READY',
        };

        $dimensions = $active instanceof Media && $active->width && $active->height ? $active->width.'x'.$active->height : null;

        return [
            'recipe_public_id' => $recipe->public_id,
            'title' => $recipe->title,
            'current_media_public_id' => $active?->public_id,
            'current_media_path' => $active?->path,
            'media_approval_status' => $active?->media_status,
            'media_classification' => $active?->kind,
            'storage_disk' => $active?->disk,
            'storage_file_exists' => $fileExists,
            'responsive_variants_exist' => $variantsReady,
            'public_url' => $publicUrl,
            'http_status' => $httpStatus,
            'public_url_broken' => $httpStatus !== null && ($httpStatus < 200 || $httpStatus >= 400),
            'mime_type' => $active?->mime_type,
            'dimensions' => $dimensions,
            'alt_text' => $active?->alt_text,
            'duplicate_usage' => $duplicate,
            'classification' => $classification,
        ];
    }

    private function fileExists(Media $media): bool
    {
        if ($media->kind === 'external_hotlinked_media') {
            return $media->publicUrl() !== null;
        }
        if (! is_string($media->path) || $media->path === '') {
            return false;
        }
        if (array_key_exists((string) $media->disk, (array) config('filesystems.disks'))) {
            return Storage::disk((string) $media->disk)->exists($media->path);
        }

        return is_file(public_path(ltrim($media->path, '/')));
    }

    private function variantsReady(Media $media): bool
    {
        if ($media->isApprovedUnsplash()) {
            return true;
        }
        $raw = $media->getRawOriginal('responsive_variants');
        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        $variants = is_array($decoded) ? $decoded : [];
        $widths = collect($variants)->pluck('width')->map(fn ($width): int => (int) $width);

        return collect([400, 640, 800, 1200, 1600])->every(fn (int $width): bool => $widths->contains($width))
            && collect($variants)->every(function (mixed $variant): bool {
                if (! is_array($variant)) {
                    return false;
                }

                return is_string($variant['jpeg'] ?? null) && is_string($variant['webp'] ?? null);
            });
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
