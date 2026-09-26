<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ConiAction;
use App\Models\ContentTranslation;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Services\Coni\ClaudeConi;
use App\Services\Coni\ConiDegerlendirici;
use App\Services\Coni\ConiOdakKumesi;
use App\Services\Coni\ConiOdakSecici;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Claude Coni: ölç, seç, uygula, değerlendir, geri al.
 *
 * Sabitlenen davranışlar: odak her dilde tek sayfa; sonuç tıklamayla
 * ölçülür; yapılan her değişiklik eski değeriyle deftere yazılır ve geri
 * alınabilir; ölçüm yoksa hiçbir şey seçilmez.
 */
final class ClaudeConiTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug, array $ek = []): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Yeterince uzun bir açıklama metni burada duruyor ve eşiği rahatça aşıyor.',
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
            ...$ek,
        ]);
    }

    /** @param list<array{0: string, 1: int, 2: int, 3: float}>|callable $satirlar */
    private function gsc(array|callable $satirlar): void
    {
        config([
            'seo.search_console.enabled' => true,
            'seo.search_console.client_id' => 'x',
            'seo.search_console.client_secret' => 'y',
            'seo.search_console.refresh_token' => 'z',
        ]);

        $yanit = static fn (array $liste) => Http::response(['rows' => array_map(
            static fn (array $s): array => ['keys' => [$s[0]], 'clicks' => $s[1], 'impressions' => $s[2], 'position' => $s[3]],
            $liste
        )]);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok']),
            'www.googleapis.com/*' => is_callable($satirlar)
                ? static fn (Request $istek) => $yanit($satirlar($istek->data()))
                : $yanit($satirlar),
        ]);
    }

    public function test_aday_ilk_ucun_disinda_ve_yirminin_icinde_olmali(): void
    {
        $adaylar = app(ConiOdakSecici::class)->adaylar([
            ['page' => 'https://x.test/a', 'impressions' => 400, 'clicks' => 2, 'position' => 7.0],
            ['page' => 'https://x.test/b', 'impressions' => 900, 'clicks' => 90, 'position' => 2.1],
            ['page' => 'https://x.test/c', 'impressions' => 500, 'clicks' => 0, 'position' => 31.0],
            ['page' => 'https://x.test/d', 'impressions' => 20, 'clicks' => 0, 'position' => 6.0],
            ['page' => 'https://x.test/e', 'impressions' => 100, 'clicks' => 1, 'position' => 9.0],
        ]);

        $this->assertSame(['https://x.test/a', 'https://x.test/e'], array_column($adaylar, 'page'));
        // İlk üçe çıksaydı: 400 * (0.11 - 0.005) = 42 ek tıklama.
        $this->assertSame(42.0, $adaylar[0]['potansiyel']);
    }

    public function test_dislanan_sayfa_aday_olmaz(): void
    {
        $adaylar = app(ConiOdakSecici::class)->adaylar(
            [['page' => 'https://x.test/a', 'impressions' => 400, 'clicks' => 2, 'position' => 7.0]],
            ['https://x.test/a/']
        );

        $this->assertSame([], $adaylar);
    }

    public function test_bes_karakterlik_ekle_ayrisan_adresler_nusha_sayilir(): void
    {
        $nushalar = app(ConiOdakSecici::class)->nushalar([
            ['page' => 'https://x.test/blog/firin-dereceleri', 'impressions' => 128, 'clicks' => 0, 'position' => 7.5],
            ['page' => 'https://x.test/blog/firin-dereceleri-B253B', 'impressions' => 46, 'clicks' => 0, 'position' => 12.0],
            ['page' => 'https://x.test/blog/baska', 'impressions' => 10, 'clicks' => 0, 'position' => 5.0],
        ]);

        $this->assertCount(1, $nushalar);
        $this->assertSame(174, $nushalar[0]['impressions']);
    }

    public function test_sonuc_tiklamayla_olculur_kucuk_dalgalanma_notrdur(): void
    {
        $d = app(ConiDegerlendirici::class);
        $once = ['clicks' => 10, 'impressions' => 500, 'position' => 8.0];

        $this->assertSame(ConiAction::KAZANC, $d->sonuc($once, ['clicks' => 13, 'impressions' => 500, 'position' => 8.0])['status']);
        $this->assertSame(ConiAction::KAYIP, $d->sonuc($once, ['clicks' => 7, 'impressions' => 500, 'position' => 8.0])['status']);
        $this->assertSame(ConiAction::NOTR, $d->sonuc($once, ['clicks' => 12, 'impressions' => 500, 'position' => 8.0])['status']);
        // Sıra yükselip tıklama gelmediyse kazanç yazılmaz.
        $this->assertSame(ConiAction::NOTR, $d->sonuc($once, ['clicks' => 10, 'impressions' => 1500, 'position' => 4.0])['status']);
    }

    public function test_her_dilde_tek_odak_secilir_ve_ikinci_kosu_tekrar_secmez(): void
    {
        $tarif = $this->tarif('hellim-kizartmasi');
        $this->tarif('molehiya');
        $this->gsc([
            ['https://housechefcyprus.com/tarifler/hellim-kizartmasi', 2, 300, 7.2],
            ['https://housechefcyprus.com/tarifler/molehiya', 1, 120, 9.0],
            ['https://housechefcyprus.com/tarifler/olmayan-tarif', 0, 900, 6.0],
        ]);

        app(ClaudeConi::class)->calis();
        app(ClaudeConi::class)->calis();

        $odaklar = ConiAction::query()->where('kind', ConiAction::ODAK)->get();
        $this->assertCount(1, $odaklar);
        $this->assertSame('tr', $odaklar[0]->locale);
        $this->assertSame($tarif->id, $odaklar[0]->evidence['id']);
        $this->assertSame(300, $odaklar[0]->baseline['impressions']);
        $this->assertSame(now()->addDays(20)->toDateString(), $odaklar[0]->evaluate_after->toDateString());

        $rapor = IntegrityReport::query()->where('guard', ClaudeConi::RAPOR)->latest('id')->first();
        $this->assertSame('var', $rapor->payload['olcum']);
        $this->assertSame($odaklar[0]->page, $rapor->payload['diller']['tr']['odak']['page']);
    }

    public function test_olcum_yoksa_hicbir_sey_secilmez(): void
    {
        $this->tarif('hellim-kizartmasi');

        app(ClaudeConi::class)->calis();

        $this->assertSame(0, ConiAction::query()->count());
        $this->assertSame('yok', IntegrityReport::query()->where('guard', ClaudeConi::RAPOR)->latest('id')->first()->payload['olcum']);
    }

    public function test_noindex_tarif_odak_olmaz(): void
    {
        $tarif = $this->tarif('sablon-tarif');
        app(SeoMetadataService::class)->update($tarif, ['robots' => 'noindex,follow']);
        $this->gsc([['https://housechefcyprus.com/tarifler/sablon-tarif', 0, 500, 7.0]]);

        app(ClaudeConi::class)->calis();

        $this->assertSame(0, ConiAction::query()->where('kind', ConiAction::ODAK)->count());
    }

    public function test_suresi_dolan_odak_once_sonra_penceresiyle_degerlendirilir(): void
    {
        $tarif = $this->tarif('hellim-kizartmasi');
        $adres = 'https://housechefcyprus.com/tarifler/hellim-kizartmasi';
        $uygulama = now()->subDays(25)->startOfDay();
        $is = ConiAction::query()->create([
            'locale' => 'tr', 'page' => $adres, 'page_hash' => ConiAction::hash($adres), 'kind' => ConiAction::ODAK,
            'status' => ConiAction::UYGULANDI, 'reason' => 'test', 'evidence' => ['tur' => 'recipe', 'id' => $tarif->id, 'slug' => $tarif->slug],
            'applied_at' => $uygulama, 'evaluate_after' => $uygulama->copy()->addDays(20),
        ]);
        $onceBaslangic = $uygulama->copy()->subDays(14)->toDateString();

        $this->gsc(static fn (array $govde): array => match ($govde['startDate'] ?? '') {
            $onceBaslangic => [[$adres, 4, 200, 8.0]],
            default => [[$adres, 11, 260, 5.5]],
        });

        app(ClaudeConi::class)->calis();

        $is->refresh();
        $this->assertSame(ConiAction::KAZANC, $is->status);
        $this->assertSame(7, $is->result['fark']['clicks']);
        $this->assertSame(-2.5, $is->result['fark']['position']);
    }

    public function test_serp_degisikligi_eski_degeri_saklar_ve_geri_alinir(): void
    {
        $tarif = $this->tarif('hellim-kizartmasi');
        app(SeoMetadataService::class)->update($tarif, ['meta_title' => 'Eski Başlık Hellim Kızartması']);

        $this->artisan('coni:serp', [
            'adres' => 'https://housechefcyprus.com/tarifler/hellim-kizartmasi',
            '--baslik' => 'Hellim Kızartması: 10 Dakikada Çıtır Kıbrıs Kahvaltısı',
            '--neden' => 'Sıra 7, 300 gösterim, 2 tıklama; başlık sorguya cevap vermiyor.',
        ])->assertSuccessful();

        $is = ConiAction::query()->where('kind', ConiAction::SERP)->firstOrFail();
        $this->assertSame('Eski Başlık Hellim Kızartması', $is->before['title']);
        $this->assertSame('Hellim Kızartması: 10 Dakikada Çıtır Kıbrıs Kahvaltısı', $tarif->seoMetadata()->first()->meta_title);

        // Aynı sayfada ölçülmeyi bekleyen değişikliğin üstüne yazılmaz.
        $this->artisan('coni:serp', [
            'adres' => 'https://housechefcyprus.com/tarifler/hellim-kizartmasi',
            '--baslik' => 'Bir Başka Başlık Denemesi Hellim',
            '--neden' => 'ikinci',
        ])->assertFailed();

        $this->artisan('coni:geri-al', ['id' => $is->id])->assertSuccessful();

        $this->assertSame('Eski Başlık Hellim Kızartması', $tarif->seoMetadata()->first()->meta_title);
        $this->assertSame(ConiAction::GERI_ALINDI, $is->fresh()->status);
    }

    public function test_serp_nedeni_ve_uzunlugu_zorunlu(): void
    {
        $this->tarif('hellim-kizartmasi');

        $this->artisan('coni:serp', ['adres' => 'https://housechefcyprus.com/tarifler/hellim-kizartmasi', '--baslik' => 'Kısa', '--neden' => 'x'])->assertFailed();
        $this->artisan('coni:serp', ['adres' => 'https://housechefcyprus.com/tarifler/hellim-kizartmasi', '--baslik' => 'Yeterince uzun bir başlık burada', '--neden' => ''])->assertFailed();
        $this->assertSame(0, ConiAction::query()->count());
    }

    public function test_yabanci_dilde_arama_basligi_yalniz_arama_sonucunu_degistirir(): void
    {
        config()->set('locales.supported.en.enabled', true);
        $tarif = $this->tarif('hellim-kizartmasi');

        foreach (['title' => 'Fried Halloumi', 'seo_title' => 'Fried Halloumi: Crispy Cypriot Breakfast in 10 Minutes', 'seo_description' => 'Pan-fried halloumi the Cypriot way: which cheese to buy, how thick to slice it and why it squeaks. Ready in 10 minutes.'] as $alan => $deger) {
            ContentTranslation::query()->create(['translatable_type' => $tarif->getMorphClass(), 'translatable_id' => $tarif->id, 'locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        app()->setLocale('en');
        $meta = app(SeoMetadataService::class)->for($tarif->fresh(), 'x', 'y', '/tarifler/hellim-kizartmasi');

        $this->assertStringStartsWith('Fried Halloumi: Crispy Cypriot Breakfast in 10 Minutes', $meta['title']);
        $this->assertStringNotContainsString('Recipe', $meta['title']);
        $this->assertStringStartsWith('Pan-fried halloumi the Cypriot way', $meta['description']);
        // H1 ve gövde çeviriden gelmeye devam eder.
        $this->assertSame('Fried Halloumi', $tarif->fresh()->translated('title', 'en'));
    }

    public function test_odak_tarif_ayni_kategorideki_komsunun_ilgili_kutusunda_one_alinir(): void
    {
        $kategori = Category::query()->create(['name' => 'Kahvaltı', 'slug' => 'kahvalti']);
        $odak = $this->tarif('hellim-kizartmasi', ['category_id' => $kategori->id]);
        $komsu = $this->tarif('sucuklu-yumurta', ['category_id' => $kategori->id]);
        $yabanci = $this->tarif('mercimek-corbasi');
        $adres = 'https://housechefcyprus.com/tarifler/hellim-kizartmasi';
        ConiAction::query()->create([
            'locale' => 'tr', 'page' => $adres, 'page_hash' => ConiAction::hash($adres), 'kind' => ConiAction::ODAK,
            'status' => ConiAction::UYGULANDI, 'reason' => 'test', 'evidence' => ['tur' => 'recipe', 'id' => $odak->id, 'slug' => $odak->slug],
            'applied_at' => now(), 'evaluate_after' => now()->addDays(20),
        ]);
        $kume = app(ConiOdakKumesi::class);

        $komsununKutusu = $kume->tariflereEkle(collect(), $komsu, 'tr', 4);
        $yabancininKutusu = $kume->tariflereEkle(collect(), $yabanci, 'tr', 4);

        $this->assertSame([$odak->id], $komsununKutusu->pluck('id')->all());
        $this->assertSame([], $yabancininKutusu->pluck('id')->all());
    }

    public function test_sayfanin_sorgulari_sayfa_suzgeciyle_istenir(): void
    {
        $this->gsc([['fırın kaç dakikada ısınır', 3, 150, 7.9]]);

        $this->artisan('coni:sorgular', ['adres' => 'https://housechefcyprus.com/blog/firin', '--json' => true])
            ->expectsOutputToContain('fırın kaç dakikada ısınır')
            ->assertSuccessful();

        Http::assertSent(static fn (Request $istek): bool => str_contains($istek->url(), 'searchAnalytics')
            && ($istek->data()['dimensionFilterGroups'][0]['filters'][0]['expression'] ?? null) === 'https://housechefcyprus.com/blog/firin');
    }

    public function test_konu_kokleri_turkce_ekleri_ve_kopya_ekini_tanir(): void
    {
        $kume = app(ConiOdakKumesi::class);

        $this->assertTrue($kume->ilgili('firinda-kuzu-incik-1FJ1P', 'firin-kac-dakikada-isinir'));
        $this->assertFalse($kume->ilgili('kibris-usulu-molehiya', 'kibris-usulu-seftali-kebabi'));
        $this->assertSame(['firin', 'derec'], $kume->kokler('firin-dereceleri-rehberi-B253B'));
    }
}
