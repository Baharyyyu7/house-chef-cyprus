<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientSubstitution;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use App\Services\IngredientAliasService;
use App\Services\IngredientMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class IngredientMatchingSprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_turkish_aliases_normalize_and_resolve_without_duplicates(): void
    {
        $ingredient = $this->ingredient('Yoğurt');
        $aliases = app(IngredientAliasService::class);
        $aliases->add($ingredient, 'Yoghurt');
        $aliases->add($ingredient, 'yoghurt');

        $this->assertSame(1, $ingredient->aliases()->count());
        $this->assertSame($ingredient->id, $aliases->resolve('YOĞHURT')->id);
    }

    public function test_exact_missing_and_substitution_matches_are_explainable(): void
    {
        [$recipe, $tomato, $yogurt] = $this->recipeWithIngredients();
        $user = User::factory()->create();
        PantryItem::create(['user_id' => $user->id, 'ingredient_id' => $tomato->id, 'quantity' => 1, 'is_available' => true]);
        $service = app(IngredientMatchingService::class);

        $missing = $service->match($recipe, $user);
        $this->assertSame(1, $missing['missing_required_count']);
        IngredientSubstitution::create(['ingredient_id' => $yogurt->id, 'substitute_ingredient_id' => $tomato->id, 'substitution_type' => 'emergency', 'ratio' => 1, 'confidence_score' => 50, 'is_verified' => false, 'is_active' => true]);
        $substituted = $service->match($recipe, $user);
        $this->assertTrue($substituted['can_cook_with_substitutions']);
        $this->assertFalse($substituted['available_substitutions'][0]['verified']);
    }

    public function test_expired_pantry_items_are_not_used_and_excluded_ingredients_remove_results(): void
    {
        [$recipe, $tomato] = $this->recipeWithIngredients();
        $user = User::factory()->create();
        PantryItem::create(['user_id' => $user->id, 'ingredient_id' => $tomato->id, 'quantity' => 1, 'expires_at' => today()->subDay(), 'is_available' => true]);
        $this->assertSame(2, app(IngredientMatchingService::class)->match($recipe, $user)['missing_required_count']);
        $results = app(IngredientMatchingService::class)->findRecipes([$tomato->public_id], [$tomato->public_id]);
        $this->assertCount(0, $results);
    }

    public function test_recipe_match_api_has_no_internal_ids(): void
    {
        [, $tomato] = $this->recipeWithIngredients();
        $response = $this->postJson('/api/v1/recipe-match', ['ingredients' => [$tomato->public_id]]);
        $response->assertOk()->assertJsonPath('data.0.recipe_public_id', fn ($id) => is_string($id));
        $this->assertStringNotContainsString('"id"', $response->getContent());
    }

    public function test_member_cannot_access_admin_ingredient_management(): void
    {
        $ingredient = $this->ingredient('Domates');
        $this->actingAs(User::factory()->create(['role' => 'member']))->get(route('admin.ingredients.edit', $ingredient))->assertForbidden();
    }

    /** @return array{0: Recipe, 1: Ingredient, 2: Ingredient} */
    private function recipeWithIngredients(): array
    {
        $tomato = $this->ingredient('Domates');
        $yogurt = $this->ingredient('Yoğurt');
        $recipe = Recipe::create(['title' => 'Eşleştirme Tarifi', 'slug' => 'eslestirme-'.Str::lower(Str::random(8)), 'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 10, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
        $recipe->ingredients()->attach($tomato->id, ['quantity' => 1, 'unit' => 'adet', 'sort_order' => 1]);
        $recipe->ingredients()->attach($yogurt->id, ['quantity' => 1, 'unit' => 'adet', 'sort_order' => 2]);

        return [$recipe, $tomato, $yogurt];
    }

    private function ingredient(string $name): Ingredient
    {
        return Ingredient::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'canonical_name' => $name, 'is_active' => true]);
    }
}
