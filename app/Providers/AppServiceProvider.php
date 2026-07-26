<?php

namespace App\Providers;

use App\Contracts\AI\AiProviderInterface;
use App\Contracts\Search\RecipeSearchInterface;
use App\Services\AI\DemoAiProvider;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', fn ($user): bool => $user->isAdmin());
        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
    }
}
