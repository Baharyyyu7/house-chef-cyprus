<?php

declare(strict_types=1);

namespace App\Services\Personalization;

use App\Models\AuditLog;
use App\Models\PersonalizationExportRequest;
use App\Models\PersonalizationFeedback;
use App\Models\PersonalizationProfile;
use App\Models\PersonalizedRecommendation;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Explainable, deterministic, opt-in recommendation service.
 * Scores are persistence-only ranking inputs; they are never presented as compatibility percentages.
 */
final class PersonalizationService
{
    public const SCORE_VERSION = 'personalization-v1';

    public function profile(User $user): PersonalizationProfile
    {
        return $user->personalizationProfile()->firstOrCreate([], ['personalization_enabled' => true, 'settings' => $this->defaultSettings()]);
    }

    /** @param array<string, mixed> $attributes @param array<string, list<string>> $preferences */
    public function updateProfile(User $user, array $attributes, array $preferences = []): PersonalizationProfile
    {
        return DB::transaction(function () use ($user, $attributes, $preferences): PersonalizationProfile {
            $profile = $this->profile($user);
            $profile->fill($attributes);
            $profile->profile_version++;
            $profile->last_recalculated_at = now()->toDateTimeString();
            $profile->save();

            foreach ($preferences as $type => $values) {
                $normalized = collect($values)->filter(fn (mixed $value): bool => is_string($value))->map(fn (string $value): string => trim($value))->filter()->unique()->values();
                $profile->preferences()->where('type', $type)->where('source', 'explicitly_selected')->update(['is_active' => false]);
                foreach ($normalized as $value) {
                    $profile->preferences()->updateOrCreate(
                        ['type' => $type, 'value' => $value],
                        ['source' => 'explicitly_selected', 'is_active' => true],
                    );
                }
            }

            $this->forget($user);
            $this->audit($user, 'personalization.profile_updated', $profile, ['profile_public_id' => $profile->public_id]);

            return $profile->fresh(['preferences']);
        });
    }

    /** @param array<string, mixed> $attributes @param array<string, list<string>> $preferences */
    public function completeOnboarding(User $user, array $attributes, array $preferences = []): PersonalizationProfile
    {
        $attributes['onboarding_completed'] = true;

        return $this->updateProfile($user, $attributes, $preferences);
    }

    /** @param array<string, bool> $settings */
    public function updateSettings(User $user, array $settings): PersonalizationProfile
    {
        $profile = $this->profile($user);
        $profile->update(['settings' => array_replace($this->defaultSettings(), $profile->settings ?? [], $settings)]);
        $this->forget($user);
        $this->audit($user, 'personalization.privacy_settings_updated', $profile, ['profile_public_id' => $profile->public_id]);

        return $profile->fresh();
    }

    /** @return Collection<int, PersonalizedRecommendation> */
    public function recommendations(User $user, int $limit = 12): Collection
    {
        $profile = $this->profile($user);
        if (! $profile->personalization_enabled) {
            return collect();
        }

        $key = 'personalization:recommendations:'.$user->public_id.':'.self::SCORE_VERSION;

        return Cache::remember($key, now()->addMinutes(15), function () use ($profile, $limit, $user): Collection {
            $hidden = $user->personalizationFeedback()->where('type', 'hide_recipe')->whereNull('withdrawn_at')->pluck('recipe_id');
            $excludedIngredients = $profile->preferences()->whereIn('type', ['excluded_ingredient', 'allergen'])->where('is_active', true)->pluck('value')->all();
            $pantry = $user->pantryItems()->where('is_available', true)->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))->pluck('ingredient_id');
            $favorites = $user->favoriteRecipes()->pluck('recipes.id');
            $favoriteCuisines = $profile->preferences()->where('type', 'favorite_cuisine')->where('is_active', true)->pluck('value');
            $dislikedCuisines = $profile->preferences()->where('type', 'disliked_cuisine')->where('is_active', true)->pluck('value');
            $maxMinutes = $profile->preferred_duration_max;

            $recipes = Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->whereNotIn('id', $hidden)
                ->when($maxMinutes !== null, fn (Builder $query) => $query->whereRaw('COALESCE(prep_minutes, 0) + COALESCE(cook_minutes, 0) + COALESCE(rest_minutes, 0) <= ?', [$maxMinutes]))
                ->when($dislikedCuisines->isNotEmpty(), fn (Builder $query) => $query->whereDoesntHave('cuisine', fn (Builder $cuisine) => $cuisine->whereIn('slug', $dislikedCuisines)))
                ->with(['cuisine:id,slug,name', 'category:id,slug,name', 'ingredients:id,public_id,name'])
                ->limit(100)
                ->get()
                ->reject(function (Recipe $recipe) use ($excludedIngredients): bool {
                    if ($excludedIngredients === []) {
                        return false;
                    }

                    return $recipe->ingredients->contains(fn ($ingredient): bool => in_array($ingredient->public_id, $excludedIngredients, true));
                });

            $recommendations = $recipes->map(function (Recipe $recipe) use ($pantry, $favorites, $favoriteCuisines): array {
                $reasons = [];
                $factors = [];
                $score = 10;
                $matches = $recipe->ingredients->pluck('id')->intersect($pantry)->count();
                if ($matches > 0) {
                    $score += min(30, $matches * 6);
                    $reasons[] = 'pantry_match';
                    $factors['pantry_match_count'] = $matches;
                }
                if ($favoriteCuisines->contains($recipe->cuisine?->slug)) {
                    $score += 18;
                    $reasons[] = 'favorite_cuisine';
                }
                if ($favorites->contains($recipe->id)) {
                    $score += 8;
                    $reasons[] = 'saved_recipe';
                }
                if (($recipe->prep_minutes ?? 0) + ($recipe->cook_minutes ?? 0) <= 30) {
                    $score += 7;
                    $reasons[] = 'quick_recipe';
                }
                $reasons[] = 'published_verified';

                return compact('recipe', 'score', 'reasons', 'factors');
            })->sortByDesc('score')->take($limit)->values();

            $records = $recommendations->map(function (array $candidate) use ($user): PersonalizedRecommendation {
                return PersonalizedRecommendation::query()->updateOrCreate(
                    ['user_id' => $user->id, 'recipe_id' => $candidate['recipe']->id, 'score_version' => self::SCORE_VERSION],
                    ['score' => $candidate['score'], 'reason_codes' => $candidate['reasons'], 'soft_factors' => $candidate['factors'], 'status' => 'active', 'generated_at' => now(), 'expires_at' => now()->addMinutes(15)],
                );
            });

            $this->audit($user, 'personalization.recommendations_generated', $this->profile($user), ['count' => $records->count(), 'score_version' => self::SCORE_VERSION]);

            return $records->map(
                fn (PersonalizedRecommendation $recommendation): PersonalizedRecommendation => $recommendation->load('recipe.cuisine', 'recipe.category')
            );
        });
    }

    /** @param array<string, mixed> $input */
    public function feedback(User $user, Recipe $recipe, string $type, array $input = []): PersonalizationFeedback
    {
        abort_unless(in_array($type, ['like', 'not_interested', 'hide_recipe', 'too_long', 'too_difficult', 'budget_mismatch', 'dietary_mismatch', 'allergen_concern', 'more_like_this'], true), 422);

        $feedback = PersonalizationFeedback::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'personalized_recommendation_id' => isset($input['recommendation_public_id'])
                ? $user->personalizedRecommendations()->where('public_id', $input['recommendation_public_id'])->value('id')
                : null,
            'type' => $type,
            'note' => isset($input['note']) ? trim((string) $input['note']) : null,
            'profile_impact' => $type === 'hide_recipe' ? ['hard_exclusion' => true] : null,
            'processed_at' => now(),
        ]);
        $this->forget($user);
        $this->audit($user, 'personalization.feedback_submitted', $feedback, ['feedback_public_id' => $feedback->public_id, 'type' => $type]);

        return $feedback;
    }

    public function disable(User $user): PersonalizationProfile
    {
        $profile = $this->profile($user);
        $profile->update(['personalization_enabled' => false]);
        $this->forget($user);
        $this->audit($user, 'personalization.disabled', $profile, ['profile_public_id' => $profile->public_id]);

        return $profile->fresh();
    }

    public function enable(User $user): PersonalizationProfile
    {
        $profile = $this->profile($user);
        $profile->update(['personalization_enabled' => true]);
        $this->forget($user);
        $this->audit($user, 'personalization.enabled', $profile, ['profile_public_id' => $profile->public_id]);

        return $profile->fresh();
    }

    public function clearLearnedData(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $profile = $this->profile($user);
            $profile->preferences()->where('source', '!=', 'explicitly_selected')->delete();
            $user->personalizedRecommendations()->delete();
            $this->audit($user, 'personalization.learned_data_cleared', $profile, ['profile_public_id' => $profile->public_id]);
        });
        $this->forget($user);
    }

    public function clearRecommendationHistory(User $user): void
    {
        $user->personalizedRecommendations()->delete();
        $this->forget($user);
        $this->audit($user, 'personalization.recommendation_history_cleared', $this->profile($user), []);
    }

    public function requestExport(User $user): PersonalizationExportRequest
    {
        $request = $user->personalizationExportRequests()->firstOrCreate(
            ['status' => 'requested'],
            ['requested_at' => now()],
        );
        $this->audit($user, 'personalization.export_requested', $request, ['export_public_id' => $request->public_id]);

        return $request;
    }

    /** @return array<string, mixed> */
    public function export(User $user): array
    {
        $profile = $this->profile($user)->load('preferences');

        return [
            'profile' => ['public_id' => $profile->public_id, 'enabled' => $profile->personalization_enabled, 'onboarding_completed' => $profile->onboarding_completed, 'settings' => $profile->settings],
            'preferences' => $profile->preferences->map(fn ($item): array => ['public_id' => $item->public_id, 'type' => $item->type, 'value' => $item->value, 'source' => $item->source, 'active' => $item->is_active])->all(),
            'feedback' => $user->personalizationFeedback()->latest()->get()->map(fn ($item): array => ['public_id' => $item->public_id, 'recipe_public_id' => $item->recipe?->public_id, 'type' => $item->type, 'created_at' => $item->created_at?->toIso8601String()])->all(),
        ];
    }

    /** @return array<string, int> */
    public function health(): array
    {
        return [
            'enabled_users' => PersonalizationProfile::query()->where('personalization_enabled', true)->count(),
            'complete_profiles' => PersonalizationProfile::query()->where('onboarding_completed', true)->count(),
            'recommendations' => PersonalizedRecommendation::query()->where('status', 'active')->count(),
            'missing_explanations' => PersonalizedRecommendation::query()->whereNull('reason_codes')->count(),
            'opt_outs' => PersonalizationProfile::query()->where('personalization_enabled', false)->count(),
            'feedback' => PersonalizationFeedback::query()->whereNull('withdrawn_at')->count(),
        ];
    }

    private function forget(User $user): void
    {
        Cache::forget('personalization:recommendations:'.$user->public_id.':'.self::SCORE_VERSION);
    }

    /** @return array<string, bool> */
    private function defaultSettings(): array
    {
        return ['history_usage' => true, 'pantry_usage' => true, 'favorites_usage' => true, 'cooking_history_usage' => true, 'ai_memory_usage' => false, 'inferred_preferences' => true];
    }

    /** @param array<string, mixed> $metadata */
    private function audit(User $user, string $action, object $subject, array $metadata): void
    {
        AuditLog::create(['user_id' => $user->id, 'action' => $action, 'auditable_type' => $subject::class, 'auditable_id' => $subject->id, 'metadata' => $metadata]);
    }
}
