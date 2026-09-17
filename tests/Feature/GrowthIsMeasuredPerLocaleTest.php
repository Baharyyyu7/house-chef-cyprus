<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\LocaleGrowthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Büyüme dil dil ölçülür.
 *
 * Büyüme motoru ne ölçebiliyorsa onu iyileştirebilir. Tek yığın halinde
 * toplanan tıklama ve gösterim, beş dilli bir sitede dördünün ne yaptığını
 * gizler: Almanca hiç tıklama almıyorsa toplam içinde görünmez.
 */
final class GrowthIsMeasuredPerLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
        config()->set('locales.supported.de.enabled', true);
    }

    private function olcum(string $sayfa, int $tiklama, int $gosterim, ?float $sira = null): void
    {
        DB::table('search_performance_daily')->insert([
            'performance_date' => now()->subDays(3)->toDateString(),
            'page' => $sayfa,
            'query_hash' => md5($sayfa.$tiklama.$gosterim),
            'country' => 'cyp',
            'device' => 'DESKTOP',
            'clicks' => $tiklama,
            'impressions' => $gosterim,
            'ctr' => $gosterim > 0 ? $tiklama / $gosterim : 0,
            'average_position' => $sira,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_adresten_dil_turetilir(): void
    {
        $servis = app(LocaleGrowthService::class);

        $this->assertSame('tr', $servis->adresinDili('https://x.test/tarifler/hellim'));
        $this->assertSame('en', $servis->adresinDili('https://x.test/en/recipes/hellim'));
        $this->assertSame('de', $servis->adresinDili('https://x.test/de/blog/x'));
        $this->assertSame('tr', $servis->adresinDili('https://x.test/'));
        // Dil gibi görünen ama dil olmayan bir ilk parça varsayılana düşer.
        $this->assertSame('tr', $servis->adresinDili('https://x.test/blog/x'));
    }

    public function test_tiklama_ve_gosterim_dile_gore_ayrilir(): void
    {
        $this->olcum('https://x.test/tarifler/a', 10, 100);
        $this->olcum('https://x.test/en/recipes/a', 3, 60);
        $this->olcum('https://x.test/de/rezepte/a', 0, 40);

        $p = app(LocaleGrowthService::class)->aramaPerformansi();

        $this->assertSame(10, $p['tr']['clicks']);
        $this->assertSame(3, $p['en']['clicks']);
        $this->assertSame(0, $p['de']['clicks']);
        $this->assertSame(40, $p['de']['impressions']);
    }

    public function test_sira_ortalamasi_gosterimle_agirliklandirilir(): void
    {
        // Bir kez gösterilen 1. sıra ile 99 kez gösterilen 11. sıra.
        // Düz ortalama 6 derdi; gerçekte kullanıcılar 11. sırayı görüyor.
        $this->olcum('https://x.test/en/a', 0, 1, 1.0);
        $this->olcum('https://x.test/en/b', 0, 99, 11.0);

        $p = app(LocaleGrowthService::class)->aramaPerformansi();

        $this->assertSame(10.9, $p['en']['position']);
    }

    public function test_darbogaz_icerik_yoklugunu_indeksleme_sorunundan_ayirir(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellim', 'slug' => 'hellim-x', 'excerpt' => 'Ö.', 'description' => 'A.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        foreach (['title' => 'Halloumi', 'excerpt' => 'S.', 'description' => 'D.'] as $a => $d) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $a, 'value' => $d]);
        }

        $tablo = app(LocaleGrowthService::class)->tablo();

        // İngilizcede sayfa var ama o dile ait hiç ölçüm satırı yok. Bu
        // "indekslenmedi" DEĞİL: indekslenmediğini söyleyebilmek için önce
        // ölçmüş olmak gerekir. İkisini ayırmazsak Search Console bağlı
        // olmadığı her gün sahte bir indeksleme alarmı üretirdik.
        $this->assertSame(1, $tablo['en']['recipes']);
        $this->assertStringContainsString('ölçüm yok', $tablo['en']['darbogaz']);
        $this->assertFalse($tablo['en']['measurement_available']);

        // Almancada hiç sayfa yok: bu içerik sorunu, ayrı bir iş.
        $this->assertSame(0, $tablo['de']['recipes']);
        $this->assertStringContainsString('içerik yok', $tablo['de']['darbogaz']);
    }

    public function test_olcum_var_ama_gosterim_yoksa_iddia_edilmez_dogrulanmasi_istenir(): void
    {
        // Üç durum üst üste biniyor ve teşhis her adımda biraz daha
        // temkinli olmak zorunda:
        //   1. dile ait hiç ölçüm satırı yok  -> hiçbir şey söylenemez
        //   2. satır var ama gösterim sıfır   -> indeksleme ŞÜPHESİ
        //   3. gösterim var, tıklama yok      -> başlık/sıralama işi
        //
        // İkinci durumda "indekslenmemiş" demek bir iddiadır ve sıfır
        // gösterim bunu kanıtlamaz: sayfa indekslenmiş ama hiç sorguyla
        // eşleşmemiş de olabilir. Teşhis yerine doğrulama istiyor.
        $tarif = Recipe::query()->create([
            'title' => 'Hellim', 'slug' => 'hellim-z', 'excerpt' => 'Ö.', 'description' => 'A.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        foreach (['title' => 'Halloumi', 'excerpt' => 'S.', 'description' => 'D.'] as $a => $d) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $a, 'value' => $d]);
        }

        // Aynı dile ait, gösterimi sıfır olan bir ölçüm satırı.
        $this->olcum('https://x.test/en/recipes/baska-sayfa', 0, 0);

        $tablo = app(LocaleGrowthService::class)->tablo();

        $this->assertTrue($tablo['en']['measurement_available']);
        $this->assertStringContainsString('doğrulanmalı', $tablo['en']['darbogaz']);
        $this->assertStringNotContainsString(
            'indekslenmemiş',
            $tablo['en']['darbogaz'],
            'Sıfır gösterim indekslenmediğini kanıtlamaz; teşhis iddia etmemeli.'
        );
    }

    public function test_gosterim_varsa_darbogaz_artik_olcume_dayanir(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellim', 'slug' => 'hellim-w', 'excerpt' => 'Ö.', 'description' => 'A.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        foreach (['title' => 'Halloumi', 'excerpt' => 'S.', 'description' => 'D.'] as $a => $d) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $a, 'value' => $d]);
        }

        $this->olcum('https://x.test/en/recipes/hellim-w', 12, 400, 4.0);

        $tablo = app(LocaleGrowthService::class)->tablo();

        $this->assertSame(12, $tablo['en']['clicks']);
        $this->assertSame(3.0, $tablo['en']['ctr']);
        $this->assertStringContainsString('ölçüm var', $tablo['en']['darbogaz']);
    }

    public function test_gosterim_varken_tiklama_yoksa_baslik_sorunu_denir(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellim', 'slug' => 'hellim-y', 'excerpt' => 'Ö.', 'description' => 'A.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        foreach (['title' => 'Halloumi', 'excerpt' => 'S.', 'description' => 'D.'] as $a => $d) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $a, 'value' => $d]);
        }
        $this->olcum('https://x.test/en/recipes/hellim-y', 0, 500, 8.0);

        $tablo = app(LocaleGrowthService::class)->tablo();

        $this->assertStringContainsString('tıklanmıyor', $tablo['en']['darbogaz']);
    }

    public function test_kapali_dil_tabloda_yer_almaz(): void
    {
        config()->set('locales.supported.de.enabled', false);

        $this->assertArrayNotHasKey('de', app(LocaleGrowthService::class)->tablo());
    }
}
