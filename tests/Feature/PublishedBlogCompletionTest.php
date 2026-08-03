<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentRevision;
use App\Models\User;
use App\Services\PublishedBlogCompletionService;
use Database\Seeders\HouseChefSeeder;
use Database\Seeders\ProductionBlogCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublishedBlogCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([HouseChefSeeder::class, ProductionBlogCatalogueSeeder::class]);
    }

    public function test_audit_reports_all_twenty_public_articles_without_mutating_them(): void
    {
        $before = BlogPost::query()->publiclyVisible()->pluck('updated_at', 'id');
        $result = app(PublishedBlogCompletionService::class)->audit();

        $this->assertSame(20, $result['blogs_audited']);
        $this->assertSame(20, $result['needs_expansion']);
        $this->assertSame(20, $result['needs_media']);
        $this->assertFalse($result['auto_publish']);

        $this->assertSame(0, ContentRevision::query()->count());
        $this->assertEquals($before, BlogPost::query()->publiclyVisible()->pluck('updated_at', 'id'));
    }

    public function test_prepare_creates_idempotent_private_review_packs_for_every_article(): void
    {
        $result = app(PublishedBlogCompletionService::class)->prepare();
        $this->assertSame(20, $result['revisions_created']);
        $this->assertFalse($result['published_content_changed']);

        $this->assertSame(20, ContentRevision::query()->where('reason', 'published-blog-completion-v1')->count());
        ContentRevision::query()->each(function (ContentRevision $revision): void {
            $pack = (array) $revision->changed_seo_fields;
            $this->assertGreaterThanOrEqual(4, substr_count((string) $revision->proposed_body, '## '));
            $this->assertGreaterThan(1200, mb_strlen((string) $revision->proposed_body));
            $this->assertSame('NEEDS_EDITOR_REVIEW', $pack['status']);
            $this->assertSame('NEEDS_MEDIA_APPROVAL', $pack['media_state']);
            $this->assertFalse($pack['auto_publish']);
            $this->assertTrue($pack['media_brief']['human_approval_required']);
        });

        $second = app(PublishedBlogCompletionService::class)->prepare();
        $this->assertSame(0, $second['revisions_created']);
        $this->assertSame(20, $second['revisions_updated']);
        $this->assertSame(20, ContentRevision::query()->count());
        $this->assertSame(20, BlogPost::query()->publiclyVisible()->count());
    }

    public function test_high_risk_articles_require_official_source_review_and_are_not_approved(): void
    {
        $this->artisan('content:published-blog-completion --prepare --json')->assertSuccessful();

        foreach (['alerjen-etiketleri-nasil-okunur', 'besin-degerlerinin-tahmini-olmasi', 'artan-yemekleri-saklama-ve-isitma', 'firin-yemeklerinde-sicaklik-ve-sure'] as $slug) {
            $post = BlogPost::query()->where('slug', $slug)->firstOrFail();
            $pack = (array) $post->revisions()->where('reason', 'published-blog-completion-v1')->firstOrFail()->changed_seo_fields;
            $this->assertSame('FACT_CHECK_REQUIRED', $pack['fact_check_state']);
            $this->assertFalse($pack['fact_check_approved']);
            $this->assertNotEmpty($pack['fact_check_sources'][0]['url']);
        }
    }

    public function test_admin_can_compare_private_revision_while_member_is_forbidden(): void
    {
        $this->artisan('content:published-blog-completion --prepare --json')->assertSuccessful();
        $post = BlogPost::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)->get(route('admin.blog.review', $post))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.blog.review', $post))
            ->assertOk()
            ->assertSee('Mevcut public sürüm')
            ->assertSee('Önerilen private revision')
            ->assertSee('NEEDS_MEDIA_APPROVAL');
    }
}
