<?php

declare(strict_types=1);

namespace App\Support\RouteAudit;

use Illuminate\Routing\Route;

final class RouteSurfaceClassifier
{
    public function classify(Route $route): string
    {
        $uri = ltrim($route->uri(), '/');
        $middleware = $route->gatherMiddleware();

        if ($this->isMachineReadable($uri)) {
            return 'machine';
        }

        if ($uri === 'yonetim' || str_starts_with($uri, 'yonetim/')) {
            return 'admin';
        }

        if ($uri === 'api' || str_starts_with($uri, 'api/')) {
            return 'api';
        }

        if (in_array('auth', $middleware, true)) {
            return 'authenticated';
        }

        return 'public';
    }

    public function access(Route $route): string
    {
        return match ($this->classify($route)) {
            'admin' => 'administrator',
            'authenticated' => 'authenticated',
            'api' => in_array('auth', $route->gatherMiddleware(), true) ? 'authenticated_api' : 'public_api',
            default => 'public',
        };
    }

    public function isSafelyProbeable(Route $route): bool
    {
        return in_array('GET', $route->methods(), true)
            && ! str_contains($route->uri(), '{');
    }

    public function path(Route $route): string
    {
        $uri = trim($route->uri(), '/');

        return $uri === '' ? '/' : '/'.$uri;
    }

    private function isMachineReadable(string $uri): bool
    {
        return $uri === 'health'
            || $uri === 'up'
            || str_starts_with($uri, 'sitemap')
            || str_starts_with($uri, 'sitemaps/')
            || str_ends_with($uri, '.xml')
            || str_ends_with($uri, '.txt')
            || str_ends_with($uri, '.webmanifest')
            || in_array($uri, ['feed', 'rss.xml', 'opensearch.xml'], true);
    }
}
