<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Seo\RecipeQualityGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeConsistencyGateTest extends TestCase
{
    use RefreshDatabase;

    /** @param list<string> $malzemeler @param list<string> $adimlar */
    private function tarif(string $baslik, array $malzemeler, array $adimlar): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik, 'slug' => str($baslik)->slug()->toString(), 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'status' => 'draft', 'verification_status' => 'pending', 'prep_minutes' => 10, 'cook_minutes' => 10, 'servings' => 2,
        ]);

        foreach ($malzemeler as $sira => $ad) {
            $malzeme = Ingredient::query()->firstOrCreate(['slug' => str($ad)->slug()->toString()], ['name' => $ad, 'canonical_name' => $ad, 'is_active' => true]);
            $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => '100', 'unit' => 'g', 'is_required' => true, 'is_optional' => false, 'sort_order' => $sira]);
        }

        foreach ($adimlar as $sira => $adim) {
            $tarif->steps()->create(['position' => $sira + 1, 'title' => 'Adım '.($sira + 1), 'instruction' => $adim]);
        }

        return $tarif;
    }

    public function test_a_title_ingredient_missing_from_the_list_is_flagged(): void
    {
        $kolokoti = $this->tarif('Kıbrıs Kolokoti', ['Un', 'Ceviz', 'Yumurta'], ['Unu ve yumurtayı yoğurun.', 'Cevizi ekleyin.', 'Unlu hamuru pişirin.']);

        $this->assertSame(['baslik_malzeme:kolokoti'], app(RecipeQualityGateService::class)->tutarlilikSorunlari($kolokoti));
        $this->assertTrue(app(RecipeQualityGateService::class)->eksikMi($kolokoti));
    }

    public function test_steps_that_never_mention_an_ingredient_are_generic(): void
    {
        $tarif = $this->tarif('Sucuklu Yumurta', ['Sucuk', 'Yumurta', 'Tereyağı'], [
            'Malzemeleri yıkayın ve hazırlayın.', 'Tavayı ocağa alıp ısıtın.', 'Belirtilen süre kadar pişirin.', 'Sıcak servis yapın.',
        ]);

        $this->assertContains('genel_adimlar', app(RecipeQualityGateService::class)->tutarlilikSorunlari($tarif));
    }

    public function test_turkish_capital_i_ingredients_match_the_title(): void
    {
        // mb_strtolower("İrmik") "i̇rmik" verir: irmikli Sütlü Revani "irmik yok" diye kapanıyordu.
        $revani = $this->tarif('Sütlü Revani', ['İrmik', 'Un', 'Süt'], ['İrmik ve unu karıştırın.', 'Sütü ekleyin.', 'Fırında pişirin.']);
        $ispanak = $this->tarif('Ispanaklı Börek', ['Un', 'Peynir', 'Yumurta'], ['Unu yoğurun.', 'Peyniri ezin.', 'Yumurtayla birleştirin.']);

        $this->assertSame([], app(RecipeQualityGateService::class)->tutarlilikSorunlari($revani));
        $this->assertSame(['baslik_malzeme:ıspanak'], app(RecipeQualityGateService::class)->tutarlilikSorunlari($ispanak));
    }

    public function test_a_consistent_recipe_passes(): void
    {
        $tarif = $this->tarif('Kıbrıs Kolokoti', ['Balkabağı', 'Bulgur', 'Un'], ['Balkabağını rendeleyin.', 'Bulguru ekleyip karıştırın.', 'Hamuru açıp doldurun.']);

        $this->assertSame([], app(RecipeQualityGateService::class)->tutarlilikSorunlari($tarif));
    }
}
