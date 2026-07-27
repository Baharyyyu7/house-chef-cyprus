<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireJsonApi
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) $request->attributes->get('request_id');

        if (! $request->isJson()) {
            return response()->json([
                'error' => ['code' => 'UNSUPPORTED_MEDIA_TYPE', 'message' => 'İstek tamamlanamadı.', 'details' => []],
                'request_id' => $requestId,
            ], 415)->header('X-Request-ID', $requestId);
        }

        if ($request->getContent() !== '' && json_decode($request->getContent(), true) === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => ['code' => 'MALFORMED_REQUEST', 'message' => 'İstek tamamlanamadı.', 'details' => []],
                'request_id' => $requestId,
            ], 400)->header('X-Request-ID', $requestId);
        }

        return $next($request);
    }
}
