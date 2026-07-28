<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminCrudV3Test extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_management_v3_filters_and_keeps_internal_ids_out_of_the_markup(): void
    {
        $admin = $this->admin();
        $cuisine = Cuisine::create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris']);
        $category = Category::create(['name' => 'Ana yemek', 'slug' => 'ana-yemek']);
        $recipe = Recipe::create(['title' => 'V3 Test Tarifi', 'slug' => 'v3-test-tarifi', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(), 'cuisine_id' => $cuisine->id, 'category_id' => $category->id]);

        $this->actingAs($admin)->get(route('admin.recipes.index', ['q' => 'V3 Test', 'verification_status' => 'verified']))
            ->assertOk()
            ->assertSee('Tarif yönetimi')
            ->assertSee('V3 Test Tarifi')
            ->assertDontSee('>'.$recipe->id.'<', false);
    }

    public function test_ingredient_v3_list_and_recipe_editor_are_admin_only(): void
    {
        $admin = $this->admin();
        $ingredient = Ingredient::create(['name' => 'V3 Domates', 'canonical_name' => 'V3 Domates', 'slug' => 'v3-domates', 'is_active' => true]);
        $recipe = Recipe::create(['title' => 'Düzenleme Tarifi', 'slug' => 'duzenleme-tarifi', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'draft']);

        $this->actingAs($admin)->get(route('admin.ingredients.index'))->assertOk()->assertSee('Malzemeler')->assertSee('V3 Domates');
        $this->actingAs($admin)->get(route('admin.ingredients.edit', $ingredient))->assertOk()->assertSee('Aliaslar')->assertSee('Alternatifler');
        $this->actingAs($admin)->get(route('admin.recipes.edit', $recipe))->assertOk()->assertSee('Yayınlama kontrolü')->assertSee('Malzemeler ve adımlar');

        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $this->actingAs($member)->get(route('admin.recipes.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.ingredients.index'))->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
