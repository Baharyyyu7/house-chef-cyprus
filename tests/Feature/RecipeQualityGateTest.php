<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
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
}
