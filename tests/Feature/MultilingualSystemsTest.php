<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Seo\IndexNowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MultilingualSystemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_contains_bilingual_references_and_english_crosslink(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus')
            ->assertSee('/en/llms.txt')
            ->assertSee('/en/feed')
            ->assertSee('/en/opensearch.xml');
    }

    public function test_english_llms_txt_returns_proper_english_geo_content(): void
    {
        $cuisine = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $category = Category::query()->create(['name' => 'Ana Yemek', 'slug' => 'ana-yemek']);

        Recipe::query()->create([
            'cuisine_id' => $cuisine->id,
            'category_id' => $category->id,
            'title' => 'Kıbrıs Şeftali Kebabı',
            'slug' => 'kibris-seftali-kebabi',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'servings' => 4,
            'description' => 'Geleneksel Kıbrıs lezzeti.',
            'published_at' => now(),
        ]);

        $response = $this->get('/en/llms.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus - Mediterranean & Cypriot Culinary Encyclopedia', false)
            ->assertSee('Authentic Cypriot Sheftalia Kebab', false)
            ->assertSee('Traditional Cypriot Heritage Recipes', false)
            ->assertSee('/en/llms-full.txt', false)
            ->assertSee('/en/feed', false);
    }

    public function test_english_llms_full_txt_returns_complete_english_corpus(): void
    {
        $cuisine = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $category = Category::query()->create(['name' => 'Ana Yemek', 'slug' => 'ana-yemek']);

        Recipe::query()->create([
            'cuisine_id' => $cuisine->id,
            'category_id' => $category->id,
            'title' => 'Kıbrıs Usulü Kleftiko',
            'slug' => 'kibris-usulu-kleftiko',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 15,
            'cook_minutes' => 240,
            'servings' => 6,
            'description' => 'Fırında kuzu tandır.',
            'published_at' => now(),
        ]);

        $response = $this->get('/en/llms-full.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus - Full Recipe & Guide Corpus (English)', false)
            ->assertSee('Traditional Cypriot Kleftiko', false)
            ->assertSee('Cuisine: Cypriot Cuisine', false)
            ->assertSee('Timing: Prep 15 min, Cook 240 min', false);
    }

    public function test_turkish_and_english_rss_feeds_serve_appropriate_locales(): void
    {
        $cuisine = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $category = Category::query()->create(['name' => 'Ana Yemek', 'slug' => 'ana-yemek']);

        Recipe::query()->create([
            'cuisine_id' => $cuisine->id,
            'category_id' => $category->id,
            'title' => 'Kıbrıs Şeftali Kebabı',
            'slug' => 'kibris-seftali-kebabi',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'servings' => 4,
            'description' => 'Geleneksel lezzet.',
            'published_at' => now(),
        ]);

        // Turkish feed
        $trResponse = $this->get('/feed');
        $trResponse->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->assertSee('<language>tr-CY</language>', false)
            ->assertSee('Kıbrıs Şeftali Kebabı');

        // English feed
        $enResponse = $this->get('/en/feed');
        $enResponse->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->assertSee('<language>en-US</language>', false)
            ->assertSee('House Chef Cyprus - Mediterranean &amp; Cypriot Recipes', false)
            ->assertSee('Authentic Cypriot Sheftalia Kebab');
    }

    public function test_opensearch_supports_both_turkish_and_english(): void
    {
        $trResponse = $this->get('/opensearch.xml');
        $trResponse->assertOk()
            ->assertSee('<ShortName>House Chef</ShortName>', false)
            ->assertSee('Kıbrıs ve Akdeniz yemek tarifleri ara');

        $enResponse = $this->get('/en/opensearch.xml');
        $enResponse->assertOk()
            ->assertSee('<ShortName>House Chef (EN)</ShortName>', false)
            ->assertSee('Search verified Cypriot and Mediterranean recipes');
    }

    public function test_english_manifest_serves_localized_pwa_metadata(): void
    {
        $response = $this->get('/en/manifest.webmanifest');

        $response->assertOk()
            ->assertSee('House Chef Cyprus')
            ->assertSee('Cypriot Recipes')
            ->assertSee('Cooking Guides');
    }

    public function test_indexnow_includes_multilingual_endpoints(): void
    {
        $service = app(IndexNowService::class);
        $urls = $service->collectRecentUrls();

        $this->assertContains(route('llms.txt'), $urls);
        $this->assertContains(route('llms.txt.en'), $urls);
        $this->assertContains(route('feed'), $urls);
        $this->assertContains(route('feed.en'), $urls);
        $this->assertContains(route('opensearch'), $urls);
        $this->assertContains(route('opensearch.en'), $urls);
        $this->assertContains(route('manifest.en'), $urls);
    }
}
