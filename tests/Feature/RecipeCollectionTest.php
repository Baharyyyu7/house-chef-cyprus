<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_member_can_create_update_and_delete_a_private_collection(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('collections.store'), ['name' => 'Hafta içi', 'description' => 'Hızlı tarifler'])
            ->assertRedirect();

        $collection = $user->recipeCollections()->firstOrFail();
        $this->assertNotNull($collection->public_id);
        $this->actingAs($user)->patch(route('collections.update', $collection), ['name' => 'Hafta sonu', 'description' => null])->assertSessionHasNoErrors();
        $this->assertSame('Hafta sonu', $collection->refresh()->name);

        $this->actingAs($user)->delete(route('collections.destroy', $collection))->assertRedirect(route('collections.index'));
        $this->assertDatabaseMissing('recipe_collections', ['id' => $collection->id]);
    }

    public function test_collection_only_accepts_published_verified_recipes_without_duplicates(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $collection = $user->recipeCollections()->create(['name' => 'Akşam']);
        $recipe = Recipe::create(['title' => 'Yayın Tarif', 'slug' => 'yayin-tarif', 'status' => 'published', 'verification_status' => 'verified']);
        $draft = Recipe::create(['title' => 'Taslak Tarif', 'slug' => 'taslak-tarif', 'status' => 'draft', 'verification_status' => 'needs_review']);

        $parameters = ['recipeCollection' => $collection, 'recipe' => $recipe->slug];
        $this->actingAs($user)->post(route('collections.recipes.add', $parameters))->assertRedirect();
        $this->actingAs($user)->post(route('collections.recipes.add', $parameters))->assertRedirect();
        $this->assertSame(1, $collection->recipes()->count());
        $this->actingAs($user)->post(route('collections.recipes.add', ['recipeCollection' => $collection, 'recipe' => $draft->slug]))->assertNotFound();
    }

    public function test_collection_routes_enforce_authentication_ownership_and_public_identifiers(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);
        $collection = $owner->recipeCollections()->create(['name' => 'Özel']);

        $this->get(route('collections.index'))->assertRedirect('/login');
        $this->actingAs($other)->get(route('collections.show', $collection))->assertForbidden();
        $this->actingAs($other)->patch(route('collections.update', $collection), ['name' => 'Ele geçir', 'description' => null])->assertForbidden();
        $this->assertSame('Özel', $collection->refresh()->name);
        $this->assertStringContainsString((string) $collection->public_id, route('collections.show', $collection));
        $this->assertStringNotContainsString('/'.$collection->id, route('collections.show', $collection));
    }

    public function test_collection_page_never_renders_draft_or_unverified_recipes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $collection = $user->recipeCollections()->create(['name' => 'Güvenli']);
        $published = Recipe::create(['title' => 'Yayın Tarif', 'slug' => 'yayin-tarif', 'status' => 'published', 'verification_status' => 'verified']);
        $draft = Recipe::create(['title' => 'Gizli Taslak', 'slug' => 'gizli-taslak', 'status' => 'draft', 'verification_status' => 'needs_review']);
        $collection->recipes()->attach([$published->id, $draft->id]);

        $this->actingAs($user)->get(route('collections.show', $collection))
            ->assertOk()->assertSee('Yayın Tarif')->assertDontSee('Gizli Taslak');
    }
}
