<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeEmbedTest extends TestCase
{
    use RefreshDatabase;

    public function test_embed_returns_ok_for_verified_recipe(): void
    {
        $recipe = $this->createRecipe();

        $response = $this->get(route('recipes.embed', $recipe));

        $response->assertOk()
            ->assertSee($recipe->title)
            ->assertSee(route('recipes.show', $recipe))
            ->assertSee('House Chef Cyprus')
            ->assertSee('Doğrulanmış');

        // Verify iframe security headers permit external embedding
        $this->assertFalse($response->headers->has('X-Frame-Options'));
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('frame-ancestors *', $csp);
    }

    /**
     * Gömme sürümü aramada tarifin ikinci kopyası olarak yarışmamalı.
     * 24 Eylül 2026: /fa/embed/ ve /ru/embed/ Search Console'da gösterim
     * alıyordu ve kanonikleri 301 veren /ru/tarifler/ yazımına gidiyordu.
     */
    public function test_embed_dizine_kapali_ve_kanonigi_dilin_kendi_adresi(): void
    {
        $recipe = $this->createRecipe();

        $this->get(route('recipes.embed', $recipe))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false);

        $this->get('/ru/embed/recipes/'.$recipe->slug)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/ru/recepty/'.$recipe->slug).'">', false)
            ->assertDontSee('/ru/tarifler/', false);
    }

    public function test_embed_supports_english_language(): void
    {
        $recipe = $this->createRecipe();

        $response = $this->get(route('recipes.embed', ['recipe' => $recipe->slug, 'lang' => 'en']));

        $response->assertOk()
            ->assertSee($recipe->title)
            ->assertSee('Verified Recipe')
            ->assertSee('min')
            ->assertSee('View Recipe & Steps →');
    }

    public function test_embed_returns_404_for_draft_recipe(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Taslak Tarif',
            'slug' => 'taslak-tarif-'.uniqid(),
            'status' => 'draft',
            'verification_status' => 'unverified',
        ]);

        $this->get(route('recipes.embed', $recipe))->assertNotFound();
    }

    public function test_recipe_show_page_includes_embed_snippet(): void
    {
        $recipe = $this->createRecipe();
        $this->makeRecipePubliclyEligible($recipe);

        $response = $this->get(route('recipes.show', $recipe));

        $response->assertOk()
            ->assertSee('Tarifi Sitene Ekle')
            ->assertSee(route('recipes.embed', $recipe));
    }

    private function createRecipe(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Kıbrıs Şeftali Kebabı',
            'slug' => 'kibris-seftali-kebabi-'.uniqid(),
            'excerpt' => 'Geleneksel lezzet.',
            'description' => 'Ayrıntılı tarif açıklaması.',
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'servings' => 4,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
