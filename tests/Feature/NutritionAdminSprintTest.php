<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Models\User;
use App\Services\NutritionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NutritionAdminSprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_open_nutrition_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member', 'email_verified_at' => now()]))->get(route('admin.nutrition.index'))->assertForbidden();
    }

    public function test_admin_can_create_nutrient_and_verified_facts_require_source(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'Test', 'canonical_name' => 'Test', 'slug' => 'test-'.Str::random(8)]);
        $nutrient = Nutrient::create(['public_id' => (string) Str::ulid(), 'code' => 'protein', 'name' => 'Protein', 'unit' => 'g']);
        $this->actingAs($admin)->post(route('admin.nutrition.nutrients.store'), ['code' => 'fiber', 'name' => 'Lif', 'unit' => 'g', 'decimal_precision' => 2])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.nutrition.facts.store', $ingredient), ['nutrient_id' => $nutrient->id, 'value' => '2', 'basis_type' => 'per_100g', 'basis_amount' => '100', 'source_type' => 'editorial', 'confidence_score' => 90, 'origin' => 'verified'])->assertSessionHasErrors('source_name');
    }

    public function test_recipe_ingredient_change_invalidates_existing_snapshot(): void
    {
        $ingredient = Ingredient::create(['name' => 'Test', 'canonical_name' => 'Test', 'slug' => 'test-'.Str::random(8)]);
        $recipe = Recipe::create(['title' => 'Test', 'slug' => 'tarif-'.Str::random(8), 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published']);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => 100, 'unit' => 'g']);
        app(NutritionService::class)->snapshot($recipe);
        $recipe->recipeIngredients()->firstOrFail()->update(['quantity' => 150]);
        $this->assertNotNull($recipe->nutritionSnapshots()->firstOrFail()->fresh()->invalidated_at);
    }
}
