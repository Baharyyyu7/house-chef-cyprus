<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Büyümeyi dil dil ölçer.
 *
 * Büyüme motoru ne ölçebiliyorsa onu iyileştirebilir. Bugüne kadar ölçüm tek
 * bir yığındı: tıklama, gösterim ve sıra site geneli toplanıyordu. Site tek
 * dilliyken bu doğruydu; beş dilli bir sitede aynı toplam, dördünün ne
 * yaptığını gizler. Almanca hiç tıklama almıyorsa toplam içinde görünmez ve
 * kimse bakmaz.
 *
 * Dil sütunu eklemiyoruz. Adres zaten dili taşıyor (/en/..., /de/...) ve
 * türetmek iki şeyi birden kazandırıyor: geçmiş veri de bölünebiliyor ve
 * ölçümün tanımı ile sitenin adres yapısı tek kaynakta kalıyor. İkisi ayrı
 * tutulsaydı, biri değişip diğeri unutulduğunda ölçüm sessizce yanlışlanırdı.
 */
final class LocaleGrowthService
{
    public function __construct(
        private readonly LocaleUrlService $diller
    ) {
    }

    /**
     * Bir adresin hangi dile ait olduğu.
     *
     * Önek yoksa varsayılan dildir; varsayılan dil kökte yaşıyor.
     */
    public function adresinDili(string $adres): string
    {
        $yol = (string) (parse_url($adres, PHP_URL_PATH) ?? $adres);
        $ilk = strtok(ltrim($yol, '/'), '/');

        if (! is_string($ilk) || $ilk === '') {
            return $this->diller->defaultLocale();
        }

        return array_key_exists($ilk, (array) config('locales.supported', []))
            ? $ilk
            : $this->diller->defaultLocale();
    }

    /**
     * Dil başına arama performansı.
     *
     * @return array<string, array{clicks: int, impressions: int, pages: int, ctr: float, position: float|null}>
     */
    public function aramaPerformansi(int $gun = 28): array
    {
        $satirlar = DB::table('search_performance_daily')
            ->whereBetween('performance_date', [Carbon::now()->subDays(max(1, min(90, $gun)))->toDateString(), Carbon::yesterday()->toDateString()])
            ->select('page', 'clicks', 'impressions', 'average_position')
            ->get();

        $toplam = [];

        foreach ($satirlar as $satir) {
            $dil = $this->adresinDili((string) $satir->page);

            $toplam[$dil] ??= ['clicks' => 0, 'impressions' => 0, 'pages' => [], 'siraToplami' => 0.0, 'siraAdedi' => 0];
            $toplam[$dil]['clicks'] += (int) $satir->clicks;
            $toplam[$dil]['impressions'] += (int) $satir->impressions;
            $toplam[$dil]['pages'][(string) $satir->page] = true;

            // Sıra ortalaması gösterimle ağırlıklandırılır. Düz ortalama,
            // bir kez gösterilen bir sayfayı binlerce kez gösterilen bir
            // sayfayla eşit sayar ve tabloyu gerçekten okunmayan sayfalar
            // belirler.
            if ($satir->average_position !== null && (int) $satir->impressions > 0) {
                $toplam[$dil]['siraToplami'] += (float) $satir->average_position * (int) $satir->impressions;
                $toplam[$dil]['siraAdedi'] += (int) $satir->impressions;
            }
        }

        $sonuc = [];

        foreach ($toplam as $dil => $veri) {
            $sonuc[$dil] = [
                'clicks' => $veri['clicks'],
                'impressions' => $veri['impressions'],
                'pages' => count($veri['pages']),
                'ctr' => $veri['impressions'] > 0 ? round($veri['clicks'] / $veri['impressions'] * 100, 2) : 0.0,
                'position' => $veri['siraAdedi'] > 0 ? round($veri['siraToplami'] / $veri['siraAdedi'], 1) : null,
            ];
        }

        return $sonuc;
    }

    /**
     * Dil başına yayına hazır içerik sayısı.
     *
     * Performansın yanında bu durmadan performans yanlış okunur: tıklama
     * almayan bir dilin sorunu sıralama olmayabilir, ortada sayfa olmaması
     * olabilir. İkisi ayrı sorun ve ayrı çözümleri var.
     *
     * @return array<string, array{recipes: int, posts: int}>
     */
    public function icerikKapsami(): array
    {
        $sonuc = [];

        foreach (array_keys($this->diller->enabledLocales()) as $dil) {
            $sonuc[$dil] = [
                'recipes' => Recipe::query()
                    ->where('status', 'published')
                    ->where('verification_status', 'verified')
                    ->translatedInto($dil)
                    ->count(),
                'posts' => BlogPost::query()->publiclyVisible()->translatedInto($dil)->count(),
            ];
        }

        return $sonuc;
    }

    /**
     * Her açık dil için tek satırlık durum.
     *
     * Açık ama içeriği olmayan dil burada görünür; büyüme motorunun ilk
     * bakacağı yer burası olmalı, çünkü ölçemediği dili büyütemez.
     *
     * @return array<string, array<string, mixed>>
     */
    public function tablo(int $gun = 28): array
    {
        $performans = $this->aramaPerformansi($gun);
        $kapsam = $this->icerikKapsami();
        $tablo = [];

        foreach ($kapsam as $dil => $sayilar) {
            $p = $performans[$dil] ?? ['clicks' => 0, 'impressions' => 0, 'pages' => 0, 'ctr' => 0.0, 'position' => null];

            $tablo[$dil] = [
                'measurement_available' => array_key_exists($dil, $performans),
                'locale' => $dil,
                'recipes' => $sayilar['recipes'],
                'posts' => $sayilar['posts'],
                'clicks' => $p['clicks'],
                'impressions' => $p['impressions'],
                'ctr' => $p['ctr'],
                'position' => $p['position'],
                'darbogaz' => ! array_key_exists($dil, $performans) && ($sayilar['recipes'] + $sayilar['posts']) > 0
                    ? 'ölçüm yok: indeksleme durumu bu veriden belirlenemez'
                    : $this->darbogaz($sayilar, $p),
            ];
        }

        return $tablo;
    }

    /**
     * Bu dilin önündeki asıl engel ne?
     *
     * Tek cümlelik teşhis. Üç durum birbirine karışıyor ve karıştıkları
     * sürece yanlış işe emek harcanıyor: sayfa yokluğu içerik işidir,
     * gösterim yokluğu indeksleme işidir, tıklama yokluğu başlık ve açıklama
     * işidir. Üçü için de aynı şeyi yapmak üçünü de çözmez.
     *
     * @param  array{recipes: int, posts: int}  $kapsam
     * @param  array{clicks: int, impressions: int, pages: int, ctr: float, position: float|null}  $performans
     */
    private function darbogaz(array $kapsam, array $performans): string
    {
        $sayfa = $kapsam['recipes'] + $kapsam['posts'];

        if ($sayfa === 0) {
            return 'içerik yok: bu dilde çevrilmiş tek sayfa bile yok';
        }

        if ($performans['impressions'] === 0) {
            return 'gösterim kaydı yok: indeksleme ayrıca doğrulanmalı';
        }

        if ($performans['clicks'] === 0) {
            return 'tıklanmıyor: sorgu, sıralama ve başlık uyumu incelenmeli';
        }

        if ($performans['position'] !== null && $performans['position'] > 20.0) {
            return 'sıralama düşük: ortalama '.$performans['position'].', ilk sayfanın gerisinde';
        }

        return 'ölçüm var: büyüme için önceki dönemle karşılaştırılmalı';
    }
}
