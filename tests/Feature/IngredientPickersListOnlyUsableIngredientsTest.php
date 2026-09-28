<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Malzeme seçicisi yalnız o dilde işe yarayan malzemeleri, o dilin adıyla
 * listeler.
 *
 * 28 Eylül 2026'da canlıda tarandı: İngilizce ve Almanca ana sayfanın
 * seçicisi bütün aktif malzemeleri ham Türkçe adla basıyordu; taslaktaki
 * şablon tariflerin "Ana Malzeme" ve "Sebze Karışımı" yer tutucuları dahil.
 */
final class IngredientPickersListOnlyUsableIngredientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_placeholders_and_draft_only_ingredients_are_not_offered(): void
    {
        $domates = Ingredient::query()->create(['name' => 'Domates', 'slug' => 'domates', 'is_active' => true]);
        $yerTutucu = Ingredient::query()->create(['name' => 'Ana Malzeme', 'slug' => 'ana-malzeme', 'is_active' => true]);

        foreach ([['Yayında', 'published', $domates], ['Taslak', 'draft', $yerTutucu]] as $i => [$baslik, $durum, $malzeme]) {
            $tarif = Recipe::query()->create(['title' => $baslik, 'slug' => 'tarif-'.$i, 'excerpt' => 'Özet.', 'description' => 'Açıklama.', 'status' => $durum, 'verification_status' => 'verified', 'published_at' => now()->subDay(), 'prep_minutes' => 5, 'cook_minutes' => 5, 'servings' => 2]);
            $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => 1, 'unit' => 'adet', 'is_required' => true, 'is_optional' => false, 'sort_order' => 0]);
        }

        $this->assertSame(['Domates'], Ingredient::query()->yayindaKullanilan()->pluck('name')->all());
        $this->get(route('ingredients.explore'))->assertOk()->assertSee('Domates')->assertDontSee('Ana Malzeme');
    }

    public function test_a_foreign_picker_shows_the_translated_name_in_that_alphabet_order(): void
    {
        config()->set('locales.supported.en.enabled', true);
        app()->setLocale('en');
        $malzemeler = collect(['Zeytin' => 'olives', 'Ayva' => 'quince', 'Biber' => 'peppers'])->map(function (string $en, string $tr): Ingredient {
            $m = Ingredient::query()->create(['name' => $tr, 'slug' => str($tr)->slug()->toString(), 'is_active' => true]);
            $m->translations()->create(['locale' => 'en', 'field' => 'name', 'value' => $en]);

            return $m;
        });

        $this->assertSame(['olives', 'peppers', 'quince'], Ingredient::dileGoreSirala($malzemeler->values())->map->name->all());
    }
}
