<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Observability\MetricService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RequestContext
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $requestId = (string) Str::ulid();
        $request->attributes->set('request_id', $requestId);
        $response = $next($request);
        $duration = (int) ((hrtime(true) - $startedAt) / 1_000_000);
        $response->headers->set('X-Request-ID', $requestId);
        app(MetricService::class)->recordRequest($request, $response->getStatusCode(), $duration);
        Log::info('request.completed', ['request_id' => $requestId, 'user_public_id' => $request->user()?->getAttribute('public_id'), 'route' => optional($request->route())->getName(), 'method' => $request->method(), 'status' => $response->getStatusCode(), 'duration_ms' => $duration, 'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')), 'user_agent' => Str::limit((string) $request->userAgent(), 160), 'environment' => app()->environment()]);

        return $response;
    }
}
