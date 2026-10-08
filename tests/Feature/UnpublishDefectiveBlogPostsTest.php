<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UnpublishDefectiveBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $slug, string $govde): BlogPost
    {
        return BlogPost::query()->create(['title' => $slug, 'slug' => $slug, 'excerpt' => 'Ö', 'body' => $govde, 'status' => 'published', 'published_at' => now()->subDay()]);
    }

    public function test_template_and_named_posts_are_drafted_with_a_backup(): void
    {
        Storage::fake('local');
        $sablon = $this->yazi('sablon', str_repeat('Bu konu hakkında daha fazla detaya girmek gerekirse araştırmalar gösteriyor. ', 30));
        $unlu = $this->yazi('trump-en-sevdigi-yemekler', $this->cesitliMetin('Gerçek bir kişi hakkında kaynaksız iddia', 10));
        $iyi = $this->yazi('hellim', $this->cesitliMetin('Hellim keçi ve koyun sütünden yapılır', 10));

        $this->artisan('blog:kusurlulari-yayindan-al --kusurlu --slug=trump-en-sevdigi-yemekler')->assertSuccessful();
        $this->assertSame('published', $sablon->fresh()->status);

        $this->artisan('blog:kusurlulari-yayindan-al --kusurlu --slug=trump-en-sevdigi-yemekler --apply')->assertSuccessful();

        $this->assertSame('draft', $sablon->fresh()->status);
        $this->assertSame('draft', $unlu->fresh()->status);
        $this->assertSame('published', $iyi->fresh()->status);
        $this->assertCount(1, Storage::disk('local')->files('yedek'));
    }
}
