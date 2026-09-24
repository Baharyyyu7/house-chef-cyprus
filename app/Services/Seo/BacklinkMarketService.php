<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

/**
 * Bir backlink adayının hangi pazara ait olduğu ve ona hangi sayfanın
 * gösterileceği.
 *
 * Aday listesi bugüne kadar tek yığındı: Kıbrıs'taki bir üniversite ile
 * Almanya'daki bir yemek blogu aynı havuzda duruyor ve ikisine de aynı
 * Türkçe sayfa öneriliyordu. Alman bir editöre Türkçe bir tarif göndermek
 * bağlantı kazandırmaz; e-postayı okunmadan sildirtir.
 *
 * Alan adına sütun eklemiyoruz. Pazar zaten alan adının uzantısında duruyor
 * ve türetmek geçmiş 280 adayı da bir anda sınıflandırıyor - migration
 * beklemeden, elle etiketleme yapmadan.
 */
final class BacklinkMarketService
{
    /**
     * Uzantıdan pazar dili.
     *
     * Liste kısa ve bilinçli: yalnız sitenin gerçekten açtığı diller var.
     * Tanımadığımız uzantı İngilizceye düşer çünkü uluslararası ortak dil
     * İngilizce; Türkçeye düşürmek, siteyi tanımayan bir editöre okuyamadığı
     * bir sayfa göstermek olurdu.
     */
    private const UZANTI_PAZARI = [
        'tr' => 'tr',
        'cy' => 'tr',
        'de' => 'de',
        'at' => 'de',
        'ch' => 'de',
        'pl' => 'pl',
        'ir' => 'fa',
        'ru' => 'ru',
        'pt' => 'pt',
        'br' => 'pt',
    ];

    public function __construct(
        private readonly LocaleUrlService $diller
    ) {
    }

    public function pazar(string $alanAdi, ?string $explicitLocale = null): string
    {
        if ($explicitLocale !== null && ($this->diller->enabledLocales()[$explicitLocale] ?? null) !== null) {
            return $explicitLocale;
        }

        $alanAdi = strtolower(trim($alanAdi));
        $parcalar = explode('.', $alanAdi);
        $son = end($parcalar);
        $sondanIkinci = count($parcalar) >= 2 ? $parcalar[count($parcalar) - 2] : '';

        // "gastronomy.emu.edu.tr" gibi adreslerde uzantı en sonda; ama
        // "example.com.tr" gibi ikili uzantılarda da son parça belirleyici.
        $aday = self::UZANTI_PAZARI[$son] ?? null;

        if ($aday === null && $sondanIkinci !== '') {
            $aday = self::UZANTI_PAZARI[$sondanIkinci] ?? null;
        }

        $dil = $aday ?? 'en';

        // Kapalı bir dile aday yönlendirmek, olmayan bir siteye bağlantı
        // istemektir. Kapalıysa varsayılan dile düşer.
        return ($this->diller->enabledLocales()[$dil] ?? null) !== null
            ? $dil
            : $this->diller->defaultLocale();
    }

    /**
     * Bu adaya gösterilecek sayfanın adresi.
     *
     * Sayfa o pazarın dilinde yoksa **null** döner. Türkçeye sessizce düşmek
     * kolay olurdu ama o zaman "Almanca 40 aday var" diye rapor verirken
     * hepsine Türkçe sayfa gönderiyor olurduk; sayı doğru, iş yanlış.
     *
     * @return array{url: string, locale: string}|null
     */
    public function varlikAdresi(string $alanAdi, string $tur, string $slug, ?string $explicitLocale = null): ?array
    {
        if ($explicitLocale !== null && ($this->diller->enabledLocales()[$explicitLocale] ?? null) === null) {
            return null;
        }

        $dil = $this->pazar($alanAdi, $explicitLocale);

        if ($tur === 'recipe') {
            $candidates = Recipe::query()
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)->orWhere('slug', 'like', "%{$slug}%");
                })
                ->orderByRaw('LENGTH(slug) ASC')
                ->get();

            foreach ($candidates as $kayit) {
                if ($kayit->status !== 'published' || $kayit->verification_status !== 'verified') {
                    continue;
                }
                if (! $kayit->hasTranslation($dil)) {
                    continue;
                }
                $yol = '/tarifler/'.$kayit->slug;
                $baslik = (string) ($kayit->translated('title', $dil) ?: $kayit->getRawOriginal('title'));

                return ['url' => $this->diller->urlFor($dil, $yol), 'locale' => $dil, 'title' => $baslik];
            }

            return null;
        }

        if ($tur === 'blog') {
            $candidates = BlogPost::query()
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)->orWhere('slug', 'like', "%{$slug}%");
                })
                ->orderByRaw('LENGTH(slug) ASC')
                ->get();

            foreach ($candidates as $kayit) {
                if ($kayit->status !== 'published' || $kayit->published_at === null || $kayit->published_at->isFuture()) {
                    continue;
                }
                if (! $kayit->hasTranslation($dil)) {
                    continue;
                }
                $yol = '/blog/'.$kayit->slug;
                $baslik = (string) ($kayit->translated('title', $dil) ?: $kayit->getRawOriginal('title'));

                return ['url' => $this->diller->urlFor($dil, $yol), 'locale' => $dil, 'title' => $baslik];
            }

            return null;
        }

        return null;
    }

    /**
     * Pazar başına kaç aday var ve kaçına gösterecek sayfamız var.
     *
     * "Hazır" olmayan aday kayıp değil, sıradaki iş: o pazarın dilinde o
     * sayfayı çevirmek. Rakamı ayrı tutmak, çeviri emeğini hangi sayfaya
     * harcamak gerektiğini söyleyen tek sinyal.
     *
     * @param  list<array{domain: string, asset_type: string, asset_keyword: string, locale?: string}>  $adaylar
     * @return array<string, array{total: int, ready: int}>
     */
    public function pazarDagilimi(array $adaylar): array
    {
        $dagilim = [];

        foreach ($adaylar as $aday) {
            $explicit = isset($aday['locale']) && is_string($aday['locale']) ? $aday['locale'] : null;
            $dil = $this->pazar((string) ($aday['domain'] ?? ''), $explicit);
            $dagilim[$dil] ??= ['total' => 0, 'ready' => 0];
            $dagilim[$dil]['total']++;

            $adres = $this->varlikAdresi(
                (string) ($aday['domain'] ?? ''),
                (string) ($aday['asset_type'] ?? ''),
                (string) ($aday['asset_keyword'] ?? ''),
                $explicit
            );

            if ($adres !== null) {
                $dagilim[$dil]['ready']++;
            }
        }

        return $dagilim;
    }
}
