<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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

        if ($request->is('yonetim', 'yonetim/*')) {
            app()->setLocale($default);
            $request->setLocale($default);

            return $next($request);
        }

        $publicPage = ! $request->is('dil', 'api/*');

        $locale = $this->firstEnabledLocale([
            // URL'deki dil öneki her şeyin önünde gelir: adres neyi
            // gösteriyorsa o servis edilir. Çerez yalnız kullanıcı tercihini
            // hatırlar; içeriği belirleyen URL'dir. Tersi olsaydı aynı adres
            // farklı ziyaretçilere farklı dil gösterirdi ve arama motoru
            // hangisini indeksleyeceğini bilemezdi.
            $request->attributes->get('hc_url_locale'),
            $publicPage ? $default : null,
            $request->hasSession() ? $request->session()->get('hc_locale') : null,
            $request->user()?->preferred_language,
            $request->cookie('hc_locale'),
            $default,
        ], $supported, $default);

        app()->setLocale($locale);
        $request->setLocale($locale);

        $previousFormatter = URL::pathFormatter();
        $adresler = app(LocaleUrlService::class);
        URL::formatPathUsing(function ($path, $route) use ($locale, $default, $previousFormatter, $adresler) {
            $path = $previousFormatter($path, $route);
            if ($locale !== $default && $route !== null && $adresler->yerellesmisRota($route)) {
                // Önek tek başına yetmiyor: dilin yol yazımı da uygulanmalı.
                // 8 Ekim 2026 denetimi: formatter yalnız öneki ekliyordu ve
                // route('recipes.show') İngilizcede /en/tarifler/... üretiyordu;
                // o adres 301 ile /en/recipes/...'e gidiyor. 275 yabancı
                // sayfada 4.696 iç bağlantı, 217 Recipe şeması ve 23 kanonik
                // yönlendirmeye işaret ediyordu. Yazım tek yerde,
                // LocaleUrlService'te; burada yeniden yazılmaz.
                return '/'.$locale.($path === '/' ? '' : $adresler->yerellesmisYol($locale, $path));
            }

            return $path;
        });
        try {
            return $next($request);
        } finally {
            URL::formatPathUsing($previousFormatter);
        }
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
