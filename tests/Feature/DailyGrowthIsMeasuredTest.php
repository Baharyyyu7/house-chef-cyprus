<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Seo\DailyGrowthService;
use Tests\TestCase;

/**
 * "Her gün bir öncekinden yüksek" bir iddia; ölçülmeden söylenemez.
 *
 * 20 Eylül 2026: 572 gösterim, 6 tıklama, sıra 9,7. Site görünüyor ama
 * tıklanmıyor. Bu test ölçümün ve tıklanmayan sayfa ayıklamasının
 * sabitlenmesidir.
 */
final class DailyGrowthIsMeasuredTest extends TestCase
{
    /** @return list<array{page: string, impressions: int, clicks: int, position: float}> */
    private function satirlar(): array
    {
        return [
            ['page' => 'https://housechefcyprus.com/blog/a', 'impressions' => 100, 'clicks' => 4, 'position' => 8.0],
            ['page' => 'https://housechefcyprus.com/blog/b', 'impressions' => 60, 'clicks' => 0, 'position' => 6.0],
            ['page' => 'https://housechefcyprus.com/blog/c', 'impressions' => 40, 'clicks' => 0, 'position' => 12.0],
            ['page' => 'https://housechefcyprus.com/blog/d', 'impressions' => 9, 'clicks' => 0, 'position' => 5.0],
            ['page' => 'https://housechefcyprus.com/blog/e', 'impressions' => 50, 'clicks' => 0, 'position' => 44.0],
        ];
    }

    public function test_toplam_gosterime_gore_agirlikli_sira_verir(): void
    {
        $toplam = app(DailyGrowthService::class)->toplam($this->satirlar());

        $this->assertSame(4, $toplam['clicks']);
        $this->assertSame(259, $toplam['impressions']);
        $this->assertSame(5, $toplam['pages']);
        $this->assertSame(1.54, $toplam['ctr']);
        $this->assertSame(round((100 * 8 + 60 * 6 + 40 * 12 + 9 * 5 + 50 * 44) / 259, 2), $toplam['position']);
    }

    public function test_tiklanmayanlar_yalniz_ilk_yirmide_ve_yeterli_gosterimde(): void
    {
        $firsatlar = app(DailyGrowthService::class)->tiklanmayanlar($this->satirlar());

        $this->assertSame(['https://housechefcyprus.com/blog/b', 'https://housechefcyprus.com/blog/c'], array_column($firsatlar, 'page'));
    }

    public function test_onceki_olcum_yoksa_yon_bilinmiyordur(): void
    {
        $servis = app(DailyGrowthService::class);
        $bugun = $servis->toplam($this->satirlar());

        $this->assertSame('bilinmiyor', $servis->fark($bugun, null)['direction']);
        $this->assertNull($servis->fark($bugun, null)['clicks']);
    }

    public function test_tiklama_artarsa_buyudu_azalirsa_kucculdu(): void
    {
        $servis = app(DailyGrowthService::class);
        $bugun = $servis->toplam($this->satirlar());

        $this->assertSame('buyudu', $servis->fark($bugun, [...$bugun, 'clicks' => 2])['direction']);
        $this->assertSame('kucculdu', $servis->fark($bugun, [...$bugun, 'clicks' => 9])['direction']);
        // Tıklama aynıysa yönü gösterim söyler.
        $this->assertSame('buyudu', $servis->fark($bugun, [...$bugun, 'impressions' => 200])['direction']);
        $this->assertSame('ayni', $servis->fark($bugun, $bugun)['direction']);
    }
}
