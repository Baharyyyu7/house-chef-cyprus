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
            ->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'media.approvedBy', 'media.reviewedBy'])->orderBy('title')->get();
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
            ->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'media.approvedBy', 'media.reviewedBy'])->orderBy('title')->get();
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
        $identity = $this->identityState($recipe);

        return [
            'recipe_id' => $recipe->getKey(),
            'recipe_public_id' => $recipe->public_id,
            'title' => $recipe->title,
            'slug' => $recipe->slug,
            'publication_status' => $recipe->status,
            'verification_status' => $recipe->verification_status,
            'cuisine' => $recipe->cuisine?->name,
            'category' => $recipe->category?->name,
            'primary_ingredients' => $recipe->recipeIngredients
                ->sortBy('sort_order')
                ->take(6)
                ->map(fn ($ingredient): ?string => $ingredient->ingredient?->name)
                ->filter()
                ->values()
                ->all(),
            'identity_confirmation' => $identity,
            'generation_eligible' => false,
            'generation_blocker' => 'IDENTITY_CONFIRMATION_REQUIRED',
            'current_media_public_id' => $active?->public_id,
            'current_media_path' => $active?->path,
            'media_approval_status' => $active?->media_status,
            'media_classification' => $active?->kind,
            'storage_disk' => $active?->disk,
            'storage_file_exists' => $fileExists,
            'source' => $this->sourceSummary($active, $fileExists),
            'variants' => $this->variantSummary($approved),
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

    /** @return array{state:string,automatic_confirmation:bool,reason:string} */
    private function identityState(Recipe $recipe): array
    {
        // A published/verified state demonstrates public visibility, not a visual
        // identity decision. There is intentionally no inferred confirmation from
        // a title, seed data, provider result, or previous placeholder image.
        return [
            'state' => 'NOT_RECORDED',
            'automatic_confirmation' => false,
            'reason' => 'Görsel üretimi veya onayı için tarif kimliğinin insan editör tarafından ayrı olarak kaydedilmesi gerekir.',
        ];
    }

    /** @return array<string, mixed>|null */
    private function sourceSummary(?Media $media, bool $fileExists): ?array
    {
        if (! $media instanceof Media) {
            return null;
        }

        return [
            'disk' => $media->disk,
            'path' => $media->path,
            'mime_type' => $media->mime_type,
            'dimensions' => $media->width && $media->height ? ['width' => (int) $media->width, 'height' => (int) $media->height] : null,
            'checksum_sha256' => $media->checksum_sha256,
            'perceptual_hash' => $media->perceptual_hash,
            'file_exists' => $fileExists,
            'provenance' => [
                'source_name' => $media->source_name,
                'source_url' => $media->source_url,
                'license' => $media->license,
                'generated_by_ai' => (bool) $media->generated_by_ai,
                'approval_state' => $media->ai_review_status,
            ],
        ];
    }

    /** @return array{required:list<string>,present:list<string>,missing:list<string>,complete:bool}|null */
    private function variantSummary(?Media $media): ?array
    {
        if (! $media instanceof Media) {
            return null;
        }

        if ($media->isApprovedUnsplash()) {
            return ['required' => ['provider_managed'], 'present' => ['provider_managed'], 'missing' => [], 'complete' => true];
        }

        $raw = $media->getRawOriginal('responsive_variants');
        $variants = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        $variants = is_array($variants) ? $variants : [];
        $required = ['thumbnail', 'square', 'card', 'open_graph', 'hero'];
        $present = [];
        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $name = $variant['name'] ?? null;
            if (is_string($name) && in_array($name, $required, true) && is_string($variant['jpeg'] ?? null) && is_string($variant['webp'] ?? null)) {
                $present[] = $name;
            }
        }
        $present = array_values(array_unique($present));
        $missing = array_values(array_diff($required, $present));

        return ['required' => $required, 'present' => $present, 'missing' => $missing, 'complete' => $missing === []];
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
        $requiredProfiles = ['thumbnail', 'square', 'card', 'open_graph', 'hero'];

        return collect($requiredProfiles)->every(function (string $profile) use ($variants): bool {
            $variant = $variants[$profile] ?? null;
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
