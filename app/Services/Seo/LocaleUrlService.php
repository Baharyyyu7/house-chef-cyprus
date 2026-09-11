<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Http\Request;

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
                break;
            }
        }

        if ($locale === $this->defaultLocale()) {
            return (rtrim(url($path), '/') ?: url('/')).$suffix;
        }

        return rtrim(url('/'.$locale.rtrim($path, '/')), '/').$suffix;
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

        // Tek dil etkinken hreflang'in anlamı yok: bağlayacak alternatif yok.
        if (count($diller) < 2) {
            return [];
        }

        $path = '/'.ltrim($request->path(), '/');
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
