<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeIngredientGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeIngredientGroupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_groups_and_preserves_ingredient_data_when_a_group_is_removed(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $recipe = Recipe::query()->with('recipeIngredients', 'ingredientGroups')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.recipes.ingredients.groups.store', $recipe), ['name' => 'Sos için'])->assertRedirect();
        $group = RecipeIngredientGroup::query()->where('recipe_id', $recipe->id)->where('name', 'Sos için')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.recipes.ingredients.groups.update', [$recipe, $group]), ['name' => 'Üzeri için'])->assertRedirect();

        $line = $recipe->recipeIngredients->firstOrFail();
        $this->actingAs($admin)->post(route('admin.recipes.ingredients.move', [$recipe, $line]), ['group_public_id' => $group->public_id])->assertRedirect();
        $this->assertDatabaseHas('recipe_ingredients', ['id' => $line->id, 'recipe_ingredient_group_id' => $group->id]);
        $this->actingAs($admin)->delete(route('admin.recipes.ingredients.groups.destroy', [$recipe, $group]))->assertRedirect();
        $this->assertDatabaseHas('recipe_ingredients', ['id' => $line->id, 'group_name' => 'Malzemeler']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'recipe_ingredient_group.deleted']);
    }

    public function test_member_cannot_manage_groups_and_a_group_cannot_be_moved_across_recipes(): void
    {
        $this->seed();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $recipes = Recipe::query()->with(['recipeIngredients', 'ingredientGroups'])->take(2)->get();
        $this->actingAs($member)->post(route('admin.recipes.ingredients.groups.store', $recipes[0]), ['name' => 'Sos'])->assertForbidden();
        $this->actingAs(User::query()->where('email', 'admin@housechef.local')->firstOrFail())
            ->post(route('admin.recipes.ingredients.move', [$recipes[0], $recipes[0]->recipeIngredients->first()]), ['group_public_id' => $recipes[1]->ingredientGroups->first()->public_id])
            ->assertNotFound();
    }

    public function test_admin_can_reorder_groups_and_duplicate_group_names_are_rejected_safely(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $recipe = Recipe::query()->with('ingredientGroups')->firstOrFail();
        $first = $recipe->ingredientGroups->sortBy('sort_order')->firstOrFail();
        $last = $recipe->ingredientGroups->sortByDesc('sort_order')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.recipes.ingredients.groups.reorder', [$recipe, $last]), ['direction' => 'up'])->assertRedirect();
        $this->assertLessThan($first->fresh()->sort_order, $last->fresh()->sort_order);

        $this->actingAs($admin)->from(route('admin.recipes.ingredients.index', $recipe))->put(
            route('admin.recipes.ingredients.groups.update', [$recipe, $last]),
            ['name' => $first->name],
        )->assertRedirect(route('admin.recipes.ingredients.index', $recipe))->assertSessionHasErrors('name');
    }
}
