<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Yayındaki tariflerin malzeme listesi gerçekten pişirilebilir olmalı.
 *
 * Üretimde 176 yayındaki tarifin 47'si değildi ve bunlar "published" +
 * "verified" damgası taşıyordu:
 *
 * - 34'ünde malzeme listesi birebir aynıydı: "500 g Ana Malzeme" artı
 *   zeytinyağı, soğan, sarımsak, tuz, karabiber. Kumpir de kokoreç de
 *   tantuni de aynı altı satır; asıl malzeme hiç yazılmamıştı.
 * - 13'ünde malzemeler tarifle çelişiyordu. "Mercimek Çorbası" kırmızı et
 *   ve balık istiyordu. Aynı dönen malzeme penceresi tekrarlıyordu, yani
 *   malzemeler tarife göre değil sırayla atanmıştı.
 *
 * Adım metinleri gerçek göründüğü için daha önceki yer tutucu taramasından
 * kaçmışlardı.
 */
final class PublishedRecipesHaveUsableIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** Malzeme listesinde asıl malzemenin yerine geçen yer tutucu. */
    private const YER_TUTUCU = 'Ana Malzeme';

    public function test_a_placeholder_ingredient_keeps_a_recipe_out_of_the_listing(): void
    {
        $tarif = $this->tarif('Ev Kumpiri', [self::YER_TUTUCU, 'Zeytinyağı']);

        $this->assertTrue($this->yerTutucuVar($tarif));
    }

    public function test_a_real_ingredient_list_passes(): void
    {
        // Daralma gerçek tarifleri elemeyi amaçlamıyor.
        $tarif = $this->tarif('Ev Kumpiri', ['Patates', 'Tereyağı', 'Kaşar Peyniri']);

        $this->assertFalse($this->yerTutucuVar($tarif));
    }

    public function test_no_published_recipe_carries_the_placeholder(): void
    {
        // Asıl koruma: kataloğun tamamı taranıyor. Yeni bir içe aktarma
        // yer tutucu bırakırsa burada düşer.
        $this->tarif('Gerçek Tarif', ['Patates', 'Tereyağı']);
        $this->tarif('Şablon Tarif', [self::YER_TUTUCU, 'Zeytinyağı'], 'draft');

        $yayindakiler = Recipe::query()
            ->where('status', 'published')
            ->with('recipeIngredients.ingredient')
            ->get()
            ->filter(fn (Recipe $tarif): bool => $this->yerTutucuVar($tarif));

        $this->assertCount(0, $yayindakiler, 'Yayındaki tarifte yer tutucu malzeme olmamalı: '.$yayindakiler->pluck('slug')->implode(', '));
    }

    public function test_a_title_ingredient_contradiction_is_detectable(): void
    {
        // "Mercimek Çorbası" içinde mercimek geçen bir malzeme olmadan
        // yayında duruyordu.
        $tarif = $this->tarif('Mercimek Çorbası', ['Kırmızı Et', 'Balık', 'Tuz']);

        $this->assertTrue($this->celiskiVar($tarif, 'mercimek'));
    }

    public function test_a_matching_ingredient_clears_the_contradiction_check(): void
    {
        $tarif = $this->tarif('Mercimek Çorbası', ['Kırmızı Mercimek', 'Kuru Soğan']);

        $this->assertFalse($this->celiskiVar($tarif, 'mercimek'));
    }

    private function yerTutucuVar(Recipe $tarif): bool
    {
        return $tarif->recipeIngredients
            ->map(fn ($satir): string => (string) ($satir->ingredient->name ?? ''))
            ->contains(self::YER_TUTUCU);
    }

    /** Başlıkta geçen malzeme adı listede var mı? */
    private function celiskiVar(Recipe $tarif, string $anahtar): bool
    {
        $kok = mb_substr(mb_strtolower($anahtar, 'UTF-8'), 0, 5);

        if (! str_contains(mb_strtolower($tarif->title, 'UTF-8'), $kok)) {
            return false;
        }

        foreach ($tarif->recipeIngredients as $satir) {
            if (str_contains(mb_strtolower((string) ($satir->ingredient->name ?? ''), 'UTF-8'), $kok)) {
                return false;
            }
        }

        return true;
    }

    /** @param list<string> $malzemeler */
    private function tarif(string $baslik, array $malzemeler, string $durum = 'published'): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'servings' => 4,
            'status' => $durum,
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        foreach ($malzemeler as $sira => $ad) {
            $malzeme = Ingredient::query()->firstOrCreate(
                ['name' => $ad],
                ['slug' => Str::slug($ad).'-'.uniqid()]
            );

            $tarif->recipeIngredients()->create([
                'ingredient_id' => $malzeme->id,
                'display_quantity' => 1,
                'display_measure_code' => 'g',
                'sort_order' => $sira + 1,
            ]);
        }

        return $tarif->load('recipeIngredients.ingredient');
    }
}
