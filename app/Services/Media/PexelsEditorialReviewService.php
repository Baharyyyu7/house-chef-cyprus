<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AuditLog;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Persists only review metadata. Candidate images are never downloaded or
 * published by a scan; a separate explicit admin action is required.
 */
final class PexelsEditorialReviewService
{
    public function __construct(private readonly PexelsRecipeImageProvider $provider) {}

    /** @return array{recipes:int,candidates:int,auto_rejected:int,reviewable:int,failed:int} */
    public function scan(?int $batch = null, int $limit = 5): array
    {
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->with(['cuisine', 'category', 'ingredients', 'dietaryTags', 'steps'])
            ->orderBy('title')
            ->get();
        if ($batch !== null) {
            $recipes = $recipes->values()->slice(($batch - 1) * 10, 10)->values();
        }

        $summary = ['recipes' => $recipes->count(), 'candidates' => 0, 'auto_rejected' => 0, 'reviewable' => 0, 'failed' => 0];
        foreach ($recipes as $offset => $recipe) {
            $result = $this->provider->search($recipe);
            if ($result['status'] !== 'ok') {
                $summary['failed']++;

                continue;
            }
            $usedExternalIds = RecipeMediaCandidate::query()
                ->where('provider', 'pexels')
                ->where('recipe_id', '!=', $recipe->id)
                ->whereNotIn('status', ['auto_rejected', 'rejected', 'fallback_kept'])
                ->pluck('external_photo_id')
                ->all();
            $ranked = collect($this->rank($recipe, $result['candidates'], $usedExternalIds))
                ->take(max(1, min(5, $limit)))
                ->values();
            $reviewBatch = $batch ?? (intdiv($offset, 10) + 1);
            foreach ($ranked as $index => $candidate) {
                $status = $candidate['rejection_code'] === null ? 'pending_review' : 'auto_rejected';
                $record = RecipeMediaCandidate::query()->firstOrNew([
                    'recipe_id' => $recipe->id,
                    'provider' => 'pexels',
                    'external_photo_id' => $candidate['external_photo_id'],
                ]);
                if (in_array($record->status, ['selected_for_review', 'private_imported', 'approved'], true)) {
                    continue;
                }
                $record->fill([
                    'review_batch' => $reviewBatch,
                    'rank' => $index + 1,
                    'review_score' => $candidate['review_score'],
                    'score_breakdown' => $candidate['score_breakdown'],
                    'review_warnings' => $candidate['review_warnings'],
                    'status' => $status,
                    'rejection_code' => $candidate['rejection_code'],
                    'rejection_reason' => $candidate['rejection_reason'],
                    'photographer' => $candidate['photographer'],
                    'photographer_url' => $candidate['photographer_url'],
                    'pexels_page_url' => $candidate['pexels_page_url'],
                    'source_url' => $candidate['source_url'],
                    'alt_text' => $candidate['alt_text'],
                    'width' => $candidate['width'],
                    'height' => $candidate['height'],
                    'provider_metadata' => ['provider' => 'pexels', 'candidate_score' => $candidate['score'], 'orientation' => 'landscape'],
                    'scanned_at' => now(),
                ])->save();
                $summary['candidates']++;
                $summary[$status === 'auto_rejected' ? 'auto_rejected' : 'reviewable']++;
            }
        }

        return $summary;
    }

    public function select(RecipeMediaCandidate $candidate, User $reviewer): void
    {
        abort_unless($candidate->status === 'pending_review', 422, 'Bu aday seçim için uygun değil.');
        $candidate->update(['status' => 'selected_for_review', 'selected_at' => now(), 'selected_by_user_id' => $reviewer->id]);
        $this->audit($reviewer, 'pexels_editorial_candidate.selected', $candidate->recipe, ['candidate_public_id' => $candidate->public_id]);
    }

    public function reject(RecipeMediaCandidate $candidate, User $reviewer, string $reason = 'EDITORIAL_REJECTED'): void
    {
        abort_unless(in_array($candidate->status, ['pending_review', 'selected_for_review'], true), 422, 'Bu aday reddedilemez.');
        $candidate->update(['status' => 'rejected', 'rejection_code' => $reason, 'rejection_reason' => 'Editoryal incelemede uygun bulunmadı.', 'selected_at' => now(), 'selected_by_user_id' => $reviewer->id]);
        $this->audit($reviewer, 'pexels_editorial_candidate.rejected', $candidate->recipe, ['candidate_public_id' => $candidate->public_id, 'reason' => $reason]);
    }

    public function rejectAll(Recipe $recipe, User $reviewer): void
    {
        $recipe->mediaCandidates()->whereIn('status', ['pending_review', 'selected_for_review'])->update([
            'status' => 'rejected', 'rejection_code' => 'ALL_CANDIDATES_REJECTED',
            'rejection_reason' => 'Editoryal incelemede bu tarif için aday seçilmedi.',
            'selected_at' => now(), 'selected_by_user_id' => $reviewer->id,
        ]);
        $this->audit($reviewer, 'pexels_editorial_candidate.all_rejected', $recipe);
    }

    public function keepFallback(Recipe $recipe, User $reviewer): void
    {
        $recipe->mediaCandidates()->whereIn('status', ['pending_review', 'selected_for_review'])->update([
            'status' => 'fallback_kept', 'rejection_code' => 'FALLBACK_RETAINED', 'rejection_reason' => 'Editoryal inceleme, doğrulanmış uygun aday bulamadı.', 'selected_at' => now(), 'selected_by_user_id' => $reviewer->id,
        ]);
        $recipe->update(['image_status' => 'needs_real_media']);
        $this->audit($reviewer, 'pexels_editorial_candidate.fallback_kept', $recipe);
    }

    /**
     * @param  iterable<array<string,mixed>>  $candidates
     * @param  list<string>  $usedExternalIds
     * @return list<array<string,mixed>>
     */
    private function rank(Recipe $recipe, iterable $candidates, array $usedExternalIds): array
    {
        $titleTerms = $this->terms($recipe->title);
        $ingredientTerms = $recipe->ingredients->flatMap(fn ($ingredient) => $this->terms((string) $ingredient->name))->unique()->values()->all();
        $dietaryCodes = $recipe->dietaryTags->pluck('code')->all();
        $vegetarian = in_array('vegetarian', $dietaryCodes, true) || in_array('vegan', $dietaryCodes, true);
        $expectsAnimal = collect($ingredientTerms)->contains(fn (string $term): bool => in_array($term, ['chicken', 'tavuk', 'beef', 'meat', 'fish', 'balik', 'shrimp'], true));
        $methodTerms = $this->methodTerms($recipe);
        $cuisineTerms = $this->terms((string) ($recipe->cuisine->name ?? ''));

        return collect($candidates)->map(function (array $candidate) use ($titleTerms, $ingredientTerms, $vegetarian, $expectsAnimal, $methodTerms, $cuisineTerms, $usedExternalIds): array {
            $text = Str::lower(Str::ascii((string) $candidate['alt_text']));
            $warnings = [];
            $rejectionCode = null;
            $rejectionReason = null;
            $animal = preg_match('/\\b(chicken|beef|pork|meat|shrimp|prawn|fish|seafood)\\b/', $text) === 1;
            if ($vegetarian && $animal) {
                $rejectionCode = 'VEGETARIAN_CONFLICT';
                $rejectionReason = 'Vejetaryen/vegan tarif için hayvansal içerik gösteriyor.';
            } elseif (! $expectsAnimal && $animal) {
                $rejectionCode = 'UNEXPECTED_ANIMAL_PROTEIN';
                $rejectionReason = 'Tarifte bulunmayan hayvansal ana malzeme gösteriyor.';
            } elseif (preg_match('/\\b(city|landscape|mountain|street|shop|market|portrait|person|people)\\b/', $text) === 1) {
                $rejectionCode = 'NOT_A_SINGLE_DISH';
                $rejectionReason = 'Tarifin tek yemek sunumunu göstermiyor.';
            } elseif (in_array((string) $candidate['external_photo_id'], $usedExternalIds, true)) {
                $rejectionCode = 'DUPLICATE_EXTERNAL_ID';
                $rejectionReason = 'Bu Pexels görseli başka bir tarifin inceleme kaydında zaten bulunuyor.';
            }
            $titleMatches = collect($titleTerms)->filter(fn (string $term): bool => str_contains($text, $term))->count();
            $ingredientMatches = collect($ingredientTerms)->filter(fn (string $term): bool => str_contains($text, $term))->count();
            $methodMatches = collect($methodTerms)->filter(fn (string $term): bool => str_contains($text, $term))->count();
            $cuisineMatches = collect($cuisineTerms)->filter(fn (string $term): bool => str_contains($text, $term))->count();
            $quality = ((int) $candidate['width'] >= 1600 && (int) $candidate['height'] >= 900) ? 10 : 6;
            $breakdown = [
                'title' => min(25, $titleMatches * 12),
                'ingredients' => min(20, $ingredientMatches * 8),
                'method' => min(15, $methodMatches * 8),
                'category' => min(10, 5 + ($titleMatches > 0 ? 5 : 0)),
                'cuisine' => min(10, $cuisineMatches * 5),
                'quality' => $quality,
                'crop' => (((int) $candidate['width']) / max(1, (int) $candidate['height'])) >= 1.2 ? 5 : 2,
                'uniqueness' => 5,
            ];
            if ($rejectionCode !== null) {
                $warnings[] = $rejectionReason;
            }

            return array_merge($candidate, [
                'review_score' => array_sum($breakdown),
                'score_breakdown' => $breakdown,
                'review_warnings' => $warnings,
                'rejection_code' => $rejectionCode,
                'rejection_reason' => $rejectionReason,
            ]);
        })->sortByDesc('review_score')->values()->all();
    }

    /** @return list<string> */
    private function terms(string $value): array
    {
        $map = ['corbasi' => 'soup', 'tavuk' => 'chicken', 'balik' => 'fish', 'sebzeli' => 'vegetable', 'peynirli' => 'cheese', 'patatesli' => 'potato', 'nohutlu' => 'chickpea', 'makarnasi' => 'pasta', 'kabak' => 'zucchini', 'mantarli' => 'mushroom', 'ispanakli' => 'spinach', 'domatesli' => 'tomato', 'zeytinyagli' => 'olive'];

        return collect(preg_split('/\\s+/', Str::lower(Str::ascii($value))) ?: [])
            ->map(fn (string $term): string => $map[$term] ?? $term)
            ->filter(fn (string $term): bool => strlen($term) > 3 && ! in_array($term, ['usulu', 'akdeniz', 'kibris', 'fırın', 'firin'], true))
            ->unique()->values()->all();
    }

    /** @return list<string> */
    private function methodTerms(Recipe $recipe): array
    {
        $text = $recipe->steps->map(fn ($step): string => (string) $step->instruction)->implode(' ');
        $methods = [];
        foreach (['oven' => ['firin', 'fırın', 'bake'], 'grilled' => ['izgara', 'grill'], 'fried' => ['kizart', 'kızart', 'fry'], 'soup' => ['corba', 'çorba', 'boil'], 'roasted' => ['roast', 'kavur']] as $term => $aliases) {
            if (collect($aliases)->contains(fn (string $alias): bool => str_contains(Str::lower(Str::ascii($text)), Str::ascii($alias)))) {
                $methods[] = $term;
            }
        }

        return $methods;
    }

    /** @param array<string, mixed> $metadata */
    private function audit(User $reviewer, string $action, ?Recipe $recipe, array $metadata = []): void
    {
        if (! $recipe instanceof Recipe) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => $reviewer->id,
            'action' => $action,
            'auditable_type' => $recipe->getMorphClass(),
            'auditable_id' => $recipe->id,
            'metadata' => array_merge(['recipe_public_id' => $recipe->public_id], $metadata),
        ]);
    }
}
