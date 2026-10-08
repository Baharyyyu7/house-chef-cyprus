<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Dil URL'leri ve hreflang kümesi.
 *
 * Dil çerezle seçildiği sürece bütün diller aynı adresi paylaşıyordu. Bunun
 * arama motoru tarafındaki sonucu tek cümleyle şu: Googlebot çerez taşımaz ve
 * Accept-Language göndermez, dolayısıyla Türkçe dışındaki hiçbir sürümü hiç
 * görmedi. hreflang da yazılamıyordu, çünkü hreflang farklı URL'leri birbirine
 * bağlayan bir etikettir ve bağlayacak farklı URL yoktu.
 *
 * Burada üretilen küme yalnız ETKİN dilleri içerir. Kapalı bir dile URL ve
 * hreflang vermek, Türkçe içeriği yabancı dil sayfası gibi sunmak olurdu;
 * yardımcı içerik sistemi site geneli çalıştığı için çok sayıda zayıf dil
 * sayfası güçlü Türkçe sayfaların sıralamasını da aşağı çeker.
 */
final class LocaleUrlService
{
    /** @return array<string, array<string, mixed>> */
    public function enabledLocales(): array
    {
        return array_filter(
            (array) config('locales.supported', []),
            static fn (array $dil): bool => ($dil['enabled'] ?? false) === true
        );
    }

    /**
     * Rotanın yabancı dil sürümü var mı?
     *
     * Tek liste config('locales.localized_routes'): route() öneki, hreflang
     * ve önekli Türkçe sayfanın yönlendirmesi aynı cevabı kullanır.
     */
    public function yerellesmisRota(?Route $route): bool
    {
        return $route !== null && $route->named(...(array) config('locales.localized_routes', []));
    }

    public function defaultLocale(): string
    {
        return (string) config('locales.default', config('app.locale', 'tr'));
    }

    /**
     * Bir yolun belirli bir dildeki adresi.
     *
     * Varsayılan dil kök adreste yaşar; diğerleri kendi önekinde.
     */
    public function urlFor(string $locale, string $path): string
    {
        $parts = parse_url($path);
        $suffix = (isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        $path = $parts['path'] ?? '/';
        $path = '/'.ltrim($path, '/');

        // Yol zaten bir dil öneki taşıyorsa önce onu çıkar, yoksa /en/en/... olur.
        foreach (array_keys((array) config('locales.supported', [])) as $mevcut) {
            if ($path === '/'.$mevcut || str_starts_with($path, '/'.$mevcut.'/')) {
                $path = substr($path, strlen('/'.$mevcut)) ?: '/';
                // O dilin yazımı da iç rota adına döner: /pl/przepisy yolu
                // İngilizceye çevrilirken /en/przepisy kalmasın.
                $path = $this->icYol($mevcut, $path);
                break;
            }
        }

        if ($locale === $this->defaultLocale()) {
            return (rtrim(url($path), '/') ?: url('/')).$suffix;
        }

        return rtrim(url('/'.$locale.rtrim($this->yerellesmisYol($locale, $path), '/')), '/').$suffix;
    }

    /**
     * Yolun ilk parçasını o dilin yazımına çevirir: /tarifler → /recipes.
     *
     * Tarif slug'ı yemeğin kendi adıdır, çevrilmez. Yalnız ilk parça değişir;
     * iç rota adı Türkçe kalır, bu yalnızca herkese açık yazımdır.
     *
     * Girdi zaten yerelleşmiş olabilir (/en/recipes); önce Türkçe iç
     * ada geri çevrilir, sonra hedef dile ileri çevrilir ki /en/recipes
     * → /pl/przepisy gibi çapraz dönüşümler doğru çalışsın.
     */
    /** Yerelleşmiş ilk parçayı iç rota adına çevirir: /recipes → /tarifler. */
    public function icYol(string $locale, string $path): string
    {
        $ters = array_flip((array) (config('locales.path_aliases')[$locale] ?? []));

        if ($ters === [] || $path === '/' || $path === '') {
            return $path;
        }

        $parcalar = explode('/', ltrim($path, '/'));
        $parcalar[0] = $ters[$parcalar[0]] ?? $parcalar[0];

        return '/'.implode('/', $parcalar);
    }

    public function yerellesmisYol(string $locale, string $path): string
    {
        $takma = (array) (config('locales.path_aliases')[$locale] ?? []);

        if ($takma === [] || $path === '/' || $path === '') {
            return $path;
        }

        $parcalar = explode('/', ltrim($path, '/'));
        $ilk = $parcalar[0];

        // Girdi yerelleşmişse (recipes, rezepte, przepisy ...) önce Türkçe'ye çevir.
        if (! isset($takma[$ilk])) {
            foreach ((array) config('locales.path_aliases', []) as $harita) {
                $ters = array_flip((array) $harita);
                if (isset($ters[$ilk])) {
                    $ilk = $ters[$ilk];
                    break;
                }
            }
        }

        $parcalar[0] = $takma[$ilk] ?? $ilk;

        return '/'.implode('/', $parcalar);
    }

    /**
     * hreflang kümesi: her etkin dil ve x-default.
     *
     * Küme kendini de içerir. Kendine gönderme yapmayan bir hreflang kümesini
     * Google tamamen yok sayar; en sık yapılan hata bu.
     *
     * @return list<array{hreflang: string, href: string}>
     */
    public function alternates(Request $request, ?object $icerik = null): array
    {
        $diller = $this->enabledLocales();

        // Sayfa belirli bir içeriğe aitse, o içeriğin çevirisi olmayan diller
        // kümeden çıkar. hreflang'in var olmayan bir sayfayı göstermesi
        // kümenin tamamını geçersiz kılar: hedef 404 verirse Google çifti
        // atar. Listeleme, sitemap ve hreflang aynı "bu dilde var mı"
        // tanımını paylaşmak zorunda.
        if ($icerik !== null && method_exists($icerik, 'hasTranslation')) {
            $diller = array_filter(
                $diller,
                static fn (array $dil, string $kod): bool => $icerik->hasTranslation($kod),
                ARRAY_FILTER_USE_BOTH
            );
        }

        $path = '/'.ltrim($request->path(), '/');

        // Rota düzeyinde de süzülür: hub'ın o dilde tarif eşiği tutmuyorsa ya
        // da sayfanın hiç çevirisi yoksa (hakkimizda, sss) o dile hreflang
        // basılmaz. Cevap site haritasıyla aynı yerden gelir.
        $varlik = app(LocalePageAvailability::class);
        $diller = array_filter(
            $diller,
            static fn (string $kod): bool => $varlik->varMi($kod, $path),
            ARRAY_FILTER_USE_KEY
        );

        // Tek dil etkinken hreflang'in anlamı yok: bağlayacak alternatif yok.
        if (count($diller) < 2) {
            return [];
        }

        $kume = [];

        foreach (array_keys($diller) as $locale) {
            $kume[] = [
                'hreflang' => (string) (config("locales.supported.{$locale}.html_locale") ?: $locale),
                'href' => $this->urlFor($locale, $path),
            ];
        }

        // x-default: dili eşleşmeyen ziyaretçinin düşeceği sürüm.
        $kume[] = ['hreflang' => 'x-default', 'href' => $this->urlFor($this->defaultLocale(), $path)];

        return $kume;
    }
}
