<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Recipe.image yemeğin kendi fotoğrafı olmak zorunda.
 *
 * 20 Eylül 2026'da ölçüldü: fotoğrafı olmayan 21 tarif, marka varsayılanı
 * og-default.jpg'yi "tarifin görseli" diye şemada ilan ediyordu. Eski kural
 * "mutfağı ve kategorisi varsa yeterli" diyordu; mutfak bilgisi fotoğrafın
 * yerini tutmaz. Yanlış görsel bildirmektense Recipe nesnesini hiç basmamak
 * doğrudur.
 */
final class RecipeSchemaNeedsARealPhotoTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Bu tarifin açıklaması testte kullanılmak üzere yazıldı ve yeterlidir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'cuisine_id' => Cuisine::query()->create(['name' => 'Kıbrıs', 'slug' => 'kibris-'.$slug])->id,
            'category_id' => Category::query()->create(['name' => 'Ana Yemek', 'slug' => 'ana-yemek-'.$slug])->id,
        ]);
    }

    public function test_fotografsiz_tarif_marka_gorselini_kendi_gorseli_diye_bildirmez(): void
    {
        $tarif = $this->tarif('fotografsiz');

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringNotContainsString('"@type":"Recipe"', $icerik);
        $this->assertStringNotContainsString('"image":["'.url(config('brand.assets.og_default')).'"]', $icerik);
    }

    public function test_onayli_fotografi_olan_tarif_recipe_nesnesini_basar(): void
    {
        $tarif = $this->tarif('fotografli');
        $this->makeRecipePubliclyEligible($tarif);

        $icerik = (string) $this->get(route('recipes.show', $tarif->fresh()))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"Recipe"', $icerik);
    }
}
