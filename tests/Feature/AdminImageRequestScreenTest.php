<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use App\Services\Media\ImageDeliveryRequestBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Görselleri Antigravity üretiyor ve talep bugüne kadar bir betikle masaüstüne
 * yazılan metin dosyasıyla iletiliyordu. Bu ekran hangi yemeğin görselinin
 * eksik olduğunu gösterir ve talebi kopyalanabilir hâlde verir.
 */
final class AdminImageRequestScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_a_published_recipe_that_has_no_usable_image(): void
    {
        $this->recipe('Adana Kebabı', 'adana-kebabi');

        $this->actingAsAdmin()
            ->get(route('admin.image-requests.index'))
            ->assertOk()
            ->assertSee('Adana Kebabı', false)
            ->assertSee('recipe--adana-kebabi.png', false);
    }

    public function test_a_template_recipe_is_never_offered_for_image_production(): void
    {
        // Adı bir yemeği tarif etmeyen kayıt için üretilecek fotoğrafın
        // göstereceği bir şey yoktur; kredi boşa gider.
        $this->recipe('Akdeniz Mutfağı Usulü Limonlu', 'akdeniz-mutfagi-usulu-limonlu');

        $this->actingAsAdmin()
            ->get(route('admin.image-requests.index'))
            ->assertOk()
            ->assertDontSee('Akdeniz Mutfağı Usulü Limonlu', false);
    }

    public function test_the_request_carries_the_resolution_and_no_stretch_rule(): void
    {
        $recipe = $this->recipe('Mercimek Çorbası', 'mercimek-corbasi');
        $metin = app(ImageDeliveryRequestBuilder::class)->request($recipe);

        $this->assertStringContainsString('Mercimek Çorbası', $metin);
        $this->assertStringContainsString('1600x1350', $metin);
        $this->assertStringContainsString('germeyin', $metin);
        $this->assertStringContainsString('recipe--mercimek-corbasi.png', $metin);
    }

    public function test_the_prompt_names_the_distinctive_ingredients_and_skips_salt(): void
    {
        $recipe = $this->recipe('Adana Kebabı', 'adana-kebabi', ['Kuzu Kıyması', 'Tuz', 'Pul Biber']);
        $prompt = app(ImageDeliveryRequestBuilder::class)->prompt($recipe);

        $this->assertStringContainsString('Kuzu Kıyması', $prompt);
        $this->assertStringContainsString('Pul Biber', $prompt);
        $this->assertStringNotContainsString('Tuz', $prompt);
    }

    public function test_a_member_cannot_open_the_screen(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'member', 'email_verified_at' => now()]))
            ->get(route('admin.image-requests.index'))
            ->assertForbidden();
    }

    /** @param list<string> $malzemeler */
    private function recipe(string $title, string $slug, array $malzemeler = []): Recipe
    {
        $recipe = Recipe::query()->create([
            'title' => $title,
            'slug' => $slug,
            'status' => 'published',
            'verification_status' => 'verified',
            'cuisine_id' => Cuisine::query()->create(['name' => 'Türk Mutfağı', 'slug' => 'turk-mutfagi-'.uniqid()])->id,
        ]);

        foreach ($malzemeler as $sira => $ad) {
            RecipeIngredient::query()->create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => Ingredient::query()->create(['public_id' => (string) Str::ulid(), 'name' => $ad, 'slug' => Str::slug($ad).'-'.uniqid()])->id,
                'sort_order' => $sira + 1,
            ]);
        }

        return $recipe->fresh(['cuisine', 'category', 'ingredients']);
    }

    private function actingAsAdmin(): self
    {
        return $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]));
    }
}
