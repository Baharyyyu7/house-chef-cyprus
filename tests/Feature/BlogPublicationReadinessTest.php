<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Seo\BlogPublicationReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogPublicationReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_human_approved_post_is_ready(): void
    {
        $result = app(BlogPublicationReadinessService::class)->evaluate($this->readyPost());

        $this->assertTrue($result['passed']);
        $this->assertSame([], $result['issues']);
        $this->assertSame(900, $result['metrics']['word_count']);
    }

    public function test_short_post_without_human_approval_or_sources_is_blocked(): void
    {
        $post = $this->readyPost([
            'body' => '## Başlık'.PHP_EOL.implode(' ', array_fill(0, 897, 'kelime')),
            'human_approved_at' => null,
            'human_approved_by_user_id' => null,
            'source_notes' => null,
        ]);

        $result = app(BlogPublicationReadinessService::class)->evaluate($post);

        $this->assertFalse($result['passed']);
        $this->assertContains('İçerik en az 900 kelime olmalıdır.', $result['issues']);
        $this->assertContains('İnsan yayın onayı kaydı gerekli.', $result['issues']);
        $this->assertContains('Kaynak/fact-check notu gerekli.', $result['issues']);
    }

    /** @param array<string,mixed> $overrides */
    private function readyPost(array $overrides = []): BlogPost
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = BlogPost::query()->create(array_merge([
            'author_id' => $admin->id,
            'human_approved_by_user_id' => $admin->id,
            'human_approved_at' => now(),
            'title' => 'Kıbrıs mutfağı için kapsamlı rehber',
            'slug' => 'kibris-mutfagi-kapsamli-rehber',
            'excerpt' => 'Kıbrıs mutfağını ölçüler, teknikler ve doğrulanmış tariflerle anlatan kapsamlı rehber.',
            'body' => '## Temel bilgiler'.PHP_EOL.implode(' ', array_fill(0, 896, 'kelime')).PHP_EOL.'## Uygulama adımları',
            'status' => 'draft',
            'content_purpose' => 'Ev aşçılarına doğrulanmış Kıbrıs mutfağı rehberi sunmak',
            'search_intent' => 'informational',
            'primary_keyword' => 'Kıbrıs mutfağı',
            'secondary_keywords' => ['Kıbrıs yemekleri', 'Kıbrıs tarifleri', 'Akdeniz mutfağı'],
            'faq_blocks' => [
                ['question' => 'Kıbrıs mutfağı nedir?', 'answer' => 'Ada ürünlerine dayalı mutfaktır.'],
                ['question' => 'Hangi tarifle başlanır?', 'answer' => 'Doğrulanmış temel tariflerle başlanır.'],
                ['question' => 'Hellim nasıl kullanılır?', 'answer' => 'Tarife göre pişirilir.'],
            ],
            'source_notes' => ['editorial_note' => 'Kaynaklar ve tarif ölçüleri editoryal olarak doğrulandı.'],
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
            'meta_title' => 'Kıbrıs Mutfağı: Doğrulanmış Başlangıç Rehberi',
            'meta_description' => str_repeat('a', 150),
        ], $overrides));

        $recipe = Recipe::query()->create([
            'title' => 'Doğrulanmış tarif', 'slug' => 'dogrulanmis-tarif',
            'excerpt' => 'Özgün tarif özeti', 'description' => 'Doğrulanmış tarif açıklaması',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $post->recipes()->sync([$recipe->id]);

        return $post;
    }
}
