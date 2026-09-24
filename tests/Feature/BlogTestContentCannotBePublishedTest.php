<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Gövdesinde kendi test verisi olduğunu yazan ~3000 sayfa yayına alınıp sitemap'e
 * girdi. Aynı şeyin tekrar etmemesi için model seviyesinde kapı konuldu.
 */
final class BlogTestContentCannotBePublishedTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_post_that_declares_itself_test_data_cannot_be_published(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BLOG_TEST_CONTENT_CANNOT_BE_PUBLISHED');

        $this->makePost('published', "## Mutfak İpuçları\n\nBu taslak makale SEO performansı ve veritabanı testi için sistem tarafından otomatik oluşturulmuştur.\n\nTest verisi rwj3iyadwQ");
    }

    public function test_the_same_content_may_still_be_stored_as_a_draft(): void
    {
        $post = $this->makePost('draft', 'Test verisi abc123');

        $this->assertSame('draft', $post->status);
    }

    public function test_publishing_an_existing_draft_with_test_markers_is_refused(): void
    {
        $post = $this->makePost('draft', 'Bu taslak makale veritabanı testi için hazırlandı.');

        $this->expectException(RuntimeException::class);

        $post->update(['status' => 'published', 'published_at' => now()]);
    }

    public function test_genuine_content_publishes_without_obstruction(): void
    {
        $post = $this->makePost('published', 'Kıbrıs mutfağı, ada yaşamının pratikliğini paylaşmaya açık sofra alışkanlığıyla birleştirir.');

        $this->assertSame('published', $post->status);
    }

    private function makePost(string $status, string $body): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Kapı Testi',
            'slug' => 'kapi-testi-'.uniqid(),
            'excerpt' => 'Özet.',
            'body' => $body,
            'status' => $status,
            'published_at' => $status === 'published' ? now()->subDay() : null,
        ]);
    }
}
