<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\IngredientHubController;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alt bilgi, olmayan bir sayfaya bağlantı vermez.
 *
 * 23 Eylül 2026'da ölçüldü: alt bilgideki "Hellim" bağlantısı sitenin her
 * sayfasında duruyordu ve İngilizcede 404 veriyordu. Malzeme sayfası eşiği
 * o dile çevrilmiş tarif sayısına bakıyor; bağlantıyı basan yerin de aynı
 * ölçüte bakması gerekiyordu. Alt bilgi her sayfada olduğu için bu, site
 * genelinde kırık bir bağlantıydı.
 */
final class FooterDoesNotLinkMissingHubTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug, Ingredient $malzeme): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Bu tarifin açıklaması testte kullanılmak üzere yazıldı ve yeterlidir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
        ]);
        $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => '200', 'unit' => 'g', 'sort_order' => 1]);
    }

    public function test_yeterli_tarif_yokken_baglanti_basilmaz(): void
    {
        $hellim = Ingredient::query()->create(['name' => 'Hellim', 'slug' => 'hellim', 'is_active' => true]);
        $this->tarif('tek-tarif', $hellim);

        $this->assertFalse(IngredientHubController::sayfasiVarMi('hellim'));
        $this->get('/')->assertOk()->assertDontSee(route('ingredients.hub.show', 'hellim'), false);
    }

    public function test_yeterli_tarif_varken_baglanti_basilir(): void
    {
        $hellim = Ingredient::query()->create(['name' => 'Hellim', 'slug' => 'hellim', 'is_active' => true]);

        for ($i = 1; $i <= 5; $i++) {
            $this->tarif('hellimli-tarif-'.$i, $hellim);
        }

        $this->assertTrue(IngredientHubController::sayfasiVarMi('hellim'));
        $this->get('/')->assertOk()->assertSee(route('ingredients.hub.show', 'hellim'), false);
    }
}
