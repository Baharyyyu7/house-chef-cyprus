<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Günlük koşu iş kaydı açar ve aynı sayfayı ikinci kez açmaz.
 */
final class DailyGrowthReportOpensCtrWorkTest extends TestCase
{
    use RefreshDatabase;

    private function gscVer(): void
    {
        config([
            'seo.search_console.enabled' => true,
            'seo.search_console.client_id' => 'x',
            'seo.search_console.client_secret' => 'y',
            'seo.search_console.refresh_token' => 'z',
        ]);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok']),
            'www.googleapis.com/*' => Http::response(['rows' => [
                ['keys' => ['https://housechefcyprus.com/blog/gorunen'], 'impressions' => 80, 'clicks' => 0, 'position' => 7.4],
                ['keys' => ['https://housechefcyprus.com/blog/tiklanan'], 'impressions' => 40, 'clicks' => 3, 'position' => 5.1],
            ]]),
        ]);
    }

    public function test_tiklanmayan_sayfa_icin_is_kaydi_acilir_ve_tekrarlanmaz(): void
    {
        $this->gscVer();

        $this->artisan('seo:daily-growth-report')->assertSuccessful();
        $this->artisan('seo:daily-growth-report')->assertSuccessful();

        $kayitlar = ContentOpportunity::query()->where('kind', 'ctr_rewrite')->get();
        $this->assertCount(1, $kayitlar);
        $this->assertSame('https://housechefcyprus.com/blog/gorunen', $kayitlar->first()->evidence['page']);
    }

    public function test_ikinci_kosu_bir_onceki_gunle_karsilastirir(): void
    {
        $this->gscVer();

        $this->artisan('seo:daily-growth-report')->assertSuccessful();
        IntegrityReport::query()->where('guard', 'seo_daily_growth')->update(['created_at' => now()->subDay()]);
        $this->artisan('seo:daily-growth-report')->assertSuccessful();

        $son = IntegrityReport::query()->where('guard', 'seo_daily_growth')->latest('id')->first();

        $this->assertSame('ayni', $son->payload['delta']['direction']);
        $this->assertSame(3, $son->payload['today']['clicks']);
        $this->assertSame(1, $son->payload['zero_click_pages']);
    }

    /**
     * Görev 07:05'te iki kez koşuyordu ve ikincisi 37 saniye önceki
     * birinciyle karşılaştırılıyordu: her sabah "aynı".
     */
    public function test_ayni_gunun_onceki_kosusu_dun_sayilmaz(): void
    {
        $this->gscVer();
        IntegrityReport::query()->create(['guard' => 'seo_daily_growth', 'status' => 'ok', 'headline' => 1, 'payload' => ['today' => ['clicks' => 1, 'impressions' => 50, 'pages' => 1, 'ctr' => 2.0, 'position' => 9.0]]])
            ->forceFill(['created_at' => now()->subDay()])->save();

        $this->artisan('seo:daily-growth-report')->assertSuccessful();
        $this->artisan('seo:daily-growth-report')->assertSuccessful();

        $son = IntegrityReport::query()->where('guard', 'seo_daily_growth')->latest('id')->first();

        $this->assertSame('buyudu', $son->payload['delta']['direction']);
        $this->assertSame(2, $son->payload['delta']['clicks']);
    }

    public function test_veri_yoksa_rapor_yazilmaz(): void
    {
        $this->artisan('seo:daily-growth-report')->assertSuccessful();

        $this->assertSame(0, IntegrityReport::query()->where('guard', 'seo_daily_growth')->count());
    }
}
