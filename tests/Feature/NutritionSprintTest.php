<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\NutritionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NutritionSprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_metric_facts_calculate_recipe_and_per_serving_without_floats(): void
    {
        [$recipe, $ingredient, $nutrient] = $this->recipeWithNutrition('g', '250');
        $ingredient->nutritionFacts()->create(['nutrient_id' => $nutrient->id, 'value' => '10.5000', 'basis_type' => 'per_100g', 'basis_amount' => '100', 'source_type' => 'editorial', 'source_name' => 'Test', 'origin' => 'verified', 'confidence_score' => 100]);
        $result = app(NutritionService::class)->calculate($recipe, 2);
        $this->assertSame('26.250000', $result['nutrients']['protein']['amount']);
        $this->assertSame('13.125000', $result['nutrients']['protein']['per_serving']);
        $this->assertSame(100, $result['completeness_percentage']);
    }

    public function test_kilogram_and_litre_are_converted_but_household_units_are_reported_missing(): void
    {
        [$recipe, $ingredient, $nutrient] = $this->recipeWithNutrition('kg', '1');
        $ingredient->nutritionFacts()->create(['nutrient_id' => $nutrient->id, 'value' => 2, 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);
        $this->assertSame('20.000000', app(NutritionService::class)->calculate($recipe)['nutrients']['protein']['amount']);
        $recipe->recipeIngredients()->update(['unit' => 'yk', 'grams' => null]);
        app(NutritionService::class)->invalidate($recipe);
        $this->assertNotEmpty(app(NutritionService::class)->calculate($recipe)['missing_ingredients']);
    }

    public function test_snapshot_and_allergen_analysis_are_safe_and_traceable(): void
    {
        [$recipe, $ingredient] = $this->recipeWithNutrition();
        $allergen = Allergen::create(['public_id' => (string) Str::ulid(), 'code' => 'milk-test', 'name' => 'Süt', 'is_active' => true]);
        $ingredient->allergenDetails()->create(['allergen_id' => $allergen->id, 'relation_type' => 'contains', 'confidence_score' => 90, 'source_type' => 'editorial', 'is_verified' => true]);
        $snapshot = app(NutritionService::class)->snapshot($recipe);
        $this->assertDatabaseHas('recipe_nutrition_snapshots', ['recipe_id' => $recipe->id]);
        $this->assertSame('Süt', app(AllergenAnalysisService::class)->analyse($recipe)['groups']['contains'][0]['name']);
        $this->assertArrayHasKey('confidence_score', $snapshot);
    }

    public function test_nutrition_and_allergen_api_do_not_expose_internal_ids(): void
    {
        [$recipe, $ingredient, $nutrient] = $this->recipeWithNutrition();
        $ingredient->nutritionFacts()->create(['nutrient_id' => $nutrient->id, 'value' => 10, 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);
        $this->getJson('/api/v1/recipes/'.$recipe->public_id.'/nutrition')->assertOk()->assertJsonPath('data.basis.type', 'recipe');
        $this->getJson('/api/v1/ingredients/'.$ingredient->public_id.'/nutrition')->assertOk()->assertJsonMissingPath('data.id');
        $this->getJson('/api/v1/nutrients')->assertOk()->assertJsonPath('data.0.code', 'protein');
        $this->getJson('/api/v1/allergens')->assertOk();
        $this->getJson('/api/v1/dietary-tags')->assertOk();
    }

    /** @return array{0: Recipe, 1: Ingredient, 2: Nutrient} */
    private function recipeWithNutrition(string $unit = 'g', string $quantity = '100'): array
    {
        $ingredient = Ingredient::create(['name' => 'Test malzemesi', 'canonical_name' => 'Test malzemesi', 'slug' => 'test-'.Str::lower(Str::random(8)), 'is_active' => true]);
        $nutrient = Nutrient::create(['public_id' => (string) Str::ulid(), 'code' => 'protein', 'name' => 'Protein', 'unit' => 'g', 'category' => 'macro', 'is_active' => true]);
        $recipe = Recipe::create(['title' => 'Test tarif', 'slug' => 'tarif-'.Str::lower(Str::random(8)), 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published']);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => $quantity, 'unit' => $unit, 'grams' => null, 'sort_order' => 1]);

        return [$recipe, $ingredient, $nutrient];
    }
}
