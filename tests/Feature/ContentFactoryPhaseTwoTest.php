<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Models\Recipe;
use App\Models\User;
use App\Services\ContentFactoryEnrichmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentFactoryPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_two_enriches_every_draft_without_public_leaks(): void
    {
        $this->seed();

        $this->assertSame(450, Recipe::query()->where('content_origin', 'content_factory_draft')->where('content_factory_readiness_score', '>=', 70)->count());
        $this->assertSame(180, BlogPost::query()->whereNotNull('content_factory_batch_id')->where('content_factory_readiness_score', '>=', 70)->count());
        $this->assertSame(2340, ContentFactoryIssue::query()->where('status', 'pending')->count());
        $this->assertSame(0, Recipe::query()->where('content_origin', 'content_factory_draft')->whereIn('status', ['published', 'scheduled'])->count());
        $this->assertSame(0, BlogPost::query()->whereNotNull('content_factory_batch_id')->whereIn('status', ['published', 'scheduled'])->count());
        $this->assertSame(0, ContentFactoryReview::query()->whereNotNull('reviewed_by_user_id')->whereIn('stage', ['technical_review', 'originality_review', 'seo_review'])->count());
    }

    public function test_phase_two_enrichment_is_idempotent_and_auditable(): void
    {
        $this->seed();
        $before = ContentFactoryIssue::query()->count();

        $result = app(ContentFactoryEnrichmentService::class)->enrichAll();
        $audit = app(ContentFactoryEnrichmentService::class)->audit();

        $this->assertSame(['recipes' => 450, 'blogs' => 180], $result);
        $this->assertSame($before, ContentFactoryIssue::query()->count());
        $this->assertSame(450, $audit['recipe_ready']);
        $this->assertSame(180, $audit['blog_ready']);
        $this->artisan('content:factory-enrich --type=all --json')->expectsOutputToContain('"human_approval_required":true')->assertSuccessful();
    }

    public function test_admin_can_open_detail_and_request_revision_but_member_cannot(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $review = ContentFactoryReview::query()->where('stage', 'human_review')->where('status', 'pending')->firstOrFail();

        $this->actingAs($member)->get(route('admin.content-factory.reviews.show', $review))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.content-factory.reviews.show', $review))->assertOk()->assertSee('İnsan kararı');
        $this->actingAs($admin)->post(route('admin.content-factory.reviews.request-revision', $review), ['comment' => 'Lütfen saklama notlarını birincil kaynakla doğrulayın.'])->assertRedirect();

        $this->assertSame('revision_requested', $review->fresh()->status);
        $this->assertSame($admin->id, $review->fresh()->reviewed_by_user_id);
    }
}
