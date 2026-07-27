<?php

namespace App\Providers;

use App\Contracts\AI\AiProviderInterface;
use App\Contracts\Media\MediaStorageInterface;
use App\Contracts\Search\RecipeSearchInterface;
use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Observers\NutritionFactObserver;
use App\Observers\RecipeIngredientObserver;
use App\Observers\RecipeObserver;
use App\Services\AI\DemoAiProvider;
use App\Services\Media\LocalMediaStorage;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RecipeIngredient::observe(RecipeIngredientObserver::class);
        NutritionFact::observe(NutritionFactObserver::class);
        Recipe::observe(RecipeObserver::class);
        Gate::define('access-admin', fn ($user): bool => $user->isAdmin());
        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('registration', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
    }
}
