<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/** Pins the request locale for a path-prefixed language edition (/en/...). */
final class SetLocale
{
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        abort_unless(array_key_exists($locale, (array) config('localization.locales')), 404);

        $previous = app()->getLocale();
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        try {
            $response = $next($request);
        } finally {
            // Long-lived workers (and the test kernel) reuse the application
            // instance; never let a prefixed edition leak into the next request.
            app()->setLocale($previous);
            Carbon::setLocale($previous);
        }
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}
