<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductCoreAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_csp_contract_keeps_interactive_account_flows_operational(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
    }

    public function test_verified_member_can_open_their_favorites_and_only_see_their_own_recipes(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $otherMember = User::factory()->create(['email_verified_at' => now()]);
        $favorite = $this->recipe('Favori tarif');
        $otherFavorite = $this->recipe('Başka kullanıcının favorisi');
        $member->favoriteRecipes()->attach($favorite);
        $otherMember->favoriteRecipes()->attach($otherFavorite);

        $this->actingAs($member)->get(route('favorites.index'))
            ->assertOk()
            ->assertSee('Favori tarif')
            ->assertDontSee('Başka kullanıcının favorisi')
            ->assertSee(route('recipes.show', $favorite), false);
    }

    public function test_recipe_detail_shows_the_current_favorite_state_and_toggle_removes_it(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $recipe = $this->recipe('Durum tarifim');
        $member->favoriteRecipes()->attach($recipe);

        $this->actingAs($member)->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('♥ Favorilerden çıkar');

        $this->post(route('favorites.toggle', $recipe))->assertRedirect();
        $this->assertDatabaseMissing('favorites', ['user_id' => $member->id, 'recipe_id' => $recipe->id]);
    }

    public function test_member_can_edit_an_existing_pantry_item_without_creating_a_duplicate(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'Nane', 'canonical_name' => 'Nane', 'slug' => 'nane', 'is_active' => true]);
        $item = $member->pantryItems()->create(['ingredient_id' => $ingredient->id, 'quantity' => '1.00', 'unit' => 'demet']);

        $this->actingAs($member)->get(route('pantry.index', ['edit' => $item->id]))
            ->assertOk()
            ->assertSee('Dolabı güncelle')
            ->assertSee('Düzenlemeyi iptal et');
        $this->post(route('pantry.store'), ['ingredient_id' => $ingredient->id, 'quantity' => '250', 'unit' => 'g', 'expires_at' => '2026-08-01'])
            ->assertRedirect(route('pantry.index'));

        $this->assertSame(1, $member->pantryItems()->count());
        $this->assertDatabaseHas('pantry_items', ['id' => $item->id, 'quantity' => '250', 'unit' => 'g']);
    }

    public function test_member_cannot_favorite_or_start_cooking_a_draft_recipe(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $draft = $this->recipe('Gizli tarif');
        $draft->update(['status' => 'draft']);

        $this->actingAs($member)->post(route('favorites.toggle', $draft))->assertNotFound();
        $this->post(route('cooking-sessions.start', $draft), ['servings' => 2])->assertNotFound();
        $this->assertDatabaseMissing('favorites', ['user_id' => $member->id, 'recipe_id' => $draft->id]);
        $this->assertDatabaseMissing('cooking_sessions', ['user_id' => $member->id, 'recipe_id' => $draft->id]);
    }

    public function test_unpublished_favorites_and_other_users_pantry_edit_context_are_not_exposed(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $otherMember = User::factory()->create(['email_verified_at' => now()]);
        $draft = $this->recipe('Yayından kaldırılmış favori');
        $draft->update(['status' => 'draft']);
        $member->favoriteRecipes()->attach($draft);
        $ingredient = Ingredient::create(['name' => 'Dereotu', 'canonical_name' => 'Dereotu', 'slug' => 'dereotu', 'is_active' => true]);
        $otherItem = $otherMember->pantryItems()->create(['ingredient_id' => $ingredient->id, 'quantity' => '9', 'unit' => 'demet']);

        $this->actingAs($member)->get(route('favorites.index'))
            ->assertOk()
            ->assertDontSee('Yayından kaldırılmış favori');
        $this->get(route('pantry.index', ['edit' => $otherItem->id]))
            ->assertOk()
            ->assertDontSee('Dolabı güncelle')
            ->assertDontSee('value="9"', false);
    }

    private function recipe(string $title): Recipe
    {
        return Recipe::create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(8)),
            'servings' => 2,
            'difficulty' => 'Kolay',
            'status' => 'published',
        ]);
    }
}
