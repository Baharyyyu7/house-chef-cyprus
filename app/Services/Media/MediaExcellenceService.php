<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-only operational truth for the media programme. It deliberately never
 * promotes a candidate: an editor must still approve a compatible, licensed
 * asset through the existing private review flow.
 */
final class MediaExcellenceService
{
    /** @return array<string, mixed> */
    public function inventory(int $limit = 0): array
    {
        $recipeRows = Recipe::query()->with(['media.reviewedBy', 'mediaCandidates.selectedBy'])->orderBy('id')
            ->when($limit > 0, fn (Builder $query) => $query->limit($limit))->get()
            ->map(fn (Recipe $recipe): array => $this->recipeRow($recipe));

        $blogRows = BlogPost::query()->with('heroMedia.reviewedBy')->orderBy('id')
            ->when($limit > 0, fn (Builder $query) => $query->limit($limit))->get()
            ->map(fn (BlogPost $post): array => $this->blogRow($post));

        return [
            'generated_at' => now()->toIso8601String(),
            'rule_version' => (string) config('media_excellence.health_rule_version'),
            'recipes' => $recipeRows->values()->all(),
            'blogs' => $blogRows->values()->all(),
            'summary' => [
                'recipes' => $this->summarize($recipeRows),
                'blogs' => $this->summarize($blogRows),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function health(): array
    {
        $inventory = $this->inventory();
        $rows = collect(array_merge($inventory['recipes'], $inventory['blogs']));
        $hasEntities = $rows->isNotEmpty();
        $total = max(1, $rows->count());
        $approved = $rows->where('status', 'approved_real_media')->count();
        $fallback = $rows->where('status', 'branded_fallback')->count();
        $licenseMissing = $rows->where('license_complete', false)->count();
        $attributionMissing = $rows->where('attribution_complete', false)->count();
        $uncertain = $rows->whereIn('fidelity_status', ['uncertain', 'mismatch', 'prohibited'])->count();
        $responsiveMissing = $rows->where('responsive_ready', false)->where('status', 'approved_real_media')->count();
        $scanRequired = ! $hasEntities || $rows->contains(fn (array $row): bool => $row['last_scanned_at'] === null);

        $score = $hasEntities ? 100 : 0;
        $score -= (int) round((($total - $approved) / $total) * 35);
        $score -= min(25, $licenseMissing * 3);
        $score -= min(15, $attributionMissing * 2);
        $score -= min(15, $uncertain * 3);
        $score -= min(10, $responsiveMissing * 2);
        if ($scanRequired) {
            $score = min($score, 90);
        }
        if ($licenseMissing > 0) {
            $score = min($score, 80);
        }
        if ($uncertain > 0) {
            $score = min($score, 85);
        }
        if ($approved !== $total) {
            $score = min($score, 99);
        }

        return [
            'score' => max(0, min(100, $score)),
            'scan_required' => $scanRequired,
            'rule_version' => (string) config('media_excellence.health_rule_version'),
            'entities' => $rows->count(),
            'approved_real_media' => $approved,
            'branded_fallback' => $fallback,
            'no_media' => $rows->where('status', 'no_media')->count(),
            'candidates_ready' => $rows->where('status', 'candidates_ready')->count(),
            'selected_private' => $rows->where('status', 'selected_private')->count(),
            'fidelity_waiting' => $rows->where('status', 'fidelity_review')->count(),
            'license_waiting' => $rows->where('status', 'license_review')->count(),
            'attribution_waiting' => $rows->where('status', 'attribution_review')->count(),
            'optimization_waiting' => $rows->where('status', 'optimization_pending')->count(),
            'final_approval_waiting' => $rows->where('status', 'ready_for_human_approval')->count(),
            'rejected' => $rows->where('status', 'rejected')->count(),
            'duplicate_groups' => $this->duplicateGroups()->count(),
            'perceptual_duplicate_groups' => $this->perceptualDuplicateGroups()->count(),
            'provider_health' => $this->providerHealth(),
            'private_leakage' => $this->privateLeakageCount(),
            'schema_eligible' => $approved,
            'sitemap_eligible' => $approved,
            'coverage' => [
                'alt_text' => $rows->where('alt_complete', true)->count(),
                'caption' => $rows->where('caption_complete', true)->count(),
                'license' => $rows->where('license_complete', true)->count(),
                'attribution' => $rows->where('attribution_complete', true)->count(),
                'responsive' => $rows->where('responsive_ready', true)->count(),
            ],
            'last_scan_at' => Media::query()->max('health_scanned_at'),
        ];
    }

    /** @return array{scanned:int,updated:int,blocked:int} */
    public function scanFidelity(int $limit = 25): array
    {
        $candidates = RecipeMediaCandidate::query()->with('recipe.ingredients')->whereIn('status', ['pending_review', 'selected_for_review'])
            ->orderBy('id')->limit($limit)->get();
        $updated = 0;
        $blocked = 0;
        foreach ($candidates as $candidate) {
            $result = $this->evaluateCandidate($candidate);
            $candidate->update([
                'fidelity_status' => $result['status'],
                'fidelity_confidence' => $result['confidence'],
                'fidelity_reasons' => $result['reasons'],
                'fidelity_scanned_at' => now(),
            ]);
            $updated++;
            $blocked += in_array($result['status'], ['uncertain', 'mismatch', 'prohibited'], true) ? 1 : 0;
        }

        return ['scanned' => $candidates->count(), 'updated' => $updated, 'blocked' => $blocked];
    }

    /** @return array{scanned:int,missing:int} */
    public function auditLicense(int $limit = 100): array
    {
        $media = Media::query()->orderBy('id')->limit($limit)->get();
        $missing = 0;
        foreach ($media as $item) {
            $complete = filled($item->license) && filled($item->source_name) && filled($item->source_url);
            $status = $item->license_review_status === 'approved'
                ? 'approved'
                : ($complete ? 'pending_human_review' : 'missing');
            $item->update(['license_review_status' => $status, 'health_scanned_at' => now(), 'health_rule_version' => config('media_excellence.health_rule_version')]);
            $missing += $complete ? 0 : 1;
        }

        return ['scanned' => $media->count(), 'missing' => $missing];
    }

    /** @return array{scanned:int,missing:int} */
    public function auditAttribution(int $limit = 100): array
    {
        $media = Media::query()->orderBy('id')->limit($limit)->get();
        $missing = 0;
        foreach ($media as $item) {
            $required = in_array($item->source_name, ['Pexels', 'Unsplash', 'Manual licensed upload'], true);
            $complete = ! $required || filled($item->attribution);
            $status = $item->attribution_review_status === 'approved'
                ? 'approved'
                : ($complete ? 'pending_human_review' : 'missing');
            $item->update(['attribution_review_status' => $status, 'health_scanned_at' => now(), 'health_rule_version' => config('media_excellence.health_rule_version')]);
            $missing += $complete ? 0 : 1;
        }

        return ['scanned' => $media->count(), 'missing' => $missing];
    }

    /** @return Collection<int, EloquentCollection<int, Media>> */
    public function duplicateGroups(): Collection
    {
        return Media::query()->whereNotNull('checksum_sha256')->where('checksum_sha256', '!=', '')
            ->orderBy('checksum_sha256')->get()->groupBy('checksum_sha256')->filter(fn (Collection $group): bool => $group->count() > 1)->values();
    }

    /**
     * Near matches are a review queue. They are never automatically blocked
     * because a crop or a deliberately reused editorial image needs a human
     * decision.
     *
     * @return Collection<int, EloquentCollection<int, Media>>
     */
    public function perceptualDuplicateGroups(): Collection
    {
        return Media::query()->whereNotNull('perceptual_hash')->where('perceptual_hash', '!=', '')
            ->orderBy('perceptual_hash')->get()->groupBy('perceptual_hash')
            ->filter(fn (Collection $group): bool => $group->count() > 1)->values();
    }

    /** @return list<array{name:string,status:string,candidates:int}> */
    public function providerHealth(): array
    {
        return collect(['pexels', 'unsplash', 'edenai', 'gemini', 'bfl', 'stability'])->map(function (string $provider): array {
            $settings = (array) config('ai_images.providers.'.$provider, []);
            $configured = filled($settings['key'] ?? null) || filled($settings['api_key'] ?? null) || filled($settings['access_key'] ?? null);
            $enabled = (bool) ($settings['enabled'] ?? false);

            return [
                'name' => $provider,
                'status' => ! $enabled ? 'disabled' : ($configured ? 'configured' : 'credentials_missing'),
                'candidates' => RecipeMediaCandidate::query()->where('provider', $provider)->count(),
            ];
        })->all();
    }

    /** @return array{scanned:int,groups:int} */
    public function scanDuplicates(int $limit = 100): array
    {
        $groups = $this->duplicateGroups()->take(max(1, $limit));
        foreach ($groups as $group) {
            $group->each(fn (Media $media) => $media->update(['media_status' => 'blocked', 'health_scanned_at' => now(), 'health_rule_version' => config('media_excellence.health_rule_version')]));
        }

        return ['scanned' => Media::query()->limit(max(1, $limit))->count(), 'groups' => $groups->count()];
    }

    /** @return array<string, mixed> */
    public function morningBrief(): array
    {
        $health = $this->health();
        $priority = collect(array_merge($this->inventory()['recipes'], $this->inventory()['blogs']))
            ->filter(fn (array $row): bool => $row['status'] !== 'approved_real_media')
            ->sortBy(fn (array $row): int => match ($row['status']) {
                'blocked' => 0, 'license_review', 'attribution_review' => 1, 'fidelity_review' => 2, default => 3,
            })->take(20)->values()->all();

        return array_merge($health, ['generated_at' => now()->toIso8601String(), 'top_priority' => $priority]);
    }

    /** @return array<string, mixed> */
    private function recipeRow(Recipe $recipe): array
    {
        $media = $recipe->media;

        return $this->row((string) $recipe->public_id, 'recipe', $media, $recipe->mediaCandidates->count());
    }

    /** @return array<string, mixed> */
    private function blogRow(BlogPost $post): array
    {
        return $this->row((string) $post->public_id, 'blog', $post->heroMedia ? collect([$post->heroMedia]) : collect(), 0);
    }

    /** @param Collection<int, Media> $media @return array<string, mixed> */
    private function row(string $publicId, string $entityType, Collection $media, int $candidateCount): array
    {
        $real = $media->first(fn (Media $item): bool => $item->isApprovedRealMedia());
        $private = $media->first(fn (Media $item): bool => $item->visibility === 'private' && $item->verification_status !== 'rejected');
        $fallback = $media->isNotEmpty() && $media->every(fn (Media $item): bool => $item->is_placeholder || $item->verification_status === 'branded_fallback');
        $active = $real ?? $private ?? $media->first();
        $status = match (true) {
            $real instanceof Media => 'approved_real_media',
            $private instanceof Media && $private->fidelity_status === null => 'fidelity_review',
            $private instanceof Media && $private->license_review_status !== 'approved' => 'license_review',
            $private instanceof Media && $private->attribution_review_status !== 'approved' => 'attribution_review',
            $private instanceof Media => 'ready_for_human_approval',
            $candidateCount > 0 => 'candidates_ready',
            $fallback => 'branded_fallback',
            $media->isEmpty() => 'no_media',
            default => 'candidate_search_required',
        };

        return [
            'entity_public_id' => $publicId,
            'entity_type' => $entityType,
            'status' => $status,
            'current_source' => $active?->source_name,
            'fallback' => $fallback,
            'approved_real_media' => $real instanceof Media,
            'selected_private' => $private instanceof Media,
            'candidate_count' => $candidateCount,
            'rejected_count' => $media->where('verification_status', 'rejected')->count(),
            'alt_complete' => $real instanceof Media && filled($real->alt_text),
            'caption_complete' => $real instanceof Media && filled($real->caption),
            'license_complete' => $real instanceof Media && filled($real->license) && $real->license_review_status === 'approved',
            'attribution_complete' => $real instanceof Media && $real->attribution_review_status === 'approved',
            'dimensions' => $active instanceof Media && $active->width && $active->height ? $active->width.'×'.$active->height : null,
            'mime_type' => $active instanceof Media ? $active->mime_type : null,
            'file_size' => $active instanceof Media ? $active->size : null,
            'aspect_ratio' => $active instanceof Media && $active->width && $active->height ? round($active->width / $active->height, 3) : null,
            'checksum' => $active instanceof Media ? $active->checksum_sha256 : null,
            'fidelity_status' => $active instanceof Media ? $active->fidelity_status : null,
            'fidelity_confidence' => $active instanceof Media ? $active->fidelity_confidence : null,
            'responsive_ready' => $real instanceof Media && ! empty($real->responsive_variants),
            'public_usage' => $real instanceof Media,
            'og_usage' => $real instanceof Media,
            'sitemap_usage' => $real instanceof Media,
            'schema_usage' => $real instanceof Media,
            'last_reviewed_at' => $active instanceof Media && $active->media_reviewed_at !== null ? Carbon::parse((string) $active->media_reviewed_at)->toIso8601String() : null,
            'reviewer_public_id' => $active?->reviewedBy?->public_id,
            'last_scanned_at' => $active instanceof Media && $active->health_scanned_at !== null ? Carbon::parse((string) $active->health_scanned_at)->toIso8601String() : null,
            'blocking_reason' => $status === 'approved_real_media' ? null : $status,
        ];
    }

    /** @param Collection<int, array<string, mixed>> $rows @return array<string, int> */
    private function summarize(Collection $rows): array
    {
        return $rows->countBy('status')->map(fn (int $count): int => $count)->all();
    }

    /** @return array{status:string,confidence:int,reasons:list<string>} */
    private function evaluateCandidate(RecipeMediaCandidate $candidate): array
    {
        $recipe = $candidate->recipe;
        $text = mb_strtolower(implode(' ', array_filter([$candidate->alt_text, $candidate->description, $candidate->alt_description])));
        $titleTerms = $recipe ? preg_split('/[^[:alnum:]]+/u', mb_strtolower($recipe->title)) ?: [] : [];
        $ingredients = $recipe?->ingredients->pluck('name')->map(fn ($name): string => mb_strtolower((string) $name))->all() ?? [];
        $titleHit = collect($titleTerms)->filter(fn (string $term): bool => mb_strlen($term) > 3 && str_contains($text, $term))->isNotEmpty();
        $ingredientHits = collect($ingredients)->filter(fn (string $term): bool => mb_strlen($term) > 3 && str_contains($text, $term))->count();
        $score = min(100, ($titleHit ? 55 : 10) + min(30, $ingredientHits * 10) + (($candidate->width ?? 0) >= 900 ? 10 : 0));
        $status = $score >= 85 ? 'strong_match' : ($score >= 60 ? 'acceptable_match' : 'uncertain');
        $reasons = [];
        if (! $titleHit) {
            $reasons[] = 'Tarif başlığı aday metadata’sıyla doğrudan eşleşmedi.';
        }
        if ($ingredientHits === 0) {
            $reasons[] = 'Ana malzeme eşleşmesi kanıtlanamadı.';
        }
        if (($candidate->width ?? 0) < 900) {
            $reasons[] = 'Çözünürlük üretim kartı için sınırlı olabilir.';
        }

        return ['status' => $status, 'confidence' => $score, 'reasons' => $reasons];
    }

    private function privateLeakageCount(): int
    {
        return Media::query()->where('visibility', 'private')->where(function (Builder $query): void {
            $query->where('verification_status', 'verified')->orWhere('media_status', 'approved_real_media');
        })->count();
    }
}
