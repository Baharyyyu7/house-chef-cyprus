<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoIndexabilityGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_driven_recipe_discovery_is_noindex_with_a_stable_canonical(): void
    {
        $this->get('/tarifler?q=hellim&sort=popular')
            ->assertOk()
            ->assertSee('name="robots" content="noindex,follow"', false)
            ->assertSee('rel="canonical" href="'.route('recipes.index').'"', false);
    }

    public function test_recipe_serving_variants_are_noindex_and_keep_the_recipe_canonical(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Porsiyonlu tarif', 'slug' => 'porsiyonlu-tarif', 'excerpt' => 'Kısa açıklama', 'description' => 'Tarif açıklaması',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(), 'servings' => 4,
        ]);

        $this->get(route('recipes.show', $recipe).'?servings=6')
            ->assertOk()
            ->assertSee('name="robots" content="noindex,follow"', false)
            ->assertSee('rel="canonical" href="'.route('recipes.show', $recipe).'"', false);
    }

    public function test_blog_search_is_noindex_with_the_canonical_listing_url(): void
    {
        BlogPost::query()->create(['title' => 'Kıbrıs mutfağı notu', 'slug' => 'kibris-mutfagi-notu', 'excerpt' => 'Kısa not', 'body' => 'Özgün içerik', 'status' => 'published', 'published_at' => now()]);

        $this->get('/blog?q=kibris')
            ->assertOk()
            ->assertSee('name="robots" content="noindex,follow"', false)
            ->assertSee('rel="canonical" href="'.route('blog.index').'"', false);
    }

    public function test_production_deployment_guard_rejects_a_canonical_or_robots_regression(): void
    {
        config()->set('app.url', 'https://housechefcyprus.com');
        config()->set('production.canonical_host', 'wrong.example');
        config()->set('production.canonical_scheme', 'https');
        config()->set('seo.indexing_enabled', true);

        $this->artisan('seo:deployment-guard --environment=production --json')->assertFailed();

        config()->set('production.canonical_host', 'housechefcyprus.com');
        $this->artisan('seo:deployment-guard --environment=production --json')->assertSuccessful();
    }
}
