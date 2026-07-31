<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Database\Seeders\HouseChefSeeder;
use Database\Seeders\ProductionBlogCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductionEditorialCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_editorial_seed_creates_twenty_reviewed_public_articles_with_real_recipe_links(): void
    {
        $this->seed([HouseChefSeeder::class, ProductionBlogCatalogueSeeder::class]);

        $this->assertSame(50, Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count());
        $this->assertSame(20, BlogPost::query()->publiclyVisible()->count());
        $this->assertSame(20, BlogPost::query()->whereNotNull('hero_media_id')->count());
        $this->assertSame(0, BlogPost::query()->where('body', 'like', '%lorem ipsum%')->count());
        $this->assertSame(0, BlogPost::query()->where('body', 'like', '%özgün demo%')->count());
        $this->assertSame(0, BlogPost::query()->doesntHave('recipes')->count());
        $this->assertSame(20, BlogPost::query()->where('fact_check_status', 'passed')->where('editorial_status', 'approved')->where('originality_status', 'passed')->count());
    }

    public function test_production_editorial_seed_is_idempotent_and_public_blog_pages_render(): void
    {
        $this->seed([HouseChefSeeder::class, ProductionBlogCatalogueSeeder::class]);
        $firstPost = BlogPost::query()->publiclyVisible()->firstOrFail();
        $firstPublicId = $firstPost->public_id;

        $this->seed(ProductionBlogCatalogueSeeder::class);

        $this->assertSame(20, BlogPost::query()->count());
        $this->assertSame($firstPublicId, $firstPost->fresh()->public_id);
        $this->get(route('blog.index'))->assertOk()->assertSee('Mutfak rehberi');
        $this->get(route('blog.show', $firstPost->fresh()))->assertOk()->assertSee('İlgili tarifler')->assertSee('İçerik ve kaynak notu');
    }
}
