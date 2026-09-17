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

if (! function_exists('taxonomy_name')) {
    /**
     * Taksonomi görünen adı: varsayılan dilde kayıt adı, diğer dillerde
     * arayüz sözlüğündeki karşılık; karşılık yoksa kayıt adı.
     *
     * Mutfak/kategori adları çeviri hattının tür listesinde yoktur
     * (yalnız recipe/blog), o yüzden kayıt adı Türkçe kalır. İngilizce
     * sayfada "Kıbrıs Mutfağı" yazması hem kullanıcıyı hem Google'ın dil
     * tespitini bozar; bu köprü kapanana dek sözlük karşılığı basılır.
     *
     * @param  object{name?: string, slug?: string}  $model
     */
    function taxonomy_name(object $model): string
    {
        $kayit = (string) ($model->name ?? '');

        if (app()->getLocale() === config('locales.default', 'tr')) {
            return $kayit;
        }

        $tur = $model instanceof App\Models\Cuisine
            ? 'cuisine'
            : ($model instanceof App\Models\Category ? 'category' : null);

        if ($tur === null || ! isset($model->slug)) {
            return $kayit;
        }

        $anahtar = "taxonomy.{$tur}.{$model->slug}";
        $ceviri = __($anahtar);

        return $ceviri === $anahtar ? $kayit : $ceviri;
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