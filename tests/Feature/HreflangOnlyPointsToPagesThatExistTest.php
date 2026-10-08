<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Seo\LocalePageAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * hreflang, site haritası ve controller "bu dilde sayfa var mı?" sorusunu
 * aynı yerden cevaplar; yabancı hub sayfasında Türkçe kalıp kalmaz.
 *
 * 8 Ekim 2026 denetimi: en az 139 hreflang hedefi 404 veriyordu (88'i
 * malzeme hub'ı); 117 yabancı hub "tomatoes ile Ne Yapılır?", "Ana Yemek
 * Rezepte" gibi karışık dilli başlıkla indekse açıktı; /en/cuisines
 * sayfasının H1'i "Mutfaklar"dı.
 */
final class HreflangOnlyPointsToPagesThatExistTest extends TestCase
{
    use RefreshDatabase;

    private function hellimTarifleri(array $diller = []): void
    {
        $hellim = Ingredient::query()->create(['name' => 'Hellim', 'slug' => 'hellim', 'is_active' => true]);
        foreach ($diller as $dil) {
            $hellim->translations()->create(['locale' => $dil, 'field' => 'name', 'value' => 'Halloumi']);
        }

        foreach (range(1, 5) as $i) {
            $tarif = $this->tarif('hellimli-'.$i, $diller);
            $tarif->recipeIngredients()->create(['ingredient_id' => $hellim->id, 'quantity' => '200', 'unit' => 'g', 'sort_order' => 1]);
        }
    }

    /** @param list<string> $diller */
    private function tarif(string $slug, array $diller = [], array $ek = []): Recipe
    {
        $tarif = Recipe::query()->create(array_merge([
            'title' => 'Tarif '.$slug, 'slug' => $slug, 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
            'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 10, 'difficulty' => 'easy',
        ], $ek));

        foreach ($diller as $dil) {
            foreach (['title' => 'Recipe '.$slug, 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
                $tarif->translations()->create(['locale' => $dil, 'field' => $alan, 'value' => $deger]);
            }
        }

        return $tarif;
    }

    /** @return list<string> */
    private function hreflangHedefleri(string $html): array
    {
        preg_match_all('#<link rel="alternate" hreflang="[^"]+" href="([^"]+)">#', $html, $eslesme);

        return $eslesme[1];
    }

    public function test_yabanci_dilde_esigi_tutmayan_hub_icin_hreflang_ve_harita_alternatifi_basilmaz(): void
    {
        $this->hellimTarifleri();
        $varlik = app(LocalePageAvailability::class);

        $this->assertTrue($varlik->varMi('tr', '/malzemeler/hellim'));
        $this->assertFalse($varlik->varMi('en', '/malzemeler/hellim'));
        $this->get('/en/malzemeler/hellim')->assertNotFound();

        $sayfa = (string) $this->get('/malzemeler/hellim')->assertOk()->getContent();
        $this->assertSame([], $this->hreflangHedefleri($sayfa));
        $this->assertStringNotContainsString('og:locale:alternate', $sayfa);

        $harita = (string) $this->get(route('sitemap.pages'))->assertOk()->getContent();
        $this->assertStringNotContainsString('/en/malzemeler/hellim', $harita);
    }

    public function test_hub_ceviri_varken_hreflang_iki_dili_baglar_ve_hedef_acilir(): void
    {
        $this->hellimTarifleri(['en']);
        $varlik = app(LocalePageAvailability::class);

        $this->assertTrue($varlik->varMi('en', '/malzemeler/hellim'));
        $this->assertTrue($varlik->varMi('en', '/en/malzemeler/hellim'));
        $this->assertFalse($varlik->varMi('de', '/malzemeler/hellim'));

        $hedefler = $this->hreflangHedefleri((string) $this->get('/malzemeler/hellim')->getContent());
        $this->assertContains(url('/en/malzemeler/hellim'), $hedefler);
        $this->assertNotContains(url('/de/malzemeler/hellim'), $hedefler);

        $ingilizce = (string) $this->get('/en/malzemeler/hellim')->assertOk()->getContent();
        $this->assertStringContainsString('What to make with Halloumi', $ingilizce);
        $this->assertStringNotContainsString('ile ne yapılır', mb_strtolower($ingilizce));
        $this->assertStringNotContainsString('doğrulanmış tarif', $ingilizce);
        $this->assertStringNotContainsString('Ana Sayfa', $ingilizce);
    }

    public function test_site_haritasindaki_her_alternatif_hedef_acilir(): void
    {
        $this->hellimTarifleri(['en']);

        $harita = (string) $this->get(route('sitemap.pages'))->assertOk()->getContent();
        preg_match_all('#<xhtml:link[^>]+href="([^"]+)"#', $harita, $eslesme);

        $this->assertNotEmpty($eslesme[1]);
        foreach (array_unique($eslesme[1]) as $hedef) {
            $this->assertSame(200, $this->get($hedef)->getStatusCode(), $hedef.' site haritasında alternatif ama açılmıyor');
        }
    }

    public function test_cevirisi_olmayan_statik_sayfa_hreflang_basmaz(): void
    {
        $varlik = app(LocalePageAvailability::class);

        $this->assertFalse($varlik->varMi('en', '/hakkimizda'));
        $this->assertFalse($varlik->varMi('de', '/sss'));
        $this->assertTrue($varlik->varMi('en', '/tarifler'));

        $sayfa = (string) $this->get('/hakkimizda')->assertOk()->getContent();
        $this->assertSame([], $this->hreflangHedefleri($sayfa));
        $this->assertStringNotContainsString('og:locale:alternate', $sayfa);
    }

    public function test_yabanci_mutfak_listesi_kendi_dilinde_ve_yalniz_acilan_mutfaklari_listeler(): void
    {
        $akdeniz = Cuisine::query()->create(['name' => 'Akdeniz Mutfağı', 'slug' => 'akdeniz-mutfagi']);
        $turk = Cuisine::query()->create(['name' => 'Türk Mutfağı', 'slug' => 'turk-mutfagi']);
        foreach (range(1, 4) as $i) {
            $this->tarif('akdeniz-'.$i, ['en'], ['cuisine_id' => $akdeniz->id]);
            $this->tarif('turk-'.$i, [], ['cuisine_id' => $turk->id]);
        }

        $sayfa = (string) $this->get('/en/cuisines')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1[^>]*>\s*Cuisines\s*</h1>#', $sayfa);
        $this->assertStringContainsString('Mediterranean Cuisine', $sayfa);
        $this->assertStringNotContainsString('Akdeniz Mutfağı', $sayfa);
        $this->assertStringNotContainsString('doğrulanmış', $sayfa);
        // Türk mutfağı İngilizcede eşiği tutmuyor: kartı 404'e gitmemeli.
        $this->assertStringNotContainsString('/en/cuisines/turk-mutfagi', $sayfa);
        $this->get('/en/cuisines/turk-mutfagi')->assertNotFound();
    }

    public function test_adi_cevrilmemis_kategori_o_dilde_404_verir(): void
    {
        $anaYemek = Category::query()->create(['name' => 'Ana Yemek', 'slug' => 'ana-yemek']);
        $ozel = Category::query()->create(['name' => 'Özel Seçki', 'slug' => 'ozel-secki']);
        foreach (range(1, 4) as $i) {
            $this->tarif('ana-'.$i, ['de'], ['category_id' => $anaYemek->id]);
            $this->tarif('ozel-'.$i, ['de'], ['category_id' => $ozel->id]);
        }

        $sayfa = (string) $this->get('/de/kategoriler/ana-yemek')->assertOk()->getContent();
        $this->assertStringNotContainsString('Ana Yemek', $sayfa);

        // Sözlükte karşılığı olmayan ad Türkçe kalırdı: sayfa o dilde yok.
        $this->get('/de/kategoriler/ozel-secki')->assertNotFound();
        $this->assertFalse(app(LocalePageAvailability::class)->varMi('de', '/kategoriler/ozel-secki'));
        $this->assertStringNotContainsString('ozel-secki', (string) $this->get('/de/kategoriler')->getContent());
    }

    public function test_sure_ve_zorluk_sayfalari_yabanci_dilde_turkce_baslik_basmaz(): void
    {
        foreach (range(1, 4) as $i) {
            $this->tarif('kolay-'.$i, ['en']);
        }

        $sayfa = (string) $this->get('/en/zorluk/kolay')->assertOk()->getContent();
        $this->assertStringContainsString('Easy Recipes', $sayfa);
        $this->assertStringNotContainsString('Kolay Tarifler', $sayfa);
        $this->assertStringNotContainsString('doğrulanmış', $sayfa);

        $ust = (string) $this->get('/en/zorluk')->assertOk()->getContent();
        $this->assertStringContainsString('Recipes by difficulty', $ust);
        $this->assertStringNotContainsString('Zorluğa göre', $ust);
    }
}
