<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Seo\InternalLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalSeoInternationalLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_internal_link_suggestions_only_offer_translated_records(): void
    {
        $post = BlogPost::query()->create(['title' => 'Kitchen guide', 'slug' => 'kitchen-guide', 'excerpt' => 'Summary', 'body' => 'Guide', 'status' => 'draft']);
        Recipe::query()->create(['title' => 'Untranslated recipe', 'slug' => 'untranslated', 'status' => 'published', 'verification_status' => 'verified']);
        $recipe = Recipe::query()->create(['title' => 'Base recipe title', 'slug' => 'translated', 'status' => 'published', 'verification_status' => 'verified']);
        foreach (['title' => 'English recipe title', 'excerpt' => 'English summary', 'description' => 'English description'] as $field => $value) {
            $recipe->translations()->create(['locale' => 'en', 'field' => $field, 'value' => $value]);
        }
        app()->setLocale('en');

        $suggestions = app(InternalLinkService::class)->suggestionsFor($post);
        $this->assertCount(1, $suggestions);
        $this->assertSame('English recipe title', $suggestions[0]['label']);
        $this->assertStringEndsWith('/recipes/translated', $suggestions[0]['url']);
    }

    public function test_public_blog_html_keeps_explicit_editorial_noindex(): void
    {
        config()->set('seo.indexing_enabled', true);
        $post = BlogPost::query()->create([
            'title' => 'Kitchen guide', 'slug' => 'kitchen-guide', 'excerpt' => 'Summary',
            'body' => str_repeat('The guide explains how to prepare the ingredients. ', 70),
            'status' => 'published', 'published_at' => now()->subDay(),
            'fact_check_status' => 'passed', 'originality_status' => 'passed',
        ]);
        $post->seoMetadata()->create(['robots' => 'noindex,nofollow']);

        $this->get(route('blog.show', $post))->assertOk()->assertSee('name="robots" content="noindex,nofollow"', false);
        $this->get(route('sitemap.blog'))->assertOk()->assertDontSee('/blog/kitchen-guide', false);
    }
}
