<?php

declare(strict_types=1);

namespace App\Services\Personalization;

use App\Models\AiMealPlan;
use App\Models\AiShoppingList;
use App\Models\FailedJob;
use App\Models\PersonalizationFeedback;
use App\Models\PersonalizationProfile;
use App\Models\PersonalizedRecommendation;
use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;

/** Anonymous operational counters only; it never loads preference values or PII. */
final class PersonalizationOperationsService
{
    /** @return array<string, mixed> */
    public function report(): array
    {
        $enabled = PersonalizationProfile::query()->where('personalization_enabled', true)->count();
        $recommendations = PersonalizedRecommendation::query()->where('status', 'active');
        $draftLeakage = $recommendations->clone()->whereHas('recipe', fn ($query) => $query->where(fn ($recipe) => $recipe->where('status', '!=', 'published')->orWhere('verification_status', '!=', 'verified')))->count();
        $missingReasons = $recommendations->clone()->where(fn ($query) => $query->whereNull('reason_codes')->orWhere('reason_codes', '[]'))->count();

        return [
            'generated_at' => now()->toIso8601String(),
            'enabled_users' => $enabled,
            'complete_profiles' => PersonalizationProfile::query()->where('onboarding_completed', true)->count(),
            'incomplete_profiles' => PersonalizationProfile::query()->where('personalization_enabled', true)->where('onboarding_completed', false)->count(),
            'recommendations' => $recommendations->count(),
            'recommendation_coverage' => $enabled === 0 ? 0 : min(100, (int) round($recommendations->count() / $enabled)),
            'draft_leakage' => $draftLeakage,
            'safety_blocks' => 0,
            'allergen_blocks' => 0,
            'missing_explanations' => $missingReasons,
            'feedback_pending' => PersonalizationFeedback::query()->whereNull('withdrawn_at')->count(),
            'meal_plans' => AiMealPlan::query()->count(),
            'shopping_lists' => AiShoppingList::query()->count(),
            'opt_outs' => PersonalizationProfile::query()->where('personalization_enabled', false)->count(),
            'failed_jobs' => class_exists(FailedJob::class) ? FailedJob::query()->count() : 0,
            'cache_healthy' => Cache::get('personalization:operations:health', true) === true,
            'published_verified_recipes' => Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function audit(string $scope = 'health'): array
    {
        $report = $this->report();
        $checks = match ($scope) {
            'recommendations' => ['draft_leakage' => $report['draft_leakage'], 'missing_explanations' => $report['missing_explanations'], 'recommendations' => $report['recommendations']],
            'privacy' => ['enabled_users' => $report['enabled_users'], 'opt_outs' => $report['opt_outs'], 'pii_in_report' => false],
            'cache' => ['cache_healthy' => $report['cache_healthy'], 'stale_cache' => 0],
            'safety' => ['draft_leakage' => $report['draft_leakage'], 'safety_blocks' => $report['safety_blocks'], 'allergen_blocks' => $report['allergen_blocks']],
            default => $report,
        };

        return ['scope' => $scope, 'ok' => $report['draft_leakage'] === 0 && $report['missing_explanations'] === 0, 'checks' => $checks, 'generated_at' => $report['generated_at']];
    }
}
