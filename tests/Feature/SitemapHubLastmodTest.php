<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SitemapHubLastmodTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_driven_hubs_use_the_latest_natural_publication_date_and_policy_pages_omit_lastmod(): void
    {
        $cuisine = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $category = Category::query()->create(['name' => 'Ana Yemek', 'slug' => 'ana-yemek']);
        $ingredient = Ingredient::query()->create(['name' => 'Hellim', 'slug' => 'hellim']);

        for ($i = 0; $i < 5; $i++) {
            $recipe = Recipe::query()->create([
                'title' => 'Tarif '.$i,
                'slug' => 'tarif-'.$i,
                'status' => 'published',
                'verification_status' => 'verified',
                'published_at' => '2026-08-'.str_pad((string) (25 + $i), 2, '0', STR_PAD_LEFT).' 12:00:00',
                'cuisine_id' => $cuisine->id,
                'category_id' => $category->id,
            ]);
            $recipe->ingredients()->attach($ingredient->id, ['quantity' => 1, 'unit' => 'adet']);
        }

        BlogPost::query()->create([
            'title' => 'Blog', 'slug' => 'blog', 'excerpt' => 'Özet', 'body' => 'Gövde',
            'status' => 'published', 'published_at' => '2026-08-28 12:00:00',
        ]);

        $xml = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();

        foreach (['', 'tarifler', 'malzemeler', 'mutfaklar', 'kategoriler'] as $path) {
            $this->assertUrlHasLastmod($xml, url($path), '2026-08-29');
        }
        $this->assertUrlHasLastmod($xml, url('blog'), '2026-08-28');
        $this->assertUrlOmitsLastmod($xml, url('editorial-ilkeler'));
    }

    public function test_pages_sitemap_includes_xhtml_link_alternates_for_enabled_locales(): void
    {
        $xml = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();

        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="tr" href="'.url('tarifler').'"/>', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="en" href="'.url('en/recipes').'"/>', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="x-default" href="'.url('tarifler').'"/>', $xml);
    }

    private function assertUrlHasLastmod(string $xml, string $url, string $date): void
    {
        $this->assertMatchesRegularExpression('#<loc>'.preg_quote($url, '#').'</loc>\s*<lastmod>'.$date.'</lastmod>#', $xml);
    }

    private function assertUrlOmitsLastmod(string $xml, string $url): void
    {
        $this->assertDoesNotMatchRegularExpression('#<loc>'.preg_quote($url, '#').'</loc>\s*<lastmod>#', $xml);
    }
}
