<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Models\Recipe;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublishedRecipeReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HouseChefSeeder::class);
    }

    public function test_preview_is_read_only_and_never_changes_public_recipe_state(): void
    {
        $recipe = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->firstOrFail();
        $columns = ['title', 'status', 'verification_status', 'published_at', 'editorial_reviewed_at'];
        $before = $this->rawState($recipe, $columns);

        $this->artisan('content:published-recipe-review-batch', ['--limit' => 10, '--json' => true])
            ->assertSuccessful();

        $this->assertSame(0, ContentFactoryReview::query()->count());
        $this->assertSame(0, ContentFactoryIssue::query()->count());
        $this->assertSame($before, $this->rawState($recipe->fresh(), $columns));
    }

    public function test_private_queue_is_idempotent_and_does_not_publish_verify_or_create_media(): void
    {
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->withCount(['media', 'mediaCandidates'])
            ->orderByDesc('is_featured')
            ->orderBy('published_at')
            ->orderBy('id')
            ->limit(10)
            ->get();
        $before = $recipes->mapWithKeys(fn (Recipe $recipe): array => [$recipe->id => [
            'status' => $recipe->status,
            'verification_status' => $recipe->verification_status,
            'media_count' => $recipe->media_count,
            'candidate_count' => $recipe->media_candidates_count,
        ]])->all();

        $this->artisan('content:published-recipe-review-batch', ['--limit' => 10, '--write' => true, '--json' => true])
            ->assertSuccessful();

        $reviews = ContentFactoryReview::query()->count();
        $issues = ContentFactoryIssue::query()->count();
        $this->assertSame(40, $reviews);
        $this->assertGreaterThanOrEqual(20, $issues);
        $this->assertSame(10, ContentFactoryReview::query()->where('stage', 'media_brief')->where('status', 'blocked_identity_review')->count());

        $this->artisan('content:published-recipe-review-batch', ['--limit' => 10, '--write' => true, '--json' => true])
            ->assertSuccessful();

        $this->assertSame($reviews, ContentFactoryReview::query()->count());
        $this->assertSame($issues, ContentFactoryIssue::query()->count());
        $this->assertSame(0, ContentFactoryReview::query()->whereNotNull('reviewed_by_user_id')->count());

        foreach ($before as $id => $state) {
            $recipe = Recipe::query()->withCount(['media', 'mediaCandidates'])->findOrFail($id);
            $this->assertSame($state['status'], $recipe->status);
            $this->assertSame($state['verification_status'], $recipe->verification_status);
            $this->assertSame($state['media_count'], $recipe->media_count);
            $this->assertSame($state['candidate_count'], $recipe->media_candidates_count);
        }
    }

    public function test_pages_create_non_overlapping_private_review_batches_for_all_published_recipes(): void
    {
        for ($page = 1; $page <= 5; $page++) {
            $this->artisan('content:published-recipe-review-batch', ['--limit' => 10, '--page' => $page, '--write' => true, '--json' => true])
                ->assertSuccessful();
        }

        $this->assertSame(50, ContentFactoryReview::query()->where('stage', 'identity_review')->count());
        $this->assertSame(50, ContentFactoryReview::query()->where('stage', 'media_brief')->count());
        $this->assertSame(50, ContentFactoryIssue::query()->where('issue_type', 'recipe_step_completeness')->count());
        $this->assertSame(50, Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count());
    }

    public function test_remaining_mode_skips_completed_batch_one_and_queues_all_other_published_recipes(): void
    {
        $batchOneIds = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            ->orderByDesc('is_featured')->orderBy('published_at')->orderBy('id')->limit(10)->pluck('public_id');
        $before = Recipe::query()->where('status', 'published')->get(['id', 'status', 'verification_status', 'updated_at'])->toArray();

        $this->artisan('content:published-recipe-review-batch', ['--remaining' => true, '--write' => true, '--json' => true])
            ->expectsOutputToContain('"selected":40')
            ->assertSuccessful();

        $this->assertSame(40, ContentFactoryReview::query()->where('stage', 'identity_review')->count());
        $this->assertSame(16, ContentFactoryReview::query()->where('stage', 'identity_review')->get()->filter(fn (ContentFactoryReview $review): bool => data_get($review->findings, 'identity_state') === 'TITLE_INGREDIENT_MISMATCH')->count());
        $this->assertSame(0, ContentFactoryReview::query()->where('stage', 'identity_review')->whereIn('entity_public_id', $batchOneIds)->count());
        $this->assertSame(40, ContentFactoryReview::query()->where('stage', 'media_brief')->count());
        $this->assertSame($before, Recipe::query()->where('status', 'published')->get(['id', 'status', 'verification_status', 'updated_at'])->toArray());
    }

    public function test_draft_scope_queues_private_draft_reviews_without_making_drafts_public(): void
    {
        Recipe::query()->firstOrFail()->update(['status' => 'draft', 'title' => 'Kıbrıs Mutfağı Usulü Limonlu Nohut tava']);
        $draftsBefore = Recipe::query()->where('status', 'draft')->count();
        $publishedWithDraftStateBefore = Recipe::query()->where('status', 'published')->where('content_factory_state', 'draft_identity_review')->count();

        $this->artisan('content:published-recipe-review-batch', ['--limit' => 1, '--page' => 1, '--scope' => 'draft', '--write' => true, '--json' => true])
            ->assertSuccessful();

        $this->assertSame(1, ContentFactoryReview::query()->where('stage', 'draft_identity_review')->count());
        $this->assertDatabaseHas('content_factory_issues', ['issue_type' => 'draft_recipe_programmatic_identity', 'risk_level' => 'high']);
        $this->assertSame($draftsBefore, Recipe::query()->where('status', 'draft')->count());
        $this->assertSame($publishedWithDraftStateBefore, Recipe::query()->where('status', 'published')->where('content_factory_state', 'draft_identity_review')->count());
    }

    /** @param list<string> $columns @return array<string, mixed> */
    private function rawState(Recipe $recipe, array $columns): array
    {
        return collect($columns)->mapWithKeys(fn (string $column): array => [$column => $recipe->getRawOriginal($column)])->all();
    }
}
