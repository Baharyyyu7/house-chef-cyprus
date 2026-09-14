<?php

declare(strict_types=1);

use App\Services\Seo\LocaleUrlService;

if (! function_exists('localized_route')) {
    /**
     * Generate a locale-prefixed route URL.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function localized_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'tr');

        $route = route($name, $parameters, $absolute);

        if ($locale === $defaultLocale) {
            return $route;
        }

        $localeUrlService = app(LocaleUrlService::class);

        return $localeUrlService->urlFor($locale, $route);
    }
}

if (! function_exists('localized_asset')) {
    /**
     * Generate a locale-prefixed asset URL.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function localized_asset(string $path, ?bool $secure = null): string
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'tr');

        $asset = asset($path, $secure);

        if ($locale === $defaultLocale) {
            return $asset;
        }

        $localeUrlService = app(LocaleUrlService::class);
        return $localeUrlService->urlFor($locale, $asset);
    }
}