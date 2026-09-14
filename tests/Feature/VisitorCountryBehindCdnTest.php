<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AudienceCountryDay;
use App\Services\Geo\IpCountryResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * CDN arkasında gerçek ziyaretçi IP'si ve ülke sayacı.
 *
 * 12-13 Eylül 2026'da site Cloudflare'den Hostinger CDN'e geçti. Sayaç
 * ülkeyi CF-IPCountry başlığından okuyordu ve kayıt sessizce durdu. Ölçüm
 * bir sorun daha gösterdi: TRUSTED_PROXIES tanımlı ama bağlı değildi, yani
 * Laravel her ziyaretçiyi CDN sunucusu sanıyordu.
 */
final class VisitorCountryBehindCdnTest extends TestCase
{
    use RefreshDatabase;

    private const ZIYARETCI = '81.214.10.20';

    private function sahteCozucu(): IpCountryResolver
    {
        $sahte = new class extends IpCountryResolver
        {
            /** @var list<string|null> */
            public array $sorulan = [];

            public function country(?string $ip): ?string
            {
                $this->sorulan[] = $ip;

                return $ip === '81.214.10.20' ? 'TR' : null;
            }
        };

        $this->app->instance(IpCountryResolver::class, $sahte);

        return $sahte;
    }

    public function test_guvenilen_proxy_arkasinda_gercek_ziyaretci_ip_si_okunur(): void
    {
        config(['production.trusted_proxies' => '*']);
        Route::get('/_ip-olcum', fn (Request $istek) => $istek->ip());

        $this->get('/_ip-olcum', ['X-Forwarded-For' => self::ZIYARETCI])
            ->assertOk()
            ->assertSeeText(self::ZIYARETCI);
    }

    public function test_proxy_ayari_yoksa_baslik_uydurulamaz(): void
    {
        config(['production.trusted_proxies' => null]);
        Route::get('/_ip-olcum', fn (Request $istek) => $istek->ip());

        $this->get('/_ip-olcum', ['X-Forwarded-For' => self::ZIYARETCI])
            ->assertOk()
            ->assertDontSeeText(self::ZIYARETCI);
    }

    public function test_ulke_basligi_yoksa_ulke_ip_den_bulunur_ve_kaydedilir(): void
    {
        config(['production.trusted_proxies' => '*']);
        $cozucu = $this->sahteCozucu();

        $this->get('/', ['X-Forwarded-For' => self::ZIYARETCI])->assertOk();

        $this->assertContains(self::ZIYARETCI, $cozucu->sorulan, 'Çözücüye CDN adresi değil, ziyaretçi IP\'si sorulmalı.');
        $this->assertSame(1, (int) AudienceCountryDay::query()->where('country_code', 'TR')->value('hits'));
    }

    public function test_cdn_ulke_basligi_varsa_ip_ye_bakilmaz(): void
    {
        config(['production.trusted_proxies' => '*']);
        $cozucu = $this->sahteCozucu();

        $this->get('/', ['X-Forwarded-For' => self::ZIYARETCI, 'CF-IPCountry' => 'DE'])->assertOk();

        $this->assertSame([], $cozucu->sorulan);
        $this->assertSame(1, (int) AudienceCountryDay::query()->where('country_code', 'DE')->value('hits'));
    }

    public function test_ip_hicbir_tabloya_ya_da_onbellege_yazilmaz(): void
    {
        config(['production.trusted_proxies' => '*']);
        $this->sahteCozucu();

        $this->get('/', ['X-Forwarded-For' => self::ZIYARETCI])->assertOk();

        $satirlar = json_encode(DB::table('audience_country_days')->get());
        $this->assertStringNotContainsString(self::ZIYARETCI, (string) $satirlar);
        $this->assertStringNotContainsString(self::ZIYARETCI, (string) json_encode(cache()->get('audience:cografi-anahtar-adlari')));
    }

    public function test_ozel_ag_adresi_hicbir_ulkeye_yazilmaz(): void
    {
        // Proxy arkasında gerçek IP'ye ulaşılamadıysa gelen adres özel ağdan
        // olur; tahmin yürütmek yerine boş dönülmeli.
        $cozucu = new IpCountryResolver;

        foreach (['127.0.0.1', '10.1.2.3', '172.16.5.4', '192.168.1.1', '::1', 'gecersiz'] as $adres) {
            $this->assertNull($cozucu->country($adres), $adres);
        }
    }

    public function test_bozuk_indirme_eski_veritabanina_dokunmaz(): void
    {
        $yol = storage_path('framework/testing/geoip-'.uniqid().'.mmdb');
        File::ensureDirectoryExists(dirname($yol));
        File::put($yol, 'ESKI-CALISAN-DOSYA');
        config(['geoip.database' => $yol, 'geoip.download_url' => 'https://ornek.test/dbip-%s.mmdb.gz']);
        Http::fake(['*' => Http::response('bozuk-icerik', 200)]);

        $this->artisan('geoip:update --force')->assertFailed();

        $this->assertSame('ESKI-CALISAN-DOSYA', File::get($yol));
        $this->assertFileDoesNotExist($yol.'.yeni');
        File::delete($yol);
    }
}
