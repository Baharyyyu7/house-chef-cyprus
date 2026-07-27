<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\User;
use App\Services\IngredientMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HouseholdMeasurementFinalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_recipe_ingredient_from_a_household_conversion(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $recipe = Recipe::query()->firstOrFail();
        $ingredient = Ingredient::query()->where('slug', 'pirinc')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.recipes.ingredients.store', $recipe), ['ingredient_id' => $ingredient->id, 'display_quantity' => 2, 'display_measure_code' => 'su_bardagi', 'scaling_behavior' => 'scalable'])->assertRedirect();
        $this->assertDatabaseHas('recipe_ingredients', ['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id, 'canonical_quantity' => 360, 'canonical_unit' => 'g']);
    }

    public function test_manual_override_requires_a_reason_and_member_cannot_manage_recipe_ingredients(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $recipe = Recipe::query()->firstOrFail();
        $ingredient = Ingredient::query()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.recipes.ingredients.store', $recipe), ['ingredient_id' => $ingredient->id, 'display_quantity' => 1, 'display_measure_code' => 'kepce', 'manual_override' => true, 'canonical_quantity' => 100, 'canonical_unit' => 'g', 'scaling_behavior' => 'scalable'])->assertStatus(422);
        $this->actingAs($member)->get(route('admin.recipes.ingredients.index', $recipe))->assertForbidden();
    }

    public function test_matching_reports_canonical_quantity_sufficiency_and_expired_items_are_excluded(): void
    {
        $this->seed();
        $recipe = Recipe::query()->with('recipeIngredients.ingredient')->firstOrFail();
        $user = User::factory()->create();
        $line = $recipe->recipeIngredients->first();
        PantryItem::query()->create(['user_id' => $user->id, 'ingredient_id' => $line->ingredient_id, 'quantity' => 10000, 'unit' => 'g', 'canonical_quantity' => 10000, 'canonical_unit' => 'g', 'conversion_confidence' => 'high', 'is_available' => true]);
        $result = app(IngredientMatchingService::class)->match($recipe, $user);
        $this->assertContains($result['quantity_results'][0]['status'], ['sufficient', 'probably_sufficient']);
        PantryItem::query()->where('user_id', $user->id)->update(['expires_at' => now()->subDay()]);
        $expired = app(IngredientMatchingService::class)->match($recipe, $user);
        $this->assertSame(0, $expired['available']);
    }

    public function test_recipe_api_keeps_legacy_contract_and_exposes_measurement_fields_without_internal_ids(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $response = $this->getJson('/api/v1/recipes/'.$recipe->public_id)->assertOk();
        $response->assertJsonStructure(['data' => ['ingredients', 'ingredient_lines' => [['ingredient' => ['public_id', 'name'], 'display' => ['quantity', 'measure_code'], 'canonical' => ['quantity', 'unit'], 'conversion' => ['confidence', 'verified'], 'group']]]]);
        $this->assertStringNotContainsString('ingredient_id', json_encode($response->json('data.ingredient_lines')));
    }

    public function test_pantry_persists_a_canonical_household_measurement_and_rejects_an_inactive_unit(): void
    {
        $this->seed();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $ingredient = Ingredient::query()->where('slug', 'pirinc')->firstOrFail();

        $this->actingAs($user)->post(route('pantry.store'), [
            'ingredient_id' => $ingredient->id,
            'quantity' => 2,
            'unit' => 'su_bardagi',
        ])->assertRedirect(route('pantry.index'));

        $this->assertDatabaseHas('pantry_items', [
            'user_id' => $user->id,
            'ingredient_id' => $ingredient->id,
            'canonical_quantity' => 360,
            'canonical_unit' => 'g',
        ]);

        Unit::query()->where('code', 'su_bardagi')->update(['is_active' => false]);

        $this->actingAs($user)->from(route('pantry.index'))->post(route('pantry.store'), [
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
            'unit' => 'su_bardagi',
        ])->assertRedirect(route('pantry.index'))->assertSessionHasErrors('unit');
    }
}
