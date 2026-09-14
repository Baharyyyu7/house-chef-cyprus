<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Seo\BacklinkMarketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Backlink adayı hangi pazara ait olduğunu bilir.
 *
 * Aday listesi tek yığındı: Kıbrıs'taki bir üniversite ile Almanya'daki bir
 * yemek blogu aynı havuzdaydı ve ikisine de aynı Türkçe sayfa öneriliyordu.
 * Alman bir editöre Türkçe tarif göndermek bağlantı kazandırmaz.
 */
final class BacklinkProspectsKnowTheirMarketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
        config()->set('locales.supported.de.enabled', true);
    }

    private function tarif(string $slug, array $diller): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellim Izgara', 'slug' => $slug, 'excerpt' => 'Ö.', 'description' => 'A.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);

        foreach ($diller as $dil) {
            foreach (['title' => 'T-'.$dil, 'excerpt' => 'S-'.$dil, 'description' => 'D-'.$dil] as $alan => $deger) {
                $tarif->translations()->create(['locale' => $dil, 'field' => $alan, 'value' => $deger]);
            }
        }

        return $tarif;
    }

    public function test_uzantidan_pazar_turetilir(): void
    {
        $servis = app(BacklinkMarketService::class);

        $this->assertSame('tr', $servis->pazar('gastronomy.emu.edu.tr'));
        $this->assertSame('de', $servis->pazar('essen-und-trinken.de'));
        $this->assertSame('de', $servis->pazar('kochen.at'));
        // Tanımadığımız uzantı İngilizceye düşer: uluslararası ortak dil o.
        $this->assertSame('en', $servis->pazar('seriouseats.com'));
    }

    public function test_kapali_pazar_varsayilana_duser(): void
    {
        // Kapalı bir dile aday yönlendirmek, olmayan bir siteye bağlantı
        // istemek olurdu.
        config()->set('locales.supported.de.enabled', false);

        $this->assertSame('tr', app(BacklinkMarketService::class)->pazar('essen-und-trinken.de'));
    }

    public function test_pazarin_dilinde_sayfa_varsa_o_adres_verilir(): void
    {
        $this->tarif('hellim-izgara', ['de']);

        $adres = app(BacklinkMarketService::class)->varlikAdresi('essen-und-trinken.de', 'recipe', 'hellim-izgara');

        $this->assertSame(url('/de/tarifler/hellim-izgara'), $adres['url']);
        $this->assertSame('de', $adres['locale']);
    }

    public function test_kismi_slug_ile_varlik_adresi_ve_gercek_slug_cozulur(): void
    {
        $this->tarif('hellimli-kibris-boregi', ['de']);

        $adres = app(BacklinkMarketService::class)->varlikAdresi('essen-und-trinken.de', 'recipe', 'hellimli');

        $this->assertNotNull($adres);
        $this->assertSame(url('/de/tarifler/hellimli-kibris-boregi'), $adres['url']);
        $this->assertSame('de', $adres['locale']);
    }

    public function test_pazarin_dilinde_sayfa_yoksa_turkceye_dusulmez(): void
    {
        // Sessizce Türkçeye düşmek "Almanca 40 aday var" diye rapor verirken
        // hepsine Türkçe sayfa göndermek olurdu: sayı doğru, iş yanlış.
        $this->tarif('cevirisiz-tarif', []);

        $this->assertNull(
            app(BacklinkMarketService::class)->varlikAdresi('essen-und-trinken.de', 'recipe', 'cevirisiz-tarif')
        );
    }

    public function test_dagilim_hazir_olani_toplamdan_ayirir(): void
    {
        $this->tarif('almancasi-var', ['de']);
        $this->tarif('almancasi-yok', []);

        $dagilim = app(BacklinkMarketService::class)->pazarDagilimi([
            ['domain' => 'a.de', 'asset_type' => 'recipe', 'asset_keyword' => 'almancasi-var'],
            ['domain' => 'b.de', 'asset_type' => 'recipe', 'asset_keyword' => 'almancasi-yok'],
            ['domain' => 'c.edu.tr', 'asset_type' => 'recipe', 'asset_keyword' => 'almancasi-yok'],
        ]);

        $this->assertSame(['total' => 2, 'ready' => 1], $dagilim['de']);
        // Türkçe kaynak dildir; çeviri satırı istemez, her zaman hazırdır.
        $this->assertSame(['total' => 1, 'ready' => 1], $dagilim['tr']);
    }

    public function test_drafts_and_unverified_recipes_are_not_ready_assets(): void
    {
        $draft = $this->tarif('taslak', ['de']);
        $draft->update(['status' => 'draft']);
        $unverified = $this->tarif('incelemede', ['de']);
        $unverified->update(['verification_status' => 'pending']);
        $service = app(BacklinkMarketService::class);

        $this->assertNull($service->varlikAdresi('publisher.de', 'recipe', 'taslak'));
        $this->assertNull($service->varlikAdresi('publisher.de', 'recipe', 'incelemede'));
    }

    public function test_future_blog_is_not_ready_even_when_translated(): void
    {
        $post = BlogPost::query()->create([
            'title' => 'Gelecek yazi', 'slug' => 'future-guide', 'excerpt' => 'Ozet', 'body' => 'Govde',
            'status' => 'published', 'published_at' => now()->addDay(),
        ]);
        foreach (['title' => 'Guide', 'excerpt' => 'Summary', 'body' => 'Content'] as $field => $value) {
            $post->translations()->create(['locale' => 'de', 'field' => $field, 'value' => $value]);
        }

        $this->assertNull(app(BacklinkMarketService::class)->varlikAdresi('publisher.de', 'blog', 'future-guide'));
    }

    public function test_partial_match_skips_an_ineligible_exact_match(): void
    {
        $this->tarif('hellim', []);
        $this->tarif('hellim-izgara', ['de']);

        $asset = app(BacklinkMarketService::class)->varlikAdresi('publisher.de', 'recipe', 'hellim');

        $this->assertNotNull($asset);
        $this->assertSame(url('/de/tarifler/hellim-izgara'), $asset['url']);
    }

    public function test_disabled_explicit_language_does_not_yield_a_source_language_asset(): void
    {
        config()->set('locales.supported.en.enabled', false);
        $this->tarif('hellim', []);

        $this->assertNull(app(BacklinkMarketService::class)->varlikAdresi('amalia.com.cy', 'recipe', 'hellim', 'en'));
    }
}
