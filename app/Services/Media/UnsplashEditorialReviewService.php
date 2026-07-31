<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AuditLog;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use Illuminate\Support\Str;

/** Persists compact Unsplash review metadata only; no media is downloaded here. */
final class UnsplashEditorialReviewService
{
    public function __construct(private readonly UnsplashRecipeImageProvider $provider) {}

    /** @return array{candidates:int,auto_rejected:int,reviewable:int,failed:int} */
    public function search(Recipe $recipe): array
    {
        $result = $this->provider->search($recipe);
        if ($result['status'] !== 'ok') {
            return ['candidates' => 0, 'auto_rejected' => 0, 'reviewable' => 0, 'failed' => 1];
        }
        $used = RecipeMediaCandidate::query()->where('provider', 'unsplash')->where('recipe_id', '!=', $recipe->id)
            ->whereNotIn('status', ['auto_rejected', 'rejected', 'fallback_kept'])->pluck('external_photo_id')->all();
        $summary = ['candidates' => 0, 'auto_rejected' => 0, 'reviewable' => 0, 'failed' => 0];
        foreach ($this->rank($recipe, $result['candidates'], $used) as $rank => $candidate) {
            $status = $candidate['rejection_code'] === null ? 'pending_review' : 'auto_rejected';
            $record = RecipeMediaCandidate::query()->firstOrNew(['recipe_id' => $recipe->id, 'provider' => 'unsplash', 'external_photo_id' => $candidate['external_photo_id']]);
            if (in_array($record->status, ['selected_for_review', 'approved'], true)) {
                continue;
            }
            $record->fill([
                'rank' => $rank + 1, 'review_score' => $candidate['review_score'], 'score_breakdown' => $candidate['score_breakdown'], 'review_warnings' => $candidate['review_warnings'],
                'status' => $status, 'rejection_code' => $candidate['rejection_code'], 'rejection_reason' => $candidate['rejection_reason'],
                'photographer' => $candidate['photographer'], 'photographer_url' => $candidate['photographer_url'], 'external_page_url' => $candidate['external_page_url'],
                'source_url' => $candidate['source_url'], 'small_url' => $candidate['small_url'], 'thumb_url' => $candidate['thumb_url'], 'download_location' => $candidate['download_location'],
                'description' => $candidate['description'], 'alt_description' => $candidate['alt_description'], 'alt_text' => $candidate['alt_text'], 'width' => $candidate['width'], 'height' => $candidate['height'],
                'orientation' => $candidate['orientation'], 'color' => $candidate['color'], 'blur_hash' => $candidate['blur_hash'], 'utm_source' => $candidate['utm_source'], 'utm_medium' => $candidate['utm_medium'],
                'provider_metadata' => ['provider' => 'unsplash', 'hotlink_only' => true, 'attribution_text' => $candidate['attribution_text']], 'scanned_at' => now(),
            ])->save();
            $summary['candidates']++;
            $summary[$status === 'auto_rejected' ? 'auto_rejected' : 'reviewable']++;
        }

        return $summary;
    }

    public function select(RecipeMediaCandidate $candidate, User $reviewer): void
    {
        abort_unless($candidate->provider === 'unsplash' && $candidate->status === 'pending_review', 422, 'Bu Unsplash adayı seçim için uygun değil.');
        $candidate->update(['status' => 'selected_for_review', 'selected_at' => now(), 'selected_by_user_id' => $reviewer->id]);
        $this->audit($reviewer, 'unsplash_editorial_candidate.selected', $candidate);
    }

    public function reject(RecipeMediaCandidate $candidate, User $reviewer): void
    {
        abort_unless($candidate->provider === 'unsplash' && in_array($candidate->status, ['pending_review', 'selected_for_review'], true), 422, 'Bu Unsplash adayı reddedilemez.');
        $candidate->update(['status' => 'rejected', 'rejection_code' => 'EDITORIAL_REJECTED', 'rejection_reason' => 'Editoryal incelemede uygun bulunmadı.', 'selected_at' => now(), 'selected_by_user_id' => $reviewer->id]);
        $this->audit($reviewer, 'unsplash_editorial_candidate.rejected', $candidate);
    }

    /** @param list<array<string,mixed>> $items @param list<string> $used @return list<array<string,mixed>> */
    private function rank(Recipe $recipe, array $items, array $used): array
    {
        $terms = collect(preg_split('/\s+/', Str::lower(Str::ascii($recipe->title))) ?: [])->filter(fn (string $term): bool => strlen($term) > 3)->values();
        $ingredients = $recipe->ingredients->flatMap(fn ($ingredient) => preg_split('/\s+/', Str::lower(Str::ascii((string) $ingredient->name))) ?: [])->filter(fn (string $term): bool => strlen($term) > 3)->unique();
        $vegetarian = $recipe->dietaryTags->pluck('code')->intersect(['vegetarian', 'vegan'])->isNotEmpty();

        return collect($items)->map(function (array $candidate) use ($terms, $ingredients, $vegetarian, $used): array {
            $text = Str::lower(Str::ascii((string) ($candidate['alt_text'] ?? '')));
            $reason = null;
            if (in_array((string) $candidate['external_photo_id'], $used, true)) {
                $reason = ['DUPLICATE_EXTERNAL_ID', 'Bu Unsplash görseli başka bir tarifte zaten inceleniyor.'];
            } elseif ($vegetarian && preg_match('/\b(chicken|beef|pork|meat|fish|shrimp)\b/', $text) === 1) {
                $reason = ['VEGETARIAN_CONFLICT', 'Vejetaryen/vegan tarif için hayvansal içerik gösteriyor.'];
            } elseif (preg_match('/\b(city|landscape|mountain|street|portrait|person|people|fashion)\b/', $text) === 1) {
                $reason = ['NOT_A_SINGLE_DISH', 'Tarifin bitmiş tek yemek sunumunu göstermiyor.'];
            }
            $title = min(25, $terms->filter(fn (string $term): bool => str_contains($text, $term))->count() * 12);
            $ingredient = min(20, $ingredients->filter(fn (string $term): bool => str_contains($text, $term))->count() * 8);
            $quality = ((int) $candidate['width'] >= 1600 && (int) $candidate['height'] >= 900) ? 10 : 6;
            $breakdown = ['title' => $title, 'ingredients' => $ingredient, 'method' => 0, 'category' => $title > 0 ? 10 : 5, 'cuisine' => 0, 'quality' => $quality, 'crop' => ((int) $candidate['width'] / max(1, (int) $candidate['height'])) >= 1.2 ? 5 : 2, 'uniqueness' => 5];

            return array_merge($candidate, ['review_score' => array_sum($breakdown), 'score_breakdown' => $breakdown, 'review_warnings' => $reason === null ? [] : [$reason[1]], 'rejection_code' => $reason[0] ?? null, 'rejection_reason' => $reason[1] ?? null]);
        })->sortByDesc('review_score')->values()->all();
    }

    private function audit(User $user, string $action, RecipeMediaCandidate $candidate): void
    {
        AuditLog::query()->create(['user_id' => $user->id, 'action' => $action, 'auditable_type' => $candidate->recipe?->getMorphClass(), 'auditable_id' => $candidate->recipe_id, 'metadata' => ['recipe_public_id' => $candidate->recipe?->public_id, 'candidate_public_id' => $candidate->public_id, 'provider' => 'unsplash']]);
    }
}
