<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply only an explicitly selected, editorially enabled locale.
     *
     * We intentionally do not infer a language from Accept-Language: the same
     * canonical URL must not silently serve different editorial content to
     * search engines and visitors before translated content is ready.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('locales.supported', []);
        $default = (string) config('locales.default', config('app.locale', 'tr'));

        $locale = $this->firstEnabledLocale([
            $request->hasSession() ? $request->session()->get('hc_locale') : null,
            $request->user()?->preferred_language,
            $request->cookie('hc_locale'),
            $default,
        ], $supported, $default);

        app()->setLocale($locale);
        $request->setLocale($locale);

        return $next($request);
    }

    /**
     * @param  array<string, array<string, mixed>>  $supported
     * @param  array<int, mixed>  $candidates
     */
    private function firstEnabledLocale(array $candidates, array $supported, string $fallback): string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && ($supported[$candidate]['enabled'] ?? false) === true) {
                return $candidate;
            }
        }

        return ($supported[$fallback]['enabled'] ?? false) === true ? $fallback : 'tr';
    }
}
