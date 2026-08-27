<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\UnpublishPlaceholderIngredientRecipes;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Yayında 27 tarif vardı ve hepsinin malzeme listesi birebir aynıydı:
 * "Ana Malzeme, Zeytinyağı, Kuru Soğan, Sarımsak, Tuz, Karabiber". Baklava
 * da, trileçe de, turşu da. Bu bir tarif değil, doldurulmamış şablonun
 * kendisiydi ve yemek sitesinde yanlış malzeme en ağır hatadır.
 *
 * Ölçüt dar tutuldu: gerçek hiçbir tarifte "Ana Malzeme" adlı bir malzeme
 * yoktur. Bu testin asıl işi, kuralın gerçek tarifleri yakalamadığını
 * garanti etmek - soğan ve sarımsak içeren sahici bir tarif yayında kalmalı.
 */
final class PlaceholderIngredientRecipesLeavePublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_recipe_whose_ingredients_were_never_filled_in_leaves_publication(): void
    {
        $sablon = $this->tarif('Karamelli Trileçe', ['Ana Malzeme', 'Zeytinyağı', 'Kuru Soğan', 'Sarımsak']);

        $this->artisan('recipes:unpublish-placeholder-ingredients --apply')->assertSuccessful();

        $this->assertSame('draft', $sablon->fresh()->status);
    }

    public function test_a_real_recipe_with_onion_and_garlic_stays_published(): void
    {
        // Kural malzeme adlarına bakıyor; soğan sarımsak içeren gerçek bir
        // tarifi yakalarsa yayından sahici içerik düşer.
        $gercek = $this->tarif('Zeytinyağlı Barbunya', ['Barbunya', 'Zeytinyağı', 'Kuru Soğan', 'Sarımsak', 'Domates']);

        $this->artisan('recipes:unpublish-placeholder-ingredients --apply')->assertSuccessful();

        $this->assertSame('published', $gercek->fresh()->status);
    }

    public function test_a_recipe_with_no_ingredients_at_all_leaves_publication(): void
    {
        $bos = $this->tarif('Adı Var Kendi Yok', []);

        $this->artisan('recipes:unpublish-placeholder-ingredients --apply')->assertSuccessful();

        $this->assertSame('draft', $bos->fresh()->status);
    }

    public function test_the_publication_date_is_kept_so_it_can_come_back(): void
    {
        $tarih = now()->subMonth();
        $sablon = $this->tarif('Kadayıf Dolması', ['Ana Malzeme', 'Tuz'], $tarih);

        $this->artisan('recipes:unpublish-placeholder-ingredients --apply')->assertSuccessful();

        $this->assertNotNull($sablon->fresh()->published_at);
        $this->assertTrue($sablon->fresh()->published_at->isSameDay($tarih));
    }

    public function test_without_apply_nothing_changes(): void
    {
        $sablon = $this->tarif('Karışık Turşu', ['Ana Malzeme']);

        $this->artisan('recipes:unpublish-placeholder-ingredients')->assertSuccessful();

        $this->assertSame('published', $sablon->fresh()->status);
    }

    public function test_the_detector_names_the_placeholder_it_looks_for(): void
    {
        $this->assertSame('ana malzeme', UnpublishPlaceholderIngredientRecipes::YER_TUTUCU);
    }

    /** @param list<string> $malzemeler */
    private function tarif(string $baslik, array $malzemeler, $yayinTarihi = null): Recipe
    {
        $recipe = Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => $yayinTarihi ?? now(),
        ]);

        foreach ($malzemeler as $sira => $ad) {
            $ingredient = Ingredient::query()->firstOrCreate(
                ['name' => $ad],
                ['slug' => Str::slug($ad).'-'.uniqid()]
            );
            $recipe->ingredients()->attach($ingredient->id, ['sort_order' => $sira + 1]);
        }

        return $recipe->load('ingredients');
    }
}
