<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\AllergenInferenceService;
use App\Services\DietaryCompatibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 2026-10 denetimi: 77/129 tarifte alerjen satırı yoktu, flaouna süt, yumurta
 * ve susama rağmen uyarısızdı; kuzu kol "6 kcal, ✓ Düşük kalorili" diyordu.
 */
final class RecipeAllergenAndNutritionHonestyTest extends TestCase
{
    use RefreshDatabase;

    public function test_malzeme_adindan_ab_alerjenleri_kelime_sinirinda_turetilir(): void
    {
        $servis = app(AllergenInferenceService::class);

        $this->assertSame(['gluten'], $servis->kodlar(['Un']));
        $this->assertSame(['gluten'], $servis->kodlar(['Yufka', 'İrmik', 'Bulgur', 'Bayat ekmek']));
        $this->assertSame(['egg'], $servis->kodlar(['Yumurta sarısı']));
        $this->assertSame(['milk'], $servis->kodlar(['Hellim', 'Süzme yoğurt', 'Tereyağı', 'Krema', 'Beyaz peynir']));
        $this->assertSame(['sesame'], $servis->kodlar(['Tahin']));
        $this->assertSame(['tree_nuts'], $servis->kodlar(['Ceviz içi', 'Antep fıstığı']));
        $this->assertSame(['peanut'], $servis->kodlar(['Yer fıstığı']));
        $this->assertSame(['molluscs'], $servis->kodlar(['Mürekkep balığı']));
        $this->assertSame(['egg', 'milk'], $servis->kodlar(['Eggs', 'Butter']));

        // Yanlış pozitifler: kelime içi, hindistan cevizi, mısır unu, bitkisel süt.
        $this->assertSame([], $servis->kodlar(['Soğan', 'Hindistan cevizi', 'Muskat cevizi', 'Arpacık soğan', 'Kuru fasulye']));
        $this->assertSame([], $servis->kodlar(['Mısır unu', 'Hindistan cevizi sütü', 'Pirinç unu']));
        $this->assertSame(['fish'], $servis->kodlar(['Uskumru']));
    }

    public function test_flaouna_tablo_eslemesi_olmadan_da_icerir_listesini_ve_uyariyi_basar(): void
    {
        $tarif = $this->tarif('Flaouna', ['Un', 'Yumurta', 'Hellim', 'Süt', 'Susam']);

        $sayfa = $this->get(route('recipes.show', $tarif))->assertOk();

        $sayfa->assertSee('data-allergens-contains', false)
            ->assertSee('Gluten, Yumurta, Süt, Susam')
            ->assertSee('data-allergens-unverified', false)
            ->assertSee('Alerjen bilgisi tam doğrulanmadı')
            ->assertDontSee('✓ Kayıt doğrulandı');
    }

    public function test_sucuklu_yumurta_tablo_yalniz_sut_dese_de_yumurtayi_yazar(): void
    {
        $tarif = $this->tarif('Sucuklu yumurta', ['Yumurta', 'Sucuk', 'Tereyağı']);
        $sut = Allergen::create(['public_id' => (string) Str::ulid(), 'code' => 'milk', 'name' => 'Süt', 'is_active' => true]);
        Ingredient::query()->where('name', 'Tereyağı')->firstOrFail()->allergenDetails()->create(['allergen_id' => $sut->id, 'relation_type' => 'contains', 'confidence_score' => 90, 'source_type' => 'editorial', 'is_verified' => true]);

        $ozet = app(AllergenInferenceService::class)->ozet($tarif->fresh(), app(AllergenAnalysisService::class)->analyse($tarif->fresh()));

        $this->assertSame(['egg', 'milk'], array_column($ozet['contains'], 'code'));
        $this->assertSame(['egg'], $ozet['inferred_only']);
        $this->assertFalse($ozet['verified']);
    }

    public function test_tum_cikarim_dogrulanmis_kayitla_karsilaninca_uyari_kalkar(): void
    {
        $tarif = $this->tarif('Yoğurtlu meze', ['Yoğurt', 'Salatalık']);
        $sut = Allergen::create(['public_id' => (string) Str::ulid(), 'code' => 'milk', 'name' => 'Süt', 'is_active' => true]);
        Ingredient::query()->where('name', 'Yoğurt')->firstOrFail()->allergenDetails()->create(['allergen_id' => $sut->id, 'relation_type' => 'contains', 'confidence_score' => 90, 'source_type' => 'editorial', 'is_verified' => true]);

        $ozet = app(AllergenInferenceService::class)->ozet($tarif->fresh(), app(AllergenAnalysisService::class)->analyse($tarif->fresh()));

        $this->assertTrue($ozet['verified']);
        $this->get(route('recipes.show', $tarif))->assertOk()->assertDontSee('data-allergens-unverified', false);
    }

    public function test_dusuk_besin_kapsaminda_diyet_etiketi_belirsiz_ve_deger_basilmaz(): void
    {
        // Kuzu kol: tek ölçülü satır tuz; hesap tuzu yüzde yüz kapsıyordu.
        $tarif = $this->tarif('Kuzu kol', ['Tuz', 'Kuzu kol', 'Soğan', 'Domates'], ['Tuz' => ['g', '5']]);
        $enerji = Nutrient::create(['public_id' => (string) Str::ulid(), 'code' => 'energy_kcal', 'name' => 'Enerji', 'unit' => 'kcal', 'category' => 'energy', 'is_active' => true]);
        Ingredient::query()->where('name', 'Tuz')->firstOrFail()->nutritionFacts()->create(['nutrient_id' => $enerji->id, 'value' => '0.5', 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);

        $diyet = app(DietaryCompatibilityService::class)->assess($tarif->fresh());
        $this->assertSame('uncertain', $diyet['low_calorie']['status']);

        $this->get(route('recipes.show', $tarif))->assertOk()
            ->assertSee('data-nutrition-insufficient', false)
            ->assertSee('Eksik veri.')
            ->assertSee('%25 tamam')
            ->assertDontSee('Düşük kalorili: Uygun');
    }

    public function test_yeterli_besin_verisi_tam_sayi_kcal_ve_virgullu_makro_basar(): void
    {
        $tarif = $this->tarif('Pilav', ['Pirinç'], ['Pirinç' => ['g', '300']]);
        $enerji = Nutrient::create(['public_id' => (string) Str::ulid(), 'code' => 'energy_kcal', 'name' => 'Enerji', 'unit' => 'kcal', 'category' => 'energy', 'is_active' => true]);
        $protein = Nutrient::create(['public_id' => (string) Str::ulid(), 'code' => 'protein', 'name' => 'Protein', 'unit' => 'g', 'category' => 'macro', 'is_active' => true]);
        $pirinc = Ingredient::query()->where('name', 'Pirinç')->firstOrFail();
        $pirinc->nutritionFacts()->create(['nutrient_id' => $enerji->id, 'value' => '130.4', 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);
        $pirinc->nutritionFacts()->create(['nutrient_id' => $protein->id, 'value' => '2.69', 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);

        // 300 g / 2 porsiyon = 150 g: 195,6 kcal -> 196; 4,035 g -> 4,0.
        $this->get(route('recipes.show', $tarif))->assertOk()
            ->assertSee('>196<', false)
            ->assertSee('>4,0<', false)
            ->assertDontSee('data-nutrition-insufficient', false);
    }

    public function test_dil_dosyalari_her_tarifte_besin_degeri_vaat_etmez(): void
    {
        $this->assertStringNotContainsString('besin değerleri görünür', __('cuisine.body_detail', [], 'tr'));
        $this->assertStringNotContainsString('besin değerleri', __('archive.meta_desc_recipes', [], 'tr'));
    }

    /**
     * @param  list<string>  $malzemeler
     * @param  array<string, array{0: string, 1: string}>  $olculer
     */
    private function tarif(string $baslik, array $malzemeler, array $olculer = []): Recipe
    {
        $tarif = Recipe::create(['title' => $baslik, 'slug' => Str::slug($baslik).'-'.Str::lower(Str::random(5)), 'excerpt' => 'Özet.', 'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 5, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay()]);
        foreach ($malzemeler as $sira => $ad) {
            $malzeme = Ingredient::create(['name' => $ad, 'canonical_name' => $ad, 'slug' => Str::slug($ad).'-'.Str::lower(Str::random(6)), 'is_active' => true]);
            [$birim, $miktar] = $olculer[$ad] ?? ['adet', '1'];
            $tarif->ingredients()->attach($malzeme->id, ['quantity' => $miktar, 'unit' => $birim, 'grams' => null, 'sort_order' => $sira + 1]);
        }

        return $tarif;
    }
}
