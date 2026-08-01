<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentFactoryBatch;
use App\Models\ContentFactoryReview;
use App\Models\ContentTopicCluster;
use App\Models\EditorialReviewPack;
use App\Models\Recipe;
use App\Models\User;
use App\Services\ContentFactoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ControlledContentFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_factory_creates_exact_draft_catalogue_and_batches(): void
    {
        $this->seed();

        $this->assertSame(500, Recipe::query()->count());
        $this->assertSame(50, Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count());
        $this->assertSame(450, Recipe::query()->where('content_origin', 'content_factory_draft')->where('status', 'draft')->count());
        $this->assertSame(200, BlogPost::query()->count());
        $this->assertSame(20, BlogPost::query()->publiclyVisible()->count());
        $this->assertSame(180, BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'draft')->count());
        $this->assertSame(18, ContentFactoryBatch::query()->where('content_type', 'recipe')->count());
        $this->assertSame(18, ContentFactoryBatch::query()->where('content_type', 'blog')->count());
        $this->assertSame(0, ContentFactoryBatch::query()->where('content_type', 'recipe')->where('planned_items', '!=', 25)->count());
        $this->assertSame(0, ContentFactoryBatch::query()->where('content_type', 'blog')->where('planned_items', '!=', 10)->count());
        $this->assertSame(25, ContentTopicCluster::query()->count());
    }

    public function test_second_seed_is_idempotent_and_never_marks_drafts_verified_or_published(): void
    {
        $this->seed();
        app(ContentFactoryService::class)->seedAll();

        $this->assertSame(500, Recipe::query()->count());
        $this->assertSame(200, BlogPost::query()->count());
        $this->assertSame(0, Recipe::query()->where('content_origin', 'content_factory_draft')->whereIn('status', ['published', 'scheduled'])->count());
        $this->assertSame(0, Recipe::query()->where('content_origin', 'content_factory_draft')->where('verification_status', 'verified')->count());
        $this->assertSame(0, BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'published')->count());
    }

    public function test_drafts_are_hidden_from_public_pages_api_and_sitemaps(): void
    {
        $this->seed();
        $draft = Recipe::query()->where('content_origin', 'content_factory_draft')->firstOrFail();
        $post = BlogPost::query()->whereNotNull('content_factory_batch_id')->firstOrFail();

        $this->get(route('recipes.index'))->assertOk()->assertDontSee($draft->title);
        $this->getJson('/api/v1/recipes')->assertOk()->assertJsonMissing(['public_id' => $draft->public_id]);
        $this->get('/sitemaps/recipes.xml')->assertOk()->assertDontSee($draft->slug);
        $this->get('/sitemaps/blog.xml')->assertOk()->assertDontSee($post->slug);
    }

    public function test_human_approval_is_admin_only_audited_and_does_not_publish(): void
    {
        $this->seed();
        $review = ContentFactoryReview::query()->where('status', 'pending')->where('content_type', 'recipe')->where('stage', 'human_review')->firstOrFail();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)->post(route('admin.content-factory.reviews.approve', $review))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.content-factory.reviews.approve', $review))->assertStatus(422);
        $this->assertSame('pending', $review->fresh()->status);
        EditorialReviewPack::query()->where('entity_type', Recipe::class)->where('entity_public_id', $review->entity_public_id)->update(['status' => 'ready_for_final_human_approval', 'blocking_reasons' => []]);
        $this->actingAs($admin)->post(route('admin.content-factory.reviews.approve', $review))->assertRedirect();
        $review->refresh();
        $recipe = Recipe::query()->where('public_id', $review->entity_public_id)->firstOrFail();
        $this->assertSame('human_approved', $review->status);
        $this->assertSame($admin->id, $review->reviewed_by_user_id);
        $this->assertSame('human_approved', $recipe->content_factory_state);
        $this->assertSame('draft', $recipe->status);
        $this->assertNotSame('verified', $recipe->verification_status);
    }

    public function test_only_human_review_stage_can_transition_and_automated_reviews_have_no_reviewer(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $technical = ContentFactoryReview::query()->where('stage', 'technical_review')->firstOrFail();

        $this->assertNull($technical->reviewed_by_user_id);
        $this->actingAs($admin)->post(route('admin.content-factory.reviews.approve', $technical))->assertStatus(422);
        $this->assertSame('passed', $technical->fresh()->status);
    }

    public function test_factory_dashboard_is_admin_only_and_morning_brief_is_local_only(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.content-factory.index'))->assertOk()->assertSee('İçerik Fabrikası');
        $this->actingAs($member)->get(route('admin.content-factory.index'))->assertForbidden();
        $this->artisan('content:factory-audit --json')->expectsOutputToContain('"safe":true')->assertSuccessful();
        $this->artisan('content:factory-morning-brief --json')->expectsOutputToContain('"external_delivery":"disabled"')->assertSuccessful();
    }

    public function test_kill_switch_daily_limits_and_shared_hosting_fallback_are_explicit(): void
    {
        $this->seed();
        config(['content_factory.enabled' => false]);
        $this->artisan('content:factory-seed --type=recipe')->assertFailed();
        $this->assertFalse(config('content_factory.auto_publish'));
        $this->assertTrue(config('content_factory.require_human_approval'));
        $this->assertSame(25, config('content_factory.max_recipe_drafts_per_day'));
        $this->assertSame(10, config('content_factory.max_blog_drafts_per_day'));
        $cron = (string) file_get_contents(base_path('deploy/cron/content-factory-shared-hosting'));
        $this->assertStringContainsString('queue:work database --stop-when-empty --tries=2 --timeout=90 --memory=128', $cron);
    }
}
