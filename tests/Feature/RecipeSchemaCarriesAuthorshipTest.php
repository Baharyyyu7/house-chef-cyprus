<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->makeRecipePubliclyEligible($tarif);

        $düğüm = $this->recipeNode($tarif);

        $this->assertSame('Organization', $düğüm['author']['@type']);
        $this->assertNotEmpty($düğüm['author']['name']);
    }

    public function test_the_schema_carries_the_publication_date(): void
    {
        $tarif = $this->tarif();
        $this->makeRecipePubliclyEligible($tarif);

        $this->assertNotEmpty($this->recipeNode($tarif)['datePublished']);
    }

    public function test_a_recipe_without_an_approved_photo_emits_no_invalid_recipe_schema(): void
    {
        $tarif = $this->tarif();
        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('name="robots" content="noindex,follow"', $icerik);
        $this->assertNull($this->findRecipeNode($icerik));
    }

    public function test_an_approved_photo_is_declared(): void
    {
        $tarif = $this->tarif();
        $this->makeRecipePubliclyEligible($tarif);

        $icerik = (string) $this->get(route('recipes.show', $tarif->fresh()))->assertOk()->getContent();
        $node = $this->findRecipeNode($icerik);

        $this->assertNotNull($node);
        $this->assertNotEmpty($node['image'] ?? []);
        $this->assertStringContainsString('name="robots" content="index,follow"', $icerik);
    }

    public function test_recipe_sitemap_only_lists_recipes_with_eligible_media(): void
    {
        $hazir = $this->tarif();
        $hazir->update(['slug' => 'gorselli-tarif']);
        $this->makeRecipePubliclyEligible($hazir);
        $eksik = $this->tarif();
        $eksik->update(['slug' => 'gorselsiz-tarif']);

        $this->get(route('sitemap.recipes'))
            ->assertOk()
            ->assertSee('gorselli-tarif', false)
            ->assertDontSee('gorselsiz-tarif', false);
    }

    public function test_how_to_step_urls_point_to_real_visible_anchors(): void
    {
        $tarif = $this->tarif();
        $this->makeRecipePubliclyEligible($tarif);
        $tarif->steps()->create(['position' => 1, 'title' => 'Karıştırın', 'instruction' => 'Malzemeleri karıştırın.']);

        $icerik = (string) $this->get(route('recipes.show', $tarif->fresh()))->assertOk()->getContent();
        $node = $this->findRecipeNode($icerik);

        $this->assertSame(route('recipes.show', $tarif).'#step-1', $node['recipeInstructions'][0]['url'] ?? null);
        $this->assertStringContainsString('id="step-1"', $icerik);
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

    /** @return array<string, mixed>|null */
    private function findRecipeNode(string $icerik): ?array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $icerik, $eslesme);

        foreach ($eslesme[1] as $ham) {
            $veri = json_decode(trim($ham), true);
            foreach ($veri['@graph'] ?? [$veri] as $dugum) {
                if (($dugum['@type'] ?? null) === 'Recipe') {
                    return $dugum;
                }
            }
        }

        return null;
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
