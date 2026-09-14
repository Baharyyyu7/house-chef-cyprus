<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AudienceCountryDay;
use App\Models\User;
use App\Services\AdminDashboardService;
use App\Support\WorldMapCountries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Yönetim haritası coğrafi olarak dürüsttür.
 *
 * 14 Eylül 2026'ya kadar harita düz enlem-boylam projeksiyonu kullanıyor ve
 * koordinatları tam sayıya yuvarlıyordu. İşaret noktası bütün sınır
 * noktalarının ortalamasıydı; ABD'nin noktası Alaska yüzünden batıya
 * kayıyordu. Daha kötüsü, ülke tablosu kaldırılmış ISO kodları taşıyordu:
 * Zimbabve RH, Yemen YD, Vanuatu NH. Cloudflare güncel kodu gönderdiği için
 * o ülkelerden gelen ziyaretler hiç sayılmıyordu ve bu hiçbir yerde hata
 * olarak görünmüyordu.
 */
final class AdminWorldMapIsGeographicallyHonestTest extends TestCase
{
    use RefreshDatabase;

    private function ziyaret(string $kod, int $sayi, int $gunOnce = 0): void
    {
        AudienceCountryDay::query()->create([
            'day' => now()->subDays($gunOnce)->toDateString(),
            'country_code' => $kod,
            'hits' => $sayi,
        ]);
    }

    public function test_kaldirilmis_iso_kodlari_tabloda_yok_guncelleri_var(): void
    {
        foreach (['RH', 'YD', 'NH', 'ZR', 'YU', 'SU', 'DD', 'TP', 'BU'] as $eski) {
            $this->assertArrayNotHasKey($eski, WorldMapCountries::ALL, $eski.' kaldırılmış bir kod; Cloudflare bunu hiç göndermez.');
        }

        // ZW/YE/VU eskiden eksikti; MT/SG/BH/CY küçük ama ziyaret gelen ülkeler.
        foreach (['ZW', 'YE', 'VU', 'MT', 'SG', 'BH', 'CY', 'TR', 'US'] as $guncel) {
            $this->assertArrayHasKey($guncel, WorldMapCountries::ALL, $guncel.' haritada yok; bu ülkeden gelen ziyaret sayılmaz.');
        }
    }

    public function test_zimbabveden_gelen_ziyaret_artik_sayilir(): void
    {
        $this->ziyaret('ZW', 3);

        $harita = app(AdminDashboardService::class)->audienceMapForPeriod(7);

        $this->assertSame('available', $harita['data_status']);
        $this->assertSame('ZW', $harita['countries'][0]['country_code']);
    }

    public function test_her_ulke_yolu_icin_tablo_satiri_var_ve_tersi(): void
    {
        $svg = (string) file_get_contents(resource_path('views/components/admin/world-map-countries.blade.php'));
        preg_match_all('/id="ulke-([A-Z]{2})"/', $svg, $eslesme);

        $yollar = array_unique($eslesme[1]);
        sort($yollar);
        $tablo = array_keys(WorldMapCountries::ALL);
        sort($tablo);

        // Tabloda olup yolu olmayan ülke boyanamaz; yolu olup tabloda
        // olmayan ülkeden gelen ziyaret sayılmaz.
        $this->assertSame($tablo, $yollar);
    }

    public function test_isaret_noktasi_ulkenin_kendi_kutusunda_durur(): void
    {
        [$gx, $gy, $gw, $gh] = WorldMapCountries::VIEWBOX;

        foreach (WorldMapCountries::ALL as $kod => $ulke) {
            [$bx, $by, $bw, $bh] = $ulke['box'];

            $this->assertTrue(
                $ulke['x'] >= $bx - 0.2 && $ulke['x'] <= $bx + $bw + 0.2 && $ulke['y'] >= $by - 0.2 && $ulke['y'] <= $by + $bh + 0.2,
                $kod.' işareti kendi sınır kutusunun dışında; nokta başka bir ülkenin üstüne düşer.'
            );
            $this->assertTrue(
                $ulke['x'] >= $gx && $ulke['x'] <= $gx + $gw && $ulke['y'] >= $gy && $ulke['y'] <= $gy + $gh,
                $kod.' işareti görüş alanının dışında.'
            );
        }
    }

    public function test_abd_isareti_alaskaya_kaymaz(): void
    {
        // Alaska dahil edilseydi işaret batıya, Pasifik'e kayardı ve ABD'ye
        // yakınlaşan kişi yarısı okyanus olan bir kutu görürdü.
        //
        // Kanada ile kıyaslamak işe yaramıyor: Equal Earth yüksek enlemde
        // yatayı sıkıştırdığı için Kanada'nın batı kıyısı ABD'ninkinden
        // sağda çıkar. Ölçülen değerler: Alaska'nın batı ucu (168°B, 65.6°K)
        // x≈161; 48 eyaletin Pasifik kıyısı x≈185.6, kutu genişliği ≈142.
        // Alaska dahil olsaydı kutu 161'den başlar ve ≈165 genişlikte olurdu.
        $abd = WorldMapCountries::ALL['US'];

        $this->assertGreaterThan(175, $abd['box'][0], 'ABD kutusu Alaska\'yı içeriyor.');
        $this->assertLessThan(155, $abd['box'][2], 'ABD kutusu 48 eyaletten geniş; Alaska dahil olmuş.');
        $this->assertGreaterThan(200, $abd['x'], 'ABD işareti Pasifik kıyısına kaymış.');
    }

    public function test_harita_donemi_panelden_bagimsizdir_ve_tumu_eski_kayitlari_kapsar(): void
    {
        $this->ziyaret('TR', 5);
        $this->ziyaret('DE', 3, 400);
        Cache::flush();

        $servis = app(AdminDashboardService::class);

        $this->assertSame(['TR'], array_column($servis->audienceMapForPeriod(7)['countries'], 'country_code'));
        $this->assertSame(['TR'], array_column($servis->audienceMapForPeriod(365)['countries'], 'country_code'));
        $this->assertSame(['TR', 'DE'], array_column($servis->audienceMapForPeriod(0)['countries'], 'country_code'));

        $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]))
            ->get(route('admin.dashboard', ['period' => 7, 'harita' => 0]))
            ->assertOk()
            ->assertSee('Almanya')
            ->assertSee('harita=365', false);
    }

    public function test_gecersiz_harita_donemi_panel_donemine_duser(): void
    {
        $this->ziyaret('TR', 5);
        $this->ziyaret('DE', 3, 400);
        Cache::flush();

        $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]))
            ->get(route('admin.dashboard', ['period' => 7, 'harita' => 5]))
            ->assertOk()
            ->assertDontSee('Almanya');
    }

    public function test_toplam_butun_ulkeleri_sayar_yalniz_ilk_sekizi_degil(): void
    {
        foreach (['TR', 'US', 'DE', 'FR', 'GB', 'NL', 'IT', 'ES', 'GR', 'CY'] as $sira => $kod) {
            $this->ziyaret($kod, 100 - $sira);
        }
        Cache::flush();

        $harita = app(AdminDashboardService::class)->audienceMapForPeriod(7);

        // Önceden yalnız ilk 8 ülke toplanıyordu; 9. ve 10. ülke düşüyordu.
        $this->assertSame(array_sum(range(91, 100)), $harita['total_members']);
        $this->assertCount(10, $harita['countries']);
        $this->assertEqualsWithDelta(100.0, array_sum(array_column($harita['countries'], 'share')), 0.5);
    }

    public function test_sayac_durduysa_kart_bunu_soyler(): void
    {
        // Site ülke başlığı göndermeyen bir CDN'e taşındığında sayaç hata
        // vermeden durdu; kart günlerce eksik rakamı normalmiş gibi gösterdi.
        $this->ziyaret('TR', 5, 3);
        Cache::flush();

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 7, 'harita' => 7]))
            ->assertOk()
            ->assertSee('tarihinden beri yeni kayıt almıyor');

        $this->ziyaret('DE', 1);
        Cache::flush();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 7, 'harita' => 7]))
            ->assertOk()
            ->assertDontSee('tarihinden beri yeni kayıt almıyor');
    }

    public function test_kart_yakinlastirma_ve_donem_secimi_sunar(): void
    {
        $ulke = WorldMapCountries::ALL['TR'];
        $map = [
            'source' => 'visitor_country_aggregate', 'data_status' => 'available', 'summary' => 'x',
            'total_members' => 12, 'period' => 30, 'periodLabel' => '30 gün',
            'countries' => [['country_code' => 'TR', 'label' => 'Türkiye', 'members' => 12, 'share' => 100.0, 'x' => $ulke['x'], 'y' => $ulke['y'], 'box' => $ulke['box']]],
        ];

        $html = Blade::render(
            '<x-admin.world-activity-map :map="$map" :periods="$periods" :dashboard-period="7" />',
            ['map' => $map, 'periods' => AdminDashboardService::MAP_PERIODS]
        );

        $this->assertStringContainsString('aria-label="Yakınlaştır"', $html);
        $this->assertStringContainsString('aria-label="Bütün dünyayı göster"', $html);
        $this->assertStringContainsString('ulkeyeGit(', $html);
        $this->assertMatchesRegularExpression('/harita=30[^"]*#ulke-haritasi"\s+class="hc-admin-period hc-admin-period--active"/', $html);
        // En-boy oranı üretilen görüş kutusundan gelir; sabit bir oran imleç
        // ile harita koordinatını birbirinden kaydırırdı.
        $this->assertStringContainsString('aspect-ratio: '.WorldMapCountries::VIEWBOX[2].' / '.WorldMapCountries::VIEWBOX[3], $html);
    }
}
