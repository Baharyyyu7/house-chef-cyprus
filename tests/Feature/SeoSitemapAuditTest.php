<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Seo\SitemapAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoSitemapAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_dynamic_sitemap_commands_accept_only_canonical_public_content(): void
    {
        $recipe = Recipe::query()->create(['title' => 'Yayınlanmış', 'slug' => 'yayinlanmis', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);
        $this->makeRecipePubliclyEligible($recipe);
        BlogPost::query()->create(['title' => 'Yayınlanmış blog', 'slug' => 'yayinlanmis-blog', 'excerpt' => 'Özet', 'body' => 'İçerik', 'status' => 'published', 'published_at' => now()]);

        $this->artisan('seo:sitemap-build --json')->expectsOutputToContain('dynamic_http_sitemap')->assertSuccessful();
        $this->artisan('seo:sitemap-audit --json')->expectsOutputToContain('"valid":true')->assertSuccessful();
        $this->assertSame(['count' => 1, 'excluded' => 0], app(SitemapAuditService::class)->audit()['sections']['recipes']);
    }

    public function test_sitemap_audit_reports_an_excluded_published_recipe_without_leaking_it(): void
    {
        Recipe::query()->create(['title' => 'İncelemesiz', 'slug' => 'incelemesiz', 'status' => 'published', 'verification_status' => 'needs_review', 'published_at' => now()]);

        $this->artisan('seo:sitemap-audit --json')->expectsOutputToContain('"recipes":{"count":0,"excluded":1}')->assertSuccessful();
        $this->get('/sitemaps/recipes.xml')->assertOk()->assertDontSee('incelemesiz');
    }

    public function test_verified_recipe_without_eligible_media_is_included_in_recipe_sitemap_but_excluded_from_image_sitemap(): void
    {
        Recipe::query()->create(['title' => 'Görselsiz', 'slug' => 'gorselsiz', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);

        $this->artisan('seo:sitemap-audit --json')
            ->expectsOutputToContain('"valid":true')
            ->assertSuccessful();

        $this->get('/sitemaps/recipes.xml')->assertOk()->assertSee('gorselsiz');
        $this->get('/sitemaps/images.xml')->assertOk()->assertDontSee('gorselsiz');
    }
}
