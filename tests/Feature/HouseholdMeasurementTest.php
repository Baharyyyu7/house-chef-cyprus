<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\User;
use App\Services\Measurements\HouseholdMeasurementFormatter;
use App\Services\Measurements\IngredientConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HouseholdMeasurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_taxonomy_and_demo_recipe_lines_have_household_and_canonical_data(): void
    {
        $this->seed();
        $this->assertDatabaseHas('units', ['code' => 'su_bardagi', 'supports_fractions' => true]);
        $this->assertDatabaseCount('recipes', 500);
        $this->assertDatabaseMissing('recipe_ingredients', ['display_measure_code' => null]);
        $this->assertDatabaseMissing('recipe_ingredients', ['canonical_quantity' => null]);
    }

    public function test_turkish_formatter_preserves_fraction_and_decimal_contract(): void
    {
        $unit = new Unit(['supports_fractions' => true, 'precision' => 2]);
        $formatter = app(HouseholdMeasurementFormatter::class);
        // Ölçeklenen tarif "4 yarım su bardağı" diyordu; kesir işareti hem
        // kısa hem her dilde aynı okunuyor.
        $this->assertSame('½', $formatter->quantity('0.5', $unit));
        $this->assertSame('¼', $formatter->quantity('0.25', $unit));
        $this->assertSame('1,5', $formatter->quantity('1.5'));
    }

    public function test_ingredient_specific_conversion_is_used_without_a_generic_gram_guess(): void
    {
        $this->seed();
        $ingredient = Ingredient::query()->where('slug', 'pirinc')->firstOrFail();
        $result = app(IngredientConversionService::class)->resolve($ingredient, 'su_bardagi', '2');
        $this->assertSame('360.0000', $result['quantity']);
        $this->assertSame('g', $result['unit']);
        $missing = app(IngredientConversionService::class)->resolve($ingredient, 'kepce', '1');
        $this->assertNull($missing['quantity']);
    }

    public function test_admin_can_manage_measurement_routes_while_member_is_denied(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.measurements.units'))->assertOk();
        $member = User::factory()->create(['email_verified_at' => now(), 'role' => 'member']);
        $this->actingAs($member)->get(route('admin.measurements.conversions'))->assertForbidden();
    }

    public function test_recipe_api_keeps_legacy_ingredients_and_exposes_structured_measurements(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $response = $this->getJson('/api/v1/recipes/'.$recipe->public_id)->assertOk();
        $response->assertJsonPath('data.public_id', $recipe->public_id)->assertJsonStructure(['data' => ['ingredients', 'ingredient_lines' => [['ingredient' => ['public_id', 'name'], 'display' => ['quantity', 'measure_code'], 'canonical' => ['quantity', 'unit'], 'conversion' => ['confidence']]]]]);
        $this->assertStringNotContainsString('"id"', json_encode($response->json('data.ingredient_lines')));
    }
}
