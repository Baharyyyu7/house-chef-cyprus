<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentFactoryReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * İçerik fabrikası taslakları AI ve içe aktarmadan gelir. Önizleme gövdeyi
 * Markdown'dan HTML'e çevirirken ham HTML'i geçirseydi, taslağa sokulmuş
 * bir betik yönetici ekranı açtığı anda onun oturumunda çalışırdı.
 */
final class AdminDraftPreviewEscapesHtmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_body_cannot_inject_script_into_the_admin_screen(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $yazi = BlogPost::query()->create([
            'title' => 'Zararlı taslak',
            'slug' => 'zararli-taslak',
            'excerpt' => 'Özet.',
            'body' => "## Ara başlık\n\n<script>alert('xss')</script>\n\n<img src=x onerror=alert(1)>\n\n[tıkla](javascript:alert(2))",
            'status' => 'draft',
        ]);
        $inceleme = ContentFactoryReview::query()->create([
            'content_type' => 'blog',
            'entity_type' => BlogPost::class,
            'entity_public_id' => $yazi->public_id,
            'stage' => 'human_review',
            'status' => 'pending',
        ]);

        $yanit = $this->actingAs($admin)
            ->get(route('admin.content-factory.reviews.show', $inceleme))
            ->assertOk()
            ->assertSee('<h2>Ara başlık</h2>', false);

        $yanit->assertDontSee("<script>alert('xss')</script>", false);
        $yanit->assertDontSee('onerror=alert(1)', false);
        $yanit->assertDontSee('javascript:alert(2)', false);
    }
}
