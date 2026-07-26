<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NutritionSearchContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_uses_computed_protein_filter_and_does_not_expose_integer_ids(): void
    {
        [$recipe, $ingredient, $nutrient] = $this->recipeWithProtein('20');
        $ingredient->nutritionFacts()->create(['nutrient_id' => $nutrient->id, 'value' => '20', 'basis_type' => 'per_100g', 'basis_amount' => '100', 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);

        $this->getJson('/api/v1/recipes?min_protein=10&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.public_id', $recipe->public_id)
            ->assertJsonMissingPath('data.0.id')
            ->assertJsonPath('meta.per_page', 1);
    }

    public function test_api_rejects_invalid_nutrition_filter_and_too_high_page_size(): void
    {
        $this->getJson('/api/v1/recipes?min_protein=-1')->assertStatus(422)->assertJsonPath('error_code', 'VALIDATION_ERROR');
        $this->getJson('/api/v1/recipes?per_page=51')->assertStatus(422)->assertJsonPath('error_code', 'VALIDATION_ERROR');
    }

    public function test_web_service_api_and_recipe_match_share_the_same_computed_filter_results(): void
    {
        [$recipe, $ingredient, $nutrient] = $this->recipeWithProtein('20');
        $ingredient->nutritionFacts()->create(['nutrient_id' => $nutrient->id, 'value' => '20', 'basis_type' => 'per_100g', 'basis_amount' => '100', 'source_type' => 'editorial', 'origin' => 'verified', 'confidence_score' => 100]);
        $filters = ['min_protein' => 10, 'verified_only' => true, 'per_page' => 12];
        $serviceIds = collect(app(RecipeSearchInterface::class)->search($filters)->items())->pluck('public_id')->values()->all();
        $apiIds = collect($this->getJson('/api/v1/recipes?min_protein=10&verified_only=1')->json('data'))->pluck('public_id')->values()->all();
        $matchIds = collect($this->postJson('/api/v1/recipe-match', ['filters' => $filters])->json('data'))->pluck('recipe_public_id')->values()->all();

        $this->assertSame([$recipe->public_id], $serviceIds);
        $this->assertSame($serviceIds, $apiIds);
        $this->assertSame($serviceIds, $matchIds);
    }

    public function test_allergen_exclusion_contract_validates_an_array_and_rejects_unknown_or_oversized_input(): void
    {
        $allergen = Allergen::create(['public_id' => (string) Str::ulid(), 'code' => 'test-milk', 'name' => 'Test süt', 'is_active' => true]);
        $this->getJson('/api/v1/recipes?exclude_allergens[]='.$allergen->public_id)->assertOk();
        $this->getJson('/api/v1/recipes?exclude_allergens[]=unknown')->assertStatus(422)->assertJsonPath('error_code', 'VALIDATION_ERROR');
        $this->postJson('/api/v1/recipe-match', ['filters' => ['exclude_allergens' => array_fill(0, 21, $allergen->public_id)]])->assertStatus(422)->assertJsonPath('error_code', 'VALIDATION_ERROR');
    }

    /** @return array{0: Recipe, 1: Ingredient, 2: Nutrient} */
    private function recipeWithProtein(string $protein): array
    {
        $ingredient = Ingredient::create(['name' => 'Protein malzemesi', 'canonical_name' => 'Protein malzemesi', 'slug' => 'protein-'.Str::lower(Str::random(8)), 'is_active' => true]);
        $nutrient = Nutrient::create(['public_id' => (string) Str::ulid(), 'code' => 'protein', 'name' => 'Protein', 'unit' => 'g', 'category' => 'macro', 'is_active' => true]);
        $recipe = Recipe::create(['title' => 'Protein tarif', 'slug' => 'protein-tarif-'.Str::lower(Str::random(8)), 'description' => 'Açıklama', 'servings' => 1, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published']);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => '100', 'unit' => 'g', 'sort_order' => 1]);

        return [$recipe, $ingredient, $nutrient];
    }
}
