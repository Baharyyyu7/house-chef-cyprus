<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\DietaryTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 12 Ekim 2026'da canlıda /de/rezepte süzgeci diyet ve alerjen adlarını
 * Türkçe basıyordu ("Düşük kalorili", "Yer fıstığı"): seçenek Türkçe adı
 * anahtar sanıyordu, o anahtar yalnız en.json'da vardı. Kodlu anahtar
 * (diet.*, allergen.*) yedi dilde var.
 */
final class FilterOptionsSpeakPageLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_filter_options_follow_the_page_language(): void
    {
        DietaryTag::query()->create(['public_id' => (string) Str::ulid(), 'code' => 'low_calorie', 'name' => 'Düşük kalorili', 'is_active' => true]);
        Allergen::query()->create(['public_id' => (string) Str::ulid(), 'code' => 'peanut', 'name' => 'Yer fıstığı', 'is_active' => true]);
        Allergen::query()->create(['public_id' => (string) Str::ulid(), 'code' => 'yeni-kod', 'name' => 'Yeni alerjen', 'is_active' => true]);

        $this->get('/en/recipes')->assertOk()->assertSee('>Peanut<', false)->assertSee('>Low calorie<', false)->assertDontSee('Yer fıstığı')->assertDontSee('Düşük kalorili')->assertSee('Yeni alerjen');
        $this->get('/tarifler')->assertOk()->assertSee('Yer fıstığı')->assertSee('Düşük kalorili');
    }

    public function test_every_locale_names_every_diet_and_allergen_code(): void
    {
        foreach (['tr', 'en', 'de', 'fa', 'pl', 'pt', 'ru'] as $dil) {
            $sozluk = json_decode((string) file_get_contents(lang_path($dil.'.json')), true);
            foreach (['vegan', 'vegetarian', 'gluten_free', 'lactose_free', 'dairy_free', 'high_protein', 'low_calorie', 'ketogenic', 'halal_suitable'] as $kod) {
                $this->assertArrayHasKey('diet.'.$kod, $sozluk, $dil);
            }
            foreach (['celery', 'crustaceans', 'egg', 'fish', 'gluten', 'lupin', 'milk', 'molluscs', 'mustard', 'peanut', 'sesame', 'soy', 'sulphites', 'tree_nuts'] as $kod) {
                $this->assertArrayHasKey('allergen.'.$kod, $sozluk, $dil);
            }
        }
    }
}
