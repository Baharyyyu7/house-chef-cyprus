<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryRevision;
use App\Models\Recipe;
use App\Models\User;
use App\Services\ContentFactoryDuplicateGroupService;
use App\Services\ContentFactoryFactCheckService;
use App\Services\ContentFactoryRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ContentFactoryFactCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_fact_check_inventory_is_triaged_without_automatic_resolution(): void
    {
        $this->seed();
        $inventory = app(ContentFactoryFactCheckService::class)->inventory();

        $this->assertSame(2340, $inventory['total']);
        $this->assertSame(2340, $inventory['pending']);
        $this->assertSame(0, $inventory['deterministic_resolved']);
        $this->assertSame(900, $inventory['critical']);
        $this->assertSame(1080, $inventory['high']);
        $this->assertSame(0, ContentFactoryIssue::query()->where('resolution_mode', '!=', 'human_required')->count());
    }

    public function test_triage_is_idempotent_and_provides_human_evidence_and_revision_proposal(): void
    {
        $this->seed();
        $service = app(ContentFactoryFactCheckService::class);
        $issue = ContentFactoryIssue::query()->where('risk_level', 'critical')->firstOrFail();
        $pack = $service->evidencePack($issue);
        $revision = $service->proposeRevision($issue);
        $service->triage();

        $this->assertTrue($pack['human_decision_required']);
        $this->assertSame('proposed', $revision->status);
        $this->assertSame(1, ContentFactoryRevision::query()->where('content_factory_issue_id', $issue->id)->count());
        $this->assertSame(2340, ContentFactoryIssue::query()->count());
        $this->artisan('content:fact-check-triage --json')->expectsOutputToContain('"human_required":2340')->assertSuccessful();
    }

    public function test_an_approved_revision_applies_and_rolls_back_without_publishing_content(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('content_origin', 'content_factory_draft')->firstOrFail();
        $issue = ContentFactoryIssue::query()->where('entity_public_id', $recipe->public_id)->firstOrFail();
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $before = $recipe->excerpt;
        $revision = ContentFactoryRevision::query()->create([
            'content_factory_issue_id' => $issue->id, 'issue_public_id' => $issue->public_id,
            'entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'field_name' => 'excerpt',
            'before_value' => ['excerpt' => $before], 'proposed_value' => ['excerpt' => 'İnsan onaylı güvenli taslak düzeltmesi.'],
            'reason' => 'Test revizyonu', 'source_evidence' => [], 'evidence_reference_ids' => [],
            'before_value_hash' => hash('sha256', json_encode($before)), 'current_value_hash' => hash('sha256', json_encode($before)),
            'status' => 'proposed',
        ]);
        $service = app(ContentFactoryRevisionService::class);
        $service->approve($revision, $reviewer);
        $service->apply($revision->refresh(), $reviewer);

        $this->assertSame('İnsan onaylı güvenli taslak düzeltmesi.', $recipe->refresh()->excerpt);
        $this->assertSame('draft', $recipe->status);
        $service->rollback($revision->refresh(), $reviewer);
        $this->assertSame($before, $recipe->refresh()->excerpt);
        $this->assertSame('rolled_back', $revision->refresh()->status);
    }

    public function test_revision_rejects_conflicts_and_self_approval(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('content_origin', 'content_factory_draft')->firstOrFail();
        $issue = ContentFactoryIssue::query()->where('entity_public_id', $recipe->public_id)->firstOrFail();
        $author = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $reviewer = User::factory()->create(['role' => 'fact_check_reviewer', 'email_verified_at' => now()]);
        $revision = ContentFactoryRevision::query()->create([
            'content_factory_issue_id' => $issue->id, 'issue_public_id' => $issue->public_id, 'entity_type' => Recipe::class,
            'entity_public_id' => $recipe->public_id, 'field_name' => 'excerpt', 'before_value' => ['excerpt' => $recipe->excerpt],
            'proposed_value' => ['excerpt' => 'Yeni metin'], 'reason' => 'Test', 'source_evidence' => [], 'evidence_reference_ids' => [],
            'created_by_user_id' => $author->id, 'before_value_hash' => hash('sha256', json_encode($recipe->excerpt)),
            'current_value_hash' => hash('sha256', json_encode($recipe->excerpt)), 'status' => 'proposed',
        ]);
        $service = app(ContentFactoryRevisionService::class);
        $this->expectException(ValidationException::class);
        $service->approve($revision, $author);
        $service->approve($revision, $reviewer);
        $recipe->update(['excerpt' => 'Başka bir insan değişikliği']);
        $service->apply($revision->refresh(), $reviewer);
    }

    public function test_duplicate_group_requires_a_fresh_dry_run_before_partial_proposals(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $groupKey = ContentFactoryIssue::query()->whereNotNull('group_key')->value('group_key');
        $issues = ContentFactoryIssue::query()->where('group_key', $groupKey)->limit(2)->get();
        $service = app(ContentFactoryDuplicateGroupService::class);
        $run = $service->dryRun((string) $groupKey, $admin);
        $result = $service->proposePartial($run, $issues->pluck('public_id')->all(), $admin);

        $this->assertSame(2, $result['proposed_revisions']);
        $this->assertSame(2, ContentFactoryRevision::query()->whereIn('content_factory_issue_id', $issues->pluck('id'))->count());
        $this->expectException(ValidationException::class);
        $service->proposePartial($run->refresh(), $issues->pluck('public_id')->all(), $admin);
    }
}
