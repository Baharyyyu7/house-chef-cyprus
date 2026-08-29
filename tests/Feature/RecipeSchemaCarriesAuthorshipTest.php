<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tarif işaretlemesi yazar ve yayın tarihi taşımalı.
 *
 * İkisi de Google'ın önerdiği alanlar ve `author` E-E-A-T tarafında
 * doğrudan sayılıyor. Sitenin bütün konumlanması "doğrulanmış tarifler"
 * üzerine kurulu ama içeriğin arkasında kimin durduğu işaretlemede hiç
 * söylenmiyordu.
 *
 * Zorunlu alan olan `image` bilerek yalnız onaylı görseli olan tarifte
 * basılıyor: olmayan görseli iddia etmek, zengin sonuç kazanmaktan daha
 * kötü bir sonuç doğurur.
 */
final class RecipeSchemaCarriesAuthorshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_schema_names_an_author(): void
    {
        $tarif = $this->tarif();

        $düğüm = $this->recipeNode($tarif);

        $this->assertSame('Organization', $düğüm['author']['@type']);
        $this->assertNotEmpty($düğüm['author']['name']);
    }

    public function test_the_schema_carries_the_publication_date(): void
    {
        $tarif = $this->tarif();

        $this->assertNotEmpty($this->recipeNode($tarif)['datePublished']);
    }

    public function test_a_recipe_without_an_approved_photo_claims_no_image(): void
    {
        // image zorunlu alan; olmayan görseli uydurmak yerine alan hiç
        // basılmıyor. Bu, zengin sonuca girememek demek - görsel eksiğinin
        // görünür olmayan ikinci bedeli.
        $tarif = $this->tarif();

        $this->assertArrayNotHasKey('image', $this->recipeNode($tarif));
    }

    public function test_an_approved_photo_is_declared(): void
    {
        $tarif = $this->tarif();
        $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/ABC/source.jpg',
            'mime_type' => 'image/jpeg',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'approved_at' => now(),
        ]);

        $icerik = (string) $this->get(route('recipes.show', $tarif->fresh()))->assertOk()->getContent();

        $this->assertStringContainsString('"recipeIngredient"', $icerik);
    }

    /** @return array<string, mixed> */
    private function recipeNode(Recipe $tarif): array
    {
        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $icerik, $eslesme);

        foreach ($eslesme[1] as $ham) {
            $veri = json_decode(trim($ham), true);

            foreach ($veri['@graph'] ?? [$veri] as $dugum) {
                if (($dugum['@type'] ?? null) === 'Recipe') {
                    return $dugum;
                }
            }
        }

        $this->fail('Sayfada Recipe işaretlemesi bulunamadı.');
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Yayla Çorbası',
            'slug' => 'yayla-corbasi-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Yeterince uzun bir açıklama metni burada duruyor ve eşiği rahatça aşıyor; yayla çorbasının nasıl pişirildiğini anlatıyor.',
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
