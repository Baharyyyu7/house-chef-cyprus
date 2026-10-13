<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Console\Commands\SeoHealRedirects;
use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use App\Services\Seo\BlogIndexability;
use App\Services\Seo\RecipeQualityGateService;
use App\Services\Seo\YaziIcBaglantilari;

/**
 * Üretim verisinde bir daha olmaması gereken şeyler.
 *
 * Neden var: bu depoda birden çok ajan çalışıyor ve her turda yeni bir kusur
 * çıkıyordu; hepsi ancak biri elle bakınca fark ediliyordu. Test paketi kodu
 * koruyor ama üretim verisinde oluşan bozulmaları göremiyor: 1.357 yönlendirme
 * dokuz hedefe yığıldığında, muhafız günlüğüne günde 9.899 satır yazıldığında
 * ya da kapalı sayfalara 5.207 çeviri işi açıldığında hiçbir test kırılmadı.
 *
 * Buradaki her kural, 20-22 Eylül 2026'da gerçekten yaşanmış bir kusurun
 * ölçülebilir hale getirilmiş halidir. Kural sayısı arttıkça aynı hatanın
 * ikinci kez sessizce geçmesi zorlaşır.
 */
final class SiteInvariants
{
    /** Bir muhafız bir günde bu kadar satır yazıyorsa kayıt değil gürültü üretiyordur. */
    public const GUNLUK_RAPOR_TAVANI = 500;

    /**
     * @return list<array{kural: string, ihlal: int, mesaj: string}>
     */
    public function ihlaller(): array
    {
        return array_values(array_filter([
            $this->yonlendirmeYigilmasi(),
            $this->raporSeli(),
            $this->kapaliSayfayaCeviriIsi(),
            $this->indeksteKusurluTarif(),
            $this->indeksteKapidanGecmemisYazi(),
            $this->ayniKonudaCoklaNusha(),
            $this->yazidaKirikIcBaglanti(),
        ]));
    }

    /** @return array{kural: string, ihlal: int, mesaj: string}|null */
    private function yonlendirmeYigilmasi(): ?array
    {
        $fazla = SlugRedirect::query()
            ->selectRaw('model_type, model_id, count(*) as adet')
            ->groupBy('model_type', 'model_id')
            ->having('adet', '>', SeoHealRedirects::HEDEF_BASINA_AZAMI)
            ->get();

        if ($fazla->isEmpty()) {
            return null;
        }

        return [
            'kural' => 'yonlendirme_yigilmasi',
            'ihlal' => (int) $fazla->sum('adet'),
            'mesaj' => $fazla->count().' hedef '.SeoHealRedirects::HEDEF_BASINA_AZAMI.'ten fazla eski adres alıyor; en yükseği '.(int) $fazla->max('adet').'. Yanlış yönlendirme 404\'ten zararlıdır.',
        ];
    }

    /** @return array{kural: string, ihlal: int, mesaj: string}|null */
    private function raporSeli(): ?array
    {
        $tasan = IntegrityReport::query()
            ->where('created_at', '>', now()->subDay())
            ->selectRaw('guard, count(*) as adet')
            ->groupBy('guard')
            ->having('adet', '>', self::GUNLUK_RAPOR_TAVANI)
            ->get();

        if ($tasan->isEmpty()) {
            return null;
        }

        return [
            'kural' => 'rapor_seli',
            'ihlal' => (int) $tasan->sum('adet'),
            'mesaj' => $tasan->pluck('adet', 'guard')->map(fn ($a, $g): string => $g.': '.$a)->implode(', ').' - muhafız günlüğü boğuluyor.',
        ];
    }

    /** @return array{kural: string, ihlal: int, mesaj: string}|null */
    private function kapaliSayfayaCeviriIsi(): ?array
    {
        $sayi = 0;

        foreach (ContentOpportunity::query()->where('kind', 'translation_gap')->where('status', 'open')->whereNull('reviewed_at')->cursor() as $is) {
            $pid = $is->related_public_ids[0] ?? null;

            if ($pid === null) {
                continue;
            }

            if ($this->kapaliMi((string) $pid) === true) {
                $sayi++;
            }
        }

        if ($sayi === 0) {
            return null;
        }

        return [
            'kural' => 'kapali_sayfaya_ceviri_isi',
            'ihlal' => $sayi,
            'mesaj' => $sayi.' çeviri işi indekse kapalı içerik için açık; gerçekten yapılacak işi gömüyor.',
        ];
    }

    private function kapaliMi(string $publicId): ?bool
    {
        $tarif = Recipe::query()->where('public_id', $publicId)->with('seoMetadata')->first();

        if ($tarif !== null) {
            return str_contains((string) $tarif->seoMetadata?->robots, 'noindex');
        }

        $yazi = BlogPost::query()->where('public_id', $publicId)->with('seoMetadata')->first();

        return $yazi === null ? null : str_contains(app(BlogIndexability::class)->robots($yazi), 'noindex');
    }

    /** @return array{kural: string, ihlal: int, mesaj: string}|null */
    private function indeksteKusurluTarif(): ?array
    {
        $kapi = app(RecipeQualityGateService::class);
        $kusurlu = [];

        foreach (Recipe::query()->where('status', 'published')->with(['steps', 'ingredients', 'recipeIngredients', 'seoMetadata'])->cursor() as $tarif) {
            if (str_contains((string) $tarif->seoMetadata?->robots, 'noindex')) {
                continue;
            }

            if ($kapi->eksikMi($tarif)) {
                $kusurlu[] = $tarif->slug;
            }
        }

        if ($kusurlu === []) {
            return null;
        }

        return [
            'kural' => 'indekste_kusurlu_tarif',
            'ihlal' => count($kusurlu),
            'mesaj' => count($kusurlu).' tarif indekse açık ama kalite kapısından geçmiyor: '.implode(', ', array_slice($kusurlu, 0, 5)),
        ];
    }

    /**
     * Kapıdan geçmeden yayına çıkmış ve eşine de bağlanmamış yazı.
     *
     * Kapıya takılan yazı zaten noindex oluyor, yani zarar vermiyor; ama
     * yayında durması bir yayın disiplini kusurudur ve sayısı sessizce
     * büyürse şablon içerik yeniden birikir. Bilinçli olarak eşine kanonikle
     * bağlanmış nüsha bunun dışındadır: orada karar kayıtlıdır.
     *
     * @return array{kural: string, ihlal: int, mesaj: string}|null
     */
    private function indeksteKapidanGecmemisYazi(): ?array
    {
        $indeks = app(BlogIndexability::class);
        $kusurlu = [];

        foreach (BlogPost::query()->where('status', 'published')->with('seoMetadata')->cursor() as $yazi) {
            if ($indeks->engeller($yazi) === []) {
                continue;
            }

            $kanonik = (string) $yazi->seoMetadata?->canonical_override;

            if ($kanonik !== '' && ! str_ends_with($kanonik, '/blog/'.$yazi->slug)) {
                continue;
            }

            $kusurlu[] = $yazi->slug;
        }

        if ($kusurlu === []) {
            return null;
        }

        return [
            'kural' => 'kapidan_gecmeden_yayinda',
            'ihlal' => count($kusurlu),
            'mesaj' => count($kusurlu).' yazı kapıdan geçmeden yayında ve eşine de bağlanmamış: '.implode(', ', array_slice($kusurlu, 0, 5)),
        ];
    }

    /** @return array{kural: string, ihlal: int, mesaj: string}|null */
    private function ayniKonudaCoklaNusha(): ?array
    {
        $indeks = app(BlogIndexability::class);

        $acik = BlogPost::query()->where('status', 'published')->with('seoMetadata')->get()
            ->reject(fn (BlogPost $y): bool => str_contains($indeks->robots($y), 'noindex'));

        $gruplar = $acik->groupBy(static fn (BlogPost $y): string => (string) preg_replace('/-[A-Z0-9]{5}$/', '', $y->slug))
            ->filter(static fn ($grup): bool => $grup->count() > 1);

        if ($gruplar->isEmpty()) {
            return null;
        }

        return [
            'kural' => 'ayni_konuda_cokla_nusha',
            'ihlal' => (int) $gruplar->flatten()->count(),
            'mesaj' => $gruplar->count().' konuda birden çok nüsha indekste; kendi sayfalarımız aynı sorguda yarışıyor: '.implode(', ', array_slice($gruplar->keys()->all(), 0, 3)),
        ];
    }

    /**
     * Yayındaki yazının gövdesinde, o dildeki canlı sayfaya doğrudan gitmeyen
     * tarif ya da yazı bağlantısı.
     *
     * 13 Ekim 2026'da 100 yazıda 50 adres 404'tü: 27 Eylül'de taslağa alınan
     * tariflere giden bağlantılar üç hafta kimse görmeden durdu.
     *
     * @return array{kural: string, ihlal: int, mesaj: string}|null
     */
    private function yazidaKirikIcBaglanti(): ?array
    {
        $baglantilar = app(YaziIcBaglantilari::class);
        $kusurlar = [];

        foreach (BlogPost::query()->publiclyVisible()->orderBy('id')->cursor() as $yazi) {
            foreach ($baglantilar->kusurlar($yazi) as $k) {
                $kusurlar[] = $k + ['yazi' => $yazi->slug];
            }
        }

        if ($kusurlar === []) {
            return null;
        }

        $olu = count(array_filter($kusurlar, static fn (array $k): bool => $k['hedef'] === null));
        $ornek = array_map(static fn (array $k): string => $k['dil'].':'.$k['adres'], array_slice($kusurlar, 0, 3));

        return [
            'kural' => 'yazida_kirik_ic_baglanti',
            'ihlal' => count($kusurlar),
            'mesaj' => count($kusurlar).' gövde bağlantısı o dildeki canlı sayfaya doğrudan gitmiyor ('.$olu.' tanesinin açık sayfası yok): '.implode(', ', $ornek).'. Onarım: blog:ic-baglanti-onar.',
        ];
    }
}
