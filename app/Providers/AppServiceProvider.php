<?php

namespace App\Providers;

use App\Contracts\AI\AiProviderInterface;
use App\Contracts\Media\ImageGenerationProviderInterface;
use App\Contracts\Media\MediaStorageInterface;
use App\Contracts\Observability\ErrorReporterInterface;
use App\Contracts\Search\RecipeSearchInterface;
use App\Models\ContentFactoryIssue;
use App\Models\EditorialReviewPack;
use App\Models\IngredientUnitConversion;
use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Observers\IngredientUnitConversionObserver;
use App\Observers\NutritionFactObserver;
use App\Observers\RecipeIngredientObserver;
use App\Observers\RecipeObserver;
use App\Services\AI\DemoAiProvider;
use App\Services\Media\GeminiImageCapabilityHealth;
use App\Services\Media\LocalMediaStorage;
use App\Services\Observability\LocalLogErrorReporter;
use App\Services\Search\DatabaseRecipeSearch;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RecipeSearchInterface::class, DatabaseRecipeSearch::class);
        $this->app->bind(AiProviderInterface::class, DemoAiProvider::class);
        $this->app->bind(MediaStorageInterface::class, LocalMediaStorage::class);
        $this->app->bind(ImageGenerationProviderInterface::class, GeminiImageCapabilityHealth::class);
        $this->app->bind(ErrorReporterInterface::class, LocalLogErrorReporter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RecipeIngredient::observe(RecipeIngredientObserver::class);
        NutritionFact::observe(NutritionFactObserver::class);
        IngredientUnitConversion::observe(IngredientUnitConversionObserver::class);
        Recipe::observe(RecipeObserver::class);
        Gate::define('access-admin', fn ($user): bool => $user->isAdmin());
        Gate::define('access-content-factory', fn ($user): bool => $user->isContentFactoryStaff());
        Gate::define('review-fact-check', function ($user, ?ContentFactoryIssue $issue, string $action = 'view'): bool {
            if (! $user->isContentFactoryStaff()) {
                return false;
            }
            if ($issue !== null && in_array($action, ['approve', 'reject', 'defer'], true) && $issue->reviewed_by_user_id === $user->id) {
                return false;
            }
            if ($user->isAdmin() || $user->role === 'fact_check_reviewer') {
                return true;
            }
            $isHighRisk = $issue !== null && in_array($issue->risk_level, ['critical', 'high'], true);
            if ($user->role === 'content_editor') {
                return ! $isHighRisk && in_array($action, ['view', 'note', 'request_more_source', 'revision_request'], true);
            }
            if ($user->role === 'seo_reviewer') {
                return ! $isHighRisk && in_array($issue?->risk_domain, ['SEO', 'editorial'], true) && in_array($action, ['view', 'note', 'request_more_source', 'revision_request'], true);
            }

            return false;
        });
        Gate::define('approve-content-revision', fn ($user): bool => $user->isAdmin() || $user->role === 'fact_check_reviewer');
        Gate::define('apply-content-revision', fn ($user): bool => $user->isAdmin());
        Gate::define('run-duplicate-dry-run', fn ($user): bool => $user->isAdmin() || $user->role === 'fact_check_reviewer');
        Gate::define('run-duplicate-bulk', fn ($user): bool => $user->isAdmin());
        Gate::define('rescan-content-readiness', fn ($user): bool => $user->isAdmin());
        Gate::define('access-editorial-operations', fn ($user): bool => $user->isContentFactoryStaff());
        Gate::define('review-editorial-pack', function ($user, ?EditorialReviewPack $pack, string $section = 'overview', string $action = 'view'): bool {
            if (! $user->isContentFactoryStaff()) {
                return false;
            }
            if ($user->isAdmin()) {
                return true;
            }
            if ($user->role === 'media_reviewer') {
                return $section === 'media' && in_array($action, ['view', 'approve_section', 'reject_section', 'request_revision', 'note'], true);
            }
            if ($user->role === 'fact_check_reviewer') {
                return in_array($section, ['fact_check', 'evidence', 'overview'], true);
            }
            if ($user->role === 'seo_reviewer') {
                return $pack?->content_type === 'blog' && in_array($section, ['seo', 'overview'], true);
            }

            return $user->role === 'content_editor' && in_array($section, ['editorial', 'overview', 'storage', 'media'], true);
        });
        Gate::define('assign-editorial-work', fn ($user): bool => $user->isAdmin());
        Gate::define('manage-source-research', fn ($user): bool => ($user->isAdmin() || $user->role === 'fact_check_reviewer') && ! $user->isSystemReviewer());
        Gate::define('final-content-approval', fn ($user): bool => $user->isAdmin());
        Gate::define('view-editorial-media', fn ($user): bool => $user->isAdmin() || $user->role === 'media_reviewer');
        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('registration', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
    }
}
