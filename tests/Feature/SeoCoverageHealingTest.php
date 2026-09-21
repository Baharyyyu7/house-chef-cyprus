<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoCoverageHealingTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_blog_post_with_published_twin_redirects_301(): void
    {
        $author = User::factory()->create();

        $published = BlogPost::query()->create([
            'author_id' => $author->id,
            'title' => 'Akdeniz Diyeti ve Sağlık',
            'slug' => 'akdeniz-diyeti-ve-saglik-0XP2B',
            'excerpt' => 'Özet.',
            'body' => str_repeat('Özgün editoryal metin. ', 40),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draft = BlogPost::query()->create([
            'author_id' => $author->id,
            'title' => 'Akdeniz Diyeti ve Sağlık',
            'slug' => 'akdeniz-diyeti-ve-saglik',
            'excerpt' => 'Özet.',
            'body' => str_repeat('Özgün editoryal metin. ', 40),
            'status' => 'draft',
        ]);

        $this->get('/blog/' . $draft->slug)
            ->assertStatus(301)
            ->assertRedirect(route('blog.show', $published));
    }

    public function test_draft_blog_post_with_slug_redirect_redirects_301(): void
    {
        $author = User::factory()->create();

        $published = BlogPost::query()->create([
            'author_id' => $author->id,
            'title' => 'Kıbrıs Çakıstes Rehberi',
            'slug' => 'kibris-cakistes-rehberi-yeni',
            'excerpt' => 'Özet.',
            'body' => str_repeat('Özgün editoryal metin. ', 40),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draft = BlogPost::query()->create([
            'author_id' => $author->id,
            'title' => 'Eski Çakıstes Notu',
            'slug' => 'eski-cakistes-taslak',
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'draft',
        ]);

        SlugRedirect::query()->create([
            'model_type' => BlogPost::class,
            'old_slug' => $draft->slug,
            'model_id' => $published->id,
        ]);

        $this->get('/blog/' . $draft->slug)
            ->assertStatus(301)
            ->assertRedirect(route('blog.show', $published));
    }

    public function test_geo_2026_missing_slug_redirects_to_published_post(): void
    {
        $author = User::factory()->create();

        $published = BlogPost::query()->create([
            'author_id' => $author->id,
            'title' => 'Karpaz Dağ Kekiği',
            'slug' => 'karpaz-dag-kekigi-ve-yabani-otlar',
            'excerpt' => 'Özet.',
            'body' => str_repeat('Özgün editoryal metin. ', 40),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/geo-2026-karpaz-dag-kekigi-ve-yabani-otlar')
            ->assertStatus(301)
            ->assertRedirect(route('blog.show', $published));
    }

    public function test_draft_recipe_with_published_twin_redirects_301(): void
    {
        $published = Recipe::query()->create([
            'title' => 'Domatesli Tavuk Sote',
            'slug' => 'domatesli-tavuk-sote',
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
            'prep_minutes' => 15,
            'cook_minutes' => 20,
        ]);
        $this->makeRecipePubliclyEligible($published);

        $draft = Recipe::query()->create([
            'title' => 'Domatesli Tavuk Sote',
            'slug' => 'domatesli-tavuk-5GGDA',
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'draft',
            'verification_status' => 'verified',
            'servings' => 4,
            'prep_minutes' => 15,
            'cook_minutes' => 20,
        ]);

        $this->get('/tarifler/' . $draft->slug)
            ->assertStatus(301)
            ->assertRedirect(route('recipes.show', $published->slug));
    }

    public function test_seo_heal_redirects_command_runs_successfully(): void
    {
        $this->artisan('seo:heal-redirects', ['--apply' => true])
            ->assertSuccessful();
    }
}
