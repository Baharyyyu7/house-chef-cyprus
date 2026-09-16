<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * "Şefin Püf Noktası" + "Evde Yoksa Ne Kullanılır?" GEO kutusu.
 *
 * Yapay zekâ alıntısı için kutu hem görünür HTML'de hem de JSON-LD'de
 * (Comment düğümleri) aynı metinle durur. Veri yoksa kutu hiç basılmaz;
 * boş başlık, ince içerik demektir.
 */
final class RecipeGeoCalloutTest extends TestCase
{
    use RefreshDatabase;

    public function test_ipucu_ve_ikame_kutusu_gorunur_ve_semada(): void
    {
        $tarif = $this->tarif('GEO Kutulu Tarif', ['Kapağı ilk 15 dakika açma.']);
        $krema = Ingredient::query()->create(['name' => 'Krema', 'slug' => 'krema-'.uniqid()]);
        RecipeIngredient::query()->create([
            'recipe_id' => $tarif->id, 'ingredient_id' => $krema->id,
            'substitution' => 'Süzme yoğurt + bir kaşık tereyağı',
        ]);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('id="tips"', $icerik);
        $this->assertStringContainsString('Kapağı ilk 15 dakika açma.', $icerik);
        $this->assertStringContainsString('Evde Yoksa Ne Kullanılır?', $icerik);
        $this->assertStringContainsString('Süzme yoğurt + bir kaşık tereyağı', $icerik);

        $sema = $this->tarifSemasi($icerik);
        $yorumlar = collect($sema['comment'] ?? [])->pluck('text')->all();
        $this->assertContains('Kapağı ilk 15 dakika açma.', $yorumlar);
        $this->assertContains('Krema yoksa: Süzme yoğurt + bir kaşık tereyağı', $yorumlar);
    }

    public function test_veri_yoksa_kutu_basılmaz(): void
    {
        $tarif = $this->tarif('Kutusuz Tarif', []);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringNotContainsString('Evde Yoksa Ne Kullanılır?', $icerik);
        $sema = $this->tarifSemasi($icerik);
        $this->assertArrayNotHasKey('comment', $sema);
    }

    /** @param list<string> $ipuclari */
    private function tarif(string $baslik, array $ipuclari): Recipe
    {
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi-'.uniqid()]);
        $kategori = \App\Models\Category::query()->firstOrCreate(['slug' => 'ana-yemek'], ['name' => 'Ana Yemek']);

        $recipe = Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama metni.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
            'cuisine_id' => $mutfak->id,
            'category_id' => $kategori->id,
            'tips' => $ipuclari,
        ]);

        $this->makeRecipePubliclyEligible($recipe);

        return $recipe;
    }

    /** @return array<string, mixed> */
    private function tarifSemasi(string $icerik): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $icerik, $eslesmeler);
        foreach ($eslesmeler[1] ?? [] as $ham) {
            $veri = json_decode($ham, true, flags: JSON_THROW_ON_ERROR);
            foreach ($veri['@graph'] ?? [] as $dugum) {
                if (($dugum['@type'] ?? null) === 'Recipe') {
                    return $dugum;
                }
            }
        }

        $this->fail('Sayfada Recipe şeması bulunamadı.');

        return [];
    }
}
