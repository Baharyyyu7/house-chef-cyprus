<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Seo\RecipeQualityGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Şablon ve eksik tarifler indekse girmez.
 *
 * 16 Eylül 2026'da 815 tarif tek günde eklendi; açıklamaları aynı kalıpla
 * üretilmişti ve aynı gün indeksleme kapısı gevşetildiği için indekse
 * girdiler. 637'sinin yayın tarihi boş olmasına rağmen site haritasındaydı.
 */
final class RecipeQualityGateTest extends TestCase
{
    use RefreshDatabase;

    private const SABLON = 'Bu tarif ev mutfagindan ilham alan anlasilir bir yapiya sahiptir ve malzemeleri her mevsim kolayca bulunur.';

    private function tarif(string $slug, string $aciklama, int $adim = 3, int $malzeme = 4, ?string $yayin = '-1 day'): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet '.$slug,
            'description' => $aciklama,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => $yayin === null ? null : now()->modify($yayin),
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
        ]);

        for ($i = 1; $i <= $adim; $i++) {
            $tarif->steps()->create(['position' => $i, 'title' => 'Adım '.$i, 'instruction' => 'Adım '.$i.' açıklaması.']);
        }

        for ($i = 1; $i <= $malzeme; $i++) {
            $malzemeKaydi = Ingredient::query()->firstOrCreate(
                ['slug' => 'malzeme-'.$i],
                ['name' => 'Malzeme '.$i],
            );
            $tarif->recipeIngredients()->create([
                'ingredient_id' => $malzemeKaydi->id,
                'quantity' => 1,
                'unit' => 'adet',
                'sort_order' => $i,
            ]);
        }

        return $tarif;
    }

    private function robots(Recipe $tarif): string
    {
        return (string) $tarif->fresh('seoMetadata')->seoMetadata?->robots;
    }

    public function test_sablon_tarif_indeks_disina_alinir_ilki_kalir(): void
    {
        $ilk = $this->tarif('ilk-tarif', self::SABLON);
        $ilk->forceFill(['published_at' => now()->subDays(10)])->save();
        $kopya = $this->tarif('kopya-tarif', self::SABLON);

        $this->artisan('seo:recipe-quality-scan --apply')->assertSuccessful();

        $this->assertStringContainsString('noindex', $this->robots($kopya));
        $this->assertStringNotContainsString('noindex', $this->robots($ilk));
        $this->assertSame('published', $kopya->fresh()->status, 'Tarif silinmemeli ya da yayından kaldırılmamalı.');
    }

    public function test_adimi_olmayan_tarif_indeks_disina_alinir(): void
    {
        $eksik = $this->tarif('adimsiz-tarif', 'Kendine ait özgün bir açıklama cümlesi burada yer alır ve başka tarifte geçmez.', adim: 0);

        $this->artisan('seo:recipe-quality-scan --apply')->assertSuccessful();

        $this->assertStringContainsString('noindex', $this->robots($eksik));
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $eksik = $this->tarif('rapor-tarifi', 'Rapor modunda ölçülen özgün bir açıklama cümlesi burada duruyor efendim.', adim: 0);

        $this->artisan('seo:recipe-quality-scan')->assertSuccessful();

        $this->assertSame('', $this->robots($eksik));
    }

    public function test_yayin_tarihi_olmayan_tarif_site_haritasinda_cikmaz(): void
    {
        $this->tarif('yayin-tarihi-yok', 'Yayın tarihi girilmemiş bir tarif site haritasına düşmemelidir kesinlikle.', yayin: null);
        $canli = $this->tarif('yayindaki-tarif', 'Yayın tarihi girilmiş bir tarif site haritasında yer almalıdır elbette.');

        $this->get('/sitemaps/recipes.xml')
            ->assertOk()
            ->assertSee(route('recipes.show', $canli), false)
            ->assertDontSee('yayin-tarihi-yok', false);
    }

    public function test_genel_adim_yonergeleri_sablon_sayilir(): void
    {
        // 111 tarifin adımları birebir aynıydı; açıklamada yemeğin adı geçtiği
        // için açıklama tek başına bunu ayırt edemiyordu.
        $genel = ['Tüm malzemeleri tarif için hazırlayın ve iyice temizleyin bu adımda.', 'Ocağı orta ateşte ısıtın ve malzemeleri sırayla tencereye alın.', 'Pişirme tamamlanınca ocaktan alıp servis tabağına aktarın ve sıcak sunun.'];

        $ilk = $this->tarif('ilk-genel-tarif', 'Bu tarifin kendine ait bir açıklaması vardır ve başka yazıda geçmez.', adim: 0);
        $ikinci = $this->tarif('ikinci-genel-tarif', 'Bu tarifin de kendine ait ayrı bir açıklaması vardır efendim.', adim: 0);

        foreach ([$ilk, $ikinci] as $tarif) {
            foreach ($genel as $i => $yonerge) {
                $tarif->steps()->create(['position' => $i + 1, 'title' => 'Adım '.($i + 1), 'instruction' => $yonerge]);
            }
        }

        $this->artisan('seo:recipe-quality-scan --apply')->assertSuccessful();

        $this->assertStringContainsString('noindex', $this->robots($ikinci));
        $this->assertStringNotContainsString('noindex', $this->robots($ilk));
    }

    public function test_ana_malzeme_yer_tutucusu_tasiyan_tarif_indeks_disina_alinir(): void
    {
        $tarif = $this->tarif('yer-tutuculu-tarif', 'Kendine ait özgün bir açıklama cümlesi burada duruyor ve tekrar etmez.');
        $yerTutucu = Ingredient::query()->create(['slug' => 'ana-malzeme', 'name' => 'Ana Malzeme']);
        $tarif->recipeIngredients()->create(['ingredient_id' => $yerTutucu->id, 'quantity' => 1, 'unit' => 'adet', 'sort_order' => 99]);

        $this->artisan('seo:recipe-quality-scan --apply')->assertSuccessful();

        $this->assertStringContainsString('noindex', $this->robots($tarif));
    }

    public function test_hicbir_malzemesinde_olcu_olmayan_tarif_eksik_sayilir(): void
    {
        $tarif = $this->tarif('olcusuz-tarif', 'Bu tarifin açıklaması yeterince uzun ve kendine özgüdür, başka tarifte geçmez.');
        $tarif->recipeIngredients()->update(['quantity' => null, 'unit' => null, 'preparation' => null]);

        $this->assertTrue(app(RecipeQualityGateService::class)->eksikMi($tarif->fresh()));
    }

    public function test_tek_malzemesinde_olcu_olan_tarif_gecer(): void
    {
        $tarif = $this->tarif('olculu-tarif', 'Bu tarifin açıklaması yeterince uzun ve kendine özgüdür, başka tarifte geçmez.');
        $tarif->recipeIngredients()->update(['quantity' => null, 'unit' => null, 'preparation' => null]);
        $tarif->recipeIngredients()->first()->forceFill(['quantity' => '2', 'unit' => 'adet'])->save();

        $this->assertFalse(app(RecipeQualityGateService::class)->eksikMi($tarif->fresh()));
    }

    public function test_ayni_malzeme_kumesini_ucten_fazla_tarif_paylasirsa_sablon_sayilir(): void
    {
        $ortak = ['un', 'seker', 'tereyagi'];

        foreach (['sablon-a', 'sablon-b', 'sablon-c'] as $slug) {
            $tarif = $this->tarif($slug, 'Bu tarifin açıklaması kendine özgüdür ve '.$slug.' için ayrıca yazılmıştır.', 3, 0);

            foreach ($ortak as $sira => $ad) {
                $malzeme = Ingredient::query()->firstOrCreate(['slug' => $ad], ['name' => $ad]);
                $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => '1', 'unit' => 'su bardağı', 'sort_order' => $sira + 1]);
            }
        }

        $kapi = app(RecipeQualityGateService::class);
        $sonuncu = Recipe::query()->where('slug', 'sablon-c')->with('ingredients')->first();

        $this->assertTrue($kapi->ortakMalzemeKumesi($sonuncu));
        $this->assertTrue($kapi->eksikMi($sonuncu));
    }

    public function test_sebze_karisimi_yer_tutucusu_eksik_sayilir(): void
    {
        $tarif = $this->tarif('yer-tutuculu', 'Bu tarifin açıklaması kendine özgüdür ve ayrıca yazılmıştır.');
        $malzeme = Ingredient::query()->firstOrCreate(['slug' => 'sebze-karisimi'], ['name' => 'Sebze Karışımı']);
        $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => '1', 'unit' => 'porsiyon', 'sort_order' => 9]);

        $this->assertTrue(app(RecipeQualityGateService::class)->eksikMi($tarif->fresh()));
    }
}
