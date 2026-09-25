<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Seo\BlogPublicationReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ElevateAndPublishBlogDraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_elevates_qualifying_draft_and_publishes_when_requested(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $recipe = Recipe::query()->create([
            'title' => 'Kıbrıs Kolokas',
            'slug' => 'kibris-kolokas',
            'excerpt' => 'Özgün tarif',
            'description' => 'Açıklama',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        // 750 kelimelik taslak oluştur (henüz hazır değil)
        $body = "## Giriş\n\n" . implode(' ', array_fill(0, 750, 'lezzet')) . "\n\n## Pişirme Adımları";
        $post = BlogPost::query()->create([
            'author_id' => $admin->id,
            'title' => 'Geleneksel Kıbrıs Yemekleri ve Püf Noktaları',
            'slug' => 'geleneksel-kibris-yemekleri-ve-puf-noktalari',
            'excerpt' => 'Kıbrıs yemeklerinin hazırlanışında dikkat edilmesi gerekenler.',
            'body' => $body,
            'status' => 'draft',
        ]);

        // Başlangıçta kalite kapısından geçmemeli (kelime az, SSS yok, tarif bağlı değil)
        $initialEval = app(BlogPublicationReadinessService::class)->evaluate($post);
        $this->assertFalse($initialEval['passed']);

        // Komutu çalıştır (--publish)
        $this->artisan('seo:elevate-and-publish-drafts', ['--publish' => true])
            ->assertSuccessful();

        $post->refresh();

        // Artık yayınlanmış olmalı
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->human_approved_at);
        $this->assertSame($admin->id, $post->human_approved_by_user_id);

        // Kalite kapısından 100% geçmiş olmalı
        $finalEval = app(BlogPublicationReadinessService::class)->evaluate($post);
        $this->assertTrue($finalEval['passed']);
        $this->assertSame(100, $finalEval['score']);
        $this->assertGreaterThanOrEqual(900, $finalEval['metrics']['word_count']);
        $this->assertGreaterThanOrEqual(3, $finalEval['metrics']['faq_count']);
        $this->assertTrue($post->recipes()->exists());
    }

    public function test_ignores_short_stubs_under_500_words(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $shortPost = BlogPost::query()->create([
            'author_id' => $admin->id,
            'title' => 'Kısa Test Taslağı',
            'slug' => 'kisa-test-taslagi',
            'excerpt' => 'Kısa.',
            'body' => 'Bu çok kısa bir metindir.',
            'status' => 'draft',
        ]);

        $this->artisan('seo:elevate-and-publish-drafts', ['--publish' => true])
            ->assertSuccessful();

        $shortPost->refresh();
        $this->assertSame('draft', $shortPost->status);
    }
}
