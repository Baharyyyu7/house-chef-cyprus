<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentFactoryEvidence;
use App\Models\EditorialReviewBatch;
use App\Models\EditorialReviewPack;
use App\Models\Recipe;
use App\Models\SourceResearchQueueItem;
use App\Models\User;
use App\Services\EditorialOperationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class EditorialOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_creates_bounded_priority_batches_source_queue_and_all_draft_packs_idempotently(): void
    {
        $this->seed();
        $service = app(EditorialOperationsService::class);
        $first = $service->refresh();
        $firstPublicIds = EditorialReviewBatch::query()->pluck('public_id', 'planning_key')->all();
        $second = $service->refresh();

        $this->assertSame(2340, $first['issues']);
        $this->assertSame(48, $first['batches']);
        $this->assertSame(2340, $first['source_queue']);
        $this->assertSame(450, $first['recipe_packs']);
        $this->assertSame(180, $first['blog_packs']);
        $this->assertSame($first, $second);
        $this->assertSame($firstPublicIds, EditorialReviewBatch::query()->pluck('public_id', 'planning_key')->all());
        $this->assertSame(0, EditorialReviewBatch::query()->where('total_issues', '>', 50)->count());
        $this->assertSame(2340, SourceResearchQueueItem::query()->count());
        $this->assertSame(630, EditorialReviewPack::query()->count());
        $this->assertSame(0, EditorialReviewPack::query()->where('status', 'ready_for_final_human_approval')->count());
    }

    public function test_review_pack_section_actions_are_audited_without_publishing_or_verifying_the_draft(): void
    {
        $this->seed();
        $editor = User::factory()->create(['role' => 'content_editor', 'email_verified_at' => now()]);
        $pack = EditorialReviewPack::query()->where('content_type', 'recipe')->firstOrFail();
        $recipe = Recipe::query()->where('public_id', $pack->entity_public_id)->firstOrFail();

        $this->actingAs($editor)->post(route('admin.editorial-operations.packs.section', $pack), [
            'section' => 'editorial', 'action' => 'request_revision', 'note' => 'Ölçülerin yayın öncesi editoryal kontrolü gerekiyor.',
        ])->assertRedirect();

        $this->assertDatabaseHas('editorial_review_sections', ['editorial_review_pack_id' => $pack->id, 'section_key' => 'editorial', 'status' => 'request_revision', 'decided_by_user_id' => $editor->id]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $editor->id, 'action' => 'editorial_operations.section.request_revision']);
        $this->assertSame('draft', $recipe->fresh()->status);
        $this->assertSame('needs_review', $recipe->fresh()->verification_status);
    }

    public function test_editorial_operations_authorization_keeps_members_out_and_limits_media_reviewers(): void
    {
        $this->seed();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $mediaReviewer = User::factory()->create(['role' => 'media_reviewer', 'email_verified_at' => now()]);
        $pack = EditorialReviewPack::query()->where('content_type', 'recipe')->firstOrFail();

        $this->actingAs($member)->get(route('admin.editorial-operations.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.editorial-operations.packs.show', $pack))->assertForbidden();
        $this->assertTrue(Gate::forUser($mediaReviewer)->allows('view-editorial-media'));
        $this->assertTrue(Gate::forUser($mediaReviewer)->allows('review-editorial-pack', [$pack, 'media', 'approve_section']));
        $this->assertFalse(Gate::forUser($mediaReviewer)->allows('review-editorial-pack', [$pack, 'fact_check', 'approve_section']));
    }

    public function test_editorial_operations_brief_is_stable_and_never_claims_auto_publish(): void
    {
        $this->seed();

        $this->artisan('content:editorial-operations-brief --json')
            ->expectsOutputToContain('"total_fact_check_issues":2340')
            ->assertSuccessful();

        $this->assertSame(0, EditorialReviewPack::query()->where('status', 'ready_for_final_human_approval')->count());
        $this->assertSame(0, Recipe::query()->where('content_origin', 'content_factory_draft')->where('status', 'published')->count());
    }

    public function test_only_a_real_reviewer_source_reference_can_mark_an_issue_evidence_ready(): void
    {
        $this->seed();
        $reviewer = User::factory()->create(['role' => 'fact_check_reviewer', 'email_verified_at' => now()]);
        $item = SourceResearchQueueItem::query()->where('reviewer_status', 'source_required')->firstOrFail();
        $issue = $item->issue;

        // A Phase 3 triage pack is helpful context, but never a source citation.
        $issue->update(['evidence_pack' => ['human_decision_required' => true, 'source_reference_ready' => false]]);
        app(EditorialOperationsService::class)->syncSourceQueue();
        $this->assertSame('source_required', $item->fresh()->reviewer_status);

        $this->actingAs($reviewer)->put(route('admin.editorial-operations.sources.update', $item), [
            'candidate_source_title' => 'Kıbrıs Gıda Güvenliği Rehberi',
            'publisher' => 'Resmî Gıda Kurumu',
            'url' => 'https://example.test/food-safety-guidance',
            'publication_date' => '2026-01-15',
            'source_type' => 'resmi_kurum',
            'evidence_summary' => 'Bu kısa özgün özet, iddiayı kaynak metnini kopyalamadan güvenli biçimde referanslar.',
            'supported_claim' => 'İddia için resmî kaynak referansı kaydedildi.',
            'confidence_score' => 90,
            'freshness_requirement' => '12 ay veya daha güncel',
        ])->assertRedirect(route('admin.editorial-operations.sources.index'));

        $this->assertDatabaseHas('content_factory_evidences', ['content_factory_issue_id' => $issue->id, 'url' => 'https://example.test/food-safety-guidance', 'review_status' => 'evidence_ready']);
        $this->assertDatabaseHas('source_research_queue_items', ['id' => $item->id, 'reviewer_status' => 'evidence_ready']);
        $this->assertTrue(data_get($issue->fresh()->evidence_pack, 'source_reference_ready'));
        $this->assertSame(1, ContentFactoryEvidence::query()->where('content_factory_issue_id', $issue->id)->count());
        $this->assertSame('draft', Recipe::query()->where('content_origin', 'content_factory_draft')->firstOrFail()->status);
    }

    public function test_members_cannot_open_or_save_source_evidence(): void
    {
        $this->seed();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $item = SourceResearchQueueItem::query()->firstOrFail();

        $this->actingAs($member)->get(route('admin.editorial-operations.sources.show', $item))->assertForbidden();
        $this->actingAs($member)->put(route('admin.editorial-operations.sources.update', $item), [])->assertForbidden();
    }

    public function test_only_admin_can_assign_real_editorial_reviewers(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $reviewer = User::factory()->create(['role' => 'fact_check_reviewer', 'email_verified_at' => now()]);
        $batch = EditorialReviewBatch::query()->firstOrFail();
        $source = SourceResearchQueueItem::query()->firstOrFail();
        $pack = EditorialReviewPack::query()->firstOrFail();

        $this->actingAs($member)->post(route('admin.editorial-operations.batches.assign', $batch), ['reviewer_public_id' => $reviewer->public_id])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.editorial-operations.batches.assign', $batch), ['reviewer_public_id' => $reviewer->public_id])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.editorial-operations.sources.assign', $source), ['reviewer_public_id' => $reviewer->public_id])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.editorial-operations.packs.assign', $pack), ['reviewer_public_id' => $reviewer->public_id])->assertRedirect();

        $this->assertSame($reviewer->id, $batch->fresh()->assigned_reviewer_id);
        $this->assertSame($reviewer->id, $source->fresh()->assigned_reviewer_id);
        $this->assertSame($reviewer->id, $pack->fresh()->assigned_reviewer_id);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'editorial_operations.pack_assigned']);
    }
}
