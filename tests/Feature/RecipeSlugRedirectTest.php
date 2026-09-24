<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tarif adresleri rastgele bir sonek taşıyor ("adana-kebabi-MT5XA"). Tek bir
 * toplu içerik çalışması 678 tarifin 628'inin sonekini değiştirdi; canlıyla
 * yerel arasında yalnız 50 slug ortak kaldı. Aynı şey canlıda olsaydı
 * paylaşılmış ve indekslenmiş her tarif adresi sessizce 404 verecekti.
 */
final class RecipeSlugRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_old_suffix_redirects_to_the_current_one(): void
    {
        $recipe = $this->publish('adana-kebabi-Y5MTK');

        $this->get('/tarifler/adana-kebabi-MT5XA')
            ->assertRedirect(route('recipes.show', $recipe))
            ->assertStatus(301);
    }

    public function test_the_current_address_is_served_directly(): void
    {
        $recipe = $this->publish('adana-kebabi-Y5MTK');

        $this->get(route('recipes.show', $recipe))->assertOk();
    }

    public function test_an_ambiguous_base_name_is_not_guessed(): void
    {
        // Aynı asıl ada iki tarif düşüyorsa hangisinin kastedildiği bilinemez;
        // yanlış tarife yönlendirmektense 404 doğrudur.
        $this->publish('limonlu-tavuk-AAAAA');
        $this->publish('limonlu-tavuk-BBBBB');

        $this->get('/tarifler/limonlu-tavuk-CCCCC')->assertNotFound();
    }

    public function test_an_unknown_recipe_still_returns_404(): void
    {
        $this->get('/tarifler/hic-olmayan-tarif-ZZZZZ')->assertNotFound();
    }

    public function test_a_draft_recipe_is_not_a_redirect_target(): void
    {
        Recipe::query()->create([
            'title' => 'Taslak Tarif',
            'slug' => 'taslak-tarif-QQQQQ',
            'status' => 'draft',
            'verification_status' => 'verified',
        ]);

        $this->get('/tarifler/taslak-tarif-WWWWW')->assertNotFound();
    }

    private function publish(string $slug): Recipe
    {
        return Recipe::query()->create([
            'title' => str($slug)->headline()->value(),
            'slug' => $slug,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
        ]);
    }
}
