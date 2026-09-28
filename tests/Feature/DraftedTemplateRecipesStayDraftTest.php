<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Seo\AutonomousPublishingGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Taslağa alınan şablon tarif otomatik yayın yolundan geri dönmez.
 *
 * 27 Eylül 2026'da AdSense siteyi "düşük değerli içerik" diye reddetti.
 * Yayındaki 534 tarifin 380'i ölçüsüz, yer tutuculu ya da şablon malzeme
 * kümeliydi ve taslağa alındı. Otomatik yayın yolu yalnız malzeme, adım ve
 * açıklama sayıyordu; bu tariflerin hepsi o sayımdan geçiyordu.
 */
final class DraftedTemplateRecipesStayDraftTest extends TestCase
{
    use RefreshDatabase;

    private function taslak(string $baslik, ?string $miktar): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik,
            'slug' => str($baslik)->slug()->toString(),
            'excerpt' => 'Özet.',
            'description' => str_repeat('Kıbrıs mutfağından, ölçüleri ve püf noktalarıyla anlatılan bir tarif. ', 3),
            'status' => 'draft',
            'verification_status' => 'pending',
            'prep_minutes' => 20,
            'cook_minutes' => 10,
            'servings' => 4,
        ]);

        foreach (['Un', 'Süt', 'Şeker', 'Yumurta'] as $sira => $ad) {
            $malzeme = Ingredient::query()->firstOrCreate(['slug' => str($ad)->slug()->toString()], ['name' => $ad, 'canonical_name' => $ad, 'is_active' => true]);
            $tarif->recipeIngredients()->create([
                'ingredient_id' => $malzeme->id,
                'quantity' => $miktar,
                'unit' => $miktar === null ? null : 'g',
                'is_required' => true,
                'is_optional' => false,
                'sort_order' => $sira,
            ]);
        }

        foreach (['Unu eleyin.', 'Sütü ılıtın.', 'Hamuru yoğurun.'] as $sira => $adim) {
            $tarif->steps()->create(['position' => $sira + 1, 'title' => 'Adım '.($sira + 1), 'instruction' => $adim]);
        }

        return $tarif;
    }

    public function test_a_recipe_without_a_single_measurement_is_held_back(): void
    {
        $olcusuz = $this->taslak('Ölçüsüz Tatlı', null);

        $sonuc = app(AutonomousPublishingGateService::class)->run(blogLimit: 0, recipeLimit: 5);

        $this->assertSame('draft', $olcusuz->fresh()->status);
        $this->assertNotContains('olcusuz-tatli', $sonuc['published_recipes']);
    }

    public function test_a_measured_recipe_still_passes(): void
    {
        $olculu = $this->taslak('Ölçülü Tatlı', '200');

        app(AutonomousPublishingGateService::class)->run(blogLimit: 0, recipeLimit: 5);

        $this->assertSame('published', $olculu->fresh()->status);
    }
}
