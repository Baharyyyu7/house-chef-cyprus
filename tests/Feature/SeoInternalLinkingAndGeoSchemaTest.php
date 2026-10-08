<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoInternalLinkingAndGeoSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_schema_has_recipe_specific_faq_without_geo_speakable_or_difficulty(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Kıbrıs Şeftali Kebabı',
            'slug' => 'kibris-seftali-kebabi',
            'excerpt' => 'Geleneksel lezzet.',
            'description' => 'Geleneksel Kıbrıs kuzu gömleğinde şeftali kebabı tarifi ve pişirme püf noktaları.',
            'servings' => 4,
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'storage_notes' => [
                'buzdolabi_gun' => 3,
                'uyari' => 'Cam kapta buzdolabında saklayınız.',
            ],
            'tips' => ['Köz ateşi çok harlı olmamalıdır.'],
            'reheating_notes' => 'Fırında 160 derecede 8 dakika ısıtın.',
            'freezing_notes' => 'Pişirmeden önce gömleğe sarılı halde dondurulabilir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $this->makeRecipePubliclyEligible($recipe);

        $response = $this->get(route('recipes.show', $recipe))->assertOk();
        $content = (string) $response->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches);
        $this->assertNotEmpty($matches[1]);

        $foundFaq = false;
        $foundRecipe = false;

        foreach ($matches[1] as $raw) {
            $data = json_decode(trim($raw), true);
            foreach ($data['@graph'] ?? [$data] as $node) {
                if (($node['@type'] ?? null) === 'Recipe') {
                    $foundRecipe = true;
                    // 8 Ekim 2026 denetimi: her tarife Lefkoşa koordinatı,
                    // schema.org'da olmayan "difficulty" ve tarif sayfasında
                    // etkisiz speakable basılıyordu.
                    $this->assertArrayNotHasKey('speakable', $node);
                    $this->assertArrayNotHasKey('geo', $node);
                    $this->assertArrayNotHasKey('difficulty', $node);
                }
                if (($node['@type'] ?? null) === 'FAQPage') {
                    $foundFaq = true;
                    $this->assertNotEmpty($node['mainEntity']);
                    $questions = collect($node['mainEntity'])->pluck('name')->all();
                    $this->assertTrue(collect($questions)->some(fn ($q) => str_contains($q, 'nasıl saklanır')));
                    // Kalıp yedek sorular (porsiyon, süre) basılmaz.
                    $this->assertFalse(collect($questions)->some(fn ($q) => str_contains($q, 'kaç kişiliktir') || str_contains($q, 'kaç dakikada')));
                }
            }
        }

        $this->assertTrue($foundRecipe, 'Recipe node not found in schema');
        $this->assertTrue($foundFaq, 'FAQPage node not found in schema for GEO');
    }

    public function test_recipe_page_renders_related_editorial_guides(): void
    {
        $author = User::factory()->create();
        $category = BlogCategory::query()->create([
            'name' => 'Kıbrıs Gastronomisi',
            'slug' => 'kibris-gastronomisi',
        ]);

        $post = BlogPost::query()->create([
            'author_id' => $author->id,
            'blog_category_id' => $category->id,
            'title' => 'Kıbrıs Şeftali Kebabının Tarihsel Kökenleri',
            'slug' => 'kibris-seftali-kebabi-tarihsel-kokenleri',
            'excerpt' => 'Şeftali kebabının adadaki kökenleri ve lezzet sırları.',
            'body' => 'Geleneksel Kıbrıs mutfağında şeftali kebabı en köklü lezzetlerdendir.',
            'status' => 'published',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $recipe = Recipe::query()->create([
            'title' => 'Şeftali Kebabı',
            'slug' => 'seftali-kebabi-ozel',
            'excerpt' => 'Özel şeftali kebabı.',
            'description' => 'Açıklama metni yeterince uzun ve detaylı olmalı.',
            'servings' => 4,
            'prep_minutes' => 15,
            'cook_minutes' => 20,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $this->makeRecipePubliclyEligible($recipe);

        $response = $this->get(route('recipes.show', $recipe))->assertOk();
        $response->assertSee('Kıbrıs Şeftali Kebabının Tarihsel Kökenleri');
        $response->assertSee(route('blog.show', $post));
    }

    public function test_blog_post_renders_suggested_recipes_when_no_direct_recipes(): void
    {
        $author = User::factory()->create();
        $category = BlogCategory::query()->create([
            'name' => 'Akdeniz Beslenmesi',
            'slug' => 'akdeniz-beslenmesi',
        ]);

        $post = BlogPost::query()->create([
            'author_id' => $author->id,
            'blog_category_id' => $category->id,
            'title' => 'Doğal Zeytinyağlıların Sağlık Sırları',
            'slug' => 'dogal-zeytinyaglilarin-saglik-sirlari',
            'excerpt' => 'Zeytinyağlı yemeklerin faydaları.',
            'body' => 'Zeytinyağı Akdeniz mutfağının kalbidir.',
            'status' => 'published',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $recipe = Recipe::query()->create([
            'title' => 'Zeytinyağlı Enginar Dolması',
            'slug' => 'zeytinyagli-enginar-dolmasi',
            'excerpt' => 'Geleneksel enginar.',
            'description' => 'Lezzetli zeytinyağlı enginar dolması tarifi.',
            'servings' => 4,
            'prep_minutes' => 15,
            'cook_minutes' => 30,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $this->makeRecipePubliclyEligible($recipe);

        $response = $this->get(route('blog.show', $post))->assertOk();
        $response->assertSee('Zeytinyağlı Enginar Dolması');
    }
}
