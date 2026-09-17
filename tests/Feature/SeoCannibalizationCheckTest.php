<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class SeoCannibalizationCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_blog_posts_sharing_primary_keyword_case_insensitively(): void
    {
        $a = $this->makePost(['status' => 'published', 'published_at' => now(), 'primary_keyword' => 'kıbrıs mezeleri']);
        $b = $this->makePost(['status' => 'published', 'published_at' => now(), 'primary_keyword' => 'KIBRIS MEZELERİ']);

        Artisan::call('seo:cannibalization-check', ['--json' => true]);
        $out = Artisan::output();

        $this->assertStringContainsString('"detected":1', $out);
        $this->assertStringContainsString('"normalized":"kibris mezeleri"', $out);
        $this->assertStringContainsString('"page_count":2', $out);
        $this->assertStringContainsString($a->public_id, $out);
        $this->assertStringContainsString($b->public_id, $out);
        $this->assertStringContainsString(route('blog.show', $a), $out);
        $this->assertStringContainsString(route('blog.show', $b), $out);
    }

    public function test_detects_metadata_query_overlap_across_entity_types(): void
    {
        $post = $this->makePost(['status' => 'published', 'published_at' => now()]);
        $recipe = Recipe::query()->create([
            'title' => 'Fırında levrek', 'slug' => 'firinda-levrek', 'excerpt' => 'Özgün tarif özeti', 'description' => 'Özgün tarif açıklaması',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $post->seoMetadata()->create(['primary_query' => 'levrek tarifi']);
        $recipe->seoMetadata()->create(['primary_query' => 'levrek-tarifi']);

        Artisan::call('seo:cannibalization-check', ['--json' => true]);
        $out = Artisan::output();

        $this->assertStringContainsString('"detected":1', $out);
        $this->assertStringContainsString('"source":"primary_query"', $out);
        $this->assertStringContainsString('"type":"blog"', $out);
        $this->assertStringContainsString('"type":"recipe"', $out);
    }

    public function test_ignores_draft_and_unpublished_content(): void
    {
        $this->makePost(['status' => 'draft', 'primary_keyword' => 'unpublished keyword']);
        $this->makePost(['status' => 'draft', 'primary_keyword' => 'unpublished keyword']);

        Artisan::call('seo:cannibalization-check', ['--json' => true]);
        $out = Artisan::output();

        $this->assertStringContainsString('"detected":0', $out);
    }

    /** @param array<string, mixed> $overrides */
    private function makePost(array $overrides = []): BlogPost
    {
        return BlogPost::query()->create(array_merge([
            'title' => 'Özgün blog rehberi '.str()->random(8), 'slug' => 'ozgun-blog-rehberi-'.str()->lower(str()->random(8)),
            'excerpt' => 'Özgün ve faydalı kısa içerik özeti.', 'body' => 'House Chef Cyprus için özgün editoryal içerik.', 'status' => 'draft',
        ], $overrides));
    }
}
