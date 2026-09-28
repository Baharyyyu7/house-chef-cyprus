<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SiteFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSiteFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(app(SiteFeatureService::class)->enabled($feature), 404);

        return $next($request);
    }
}
