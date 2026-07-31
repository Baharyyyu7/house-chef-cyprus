<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\ContentFactoryBatch;
use App\Models\ContentFactoryBulkRun;
use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Models\ContentFactoryRevision;
use App\Models\ContentTopicCluster;
use App\Models\Recipe;
use App\Services\ContentFactoryBatchReadinessService;
use App\Services\ContentFactoryDuplicateGroupService;
use App\Services\ContentFactoryEnrichmentService;
use App\Services\ContentFactoryFactCheckService;
use App\Services\ContentFactoryRevisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContentFactoryController extends Controller
{
    public function index(Request $request, ContentFactoryEnrichmentService $enrichment): View
    {
        $this->admin();
        $reviews = ContentFactoryReview::query()->with('batch')->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))->when($request->filled('stage'), fn ($query) => $query->where('stage', $request->string('stage')->toString()))->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))->latest()->paginate(25)->withQueryString();

        return view('admin.content-factory.index', [
            'recipeDrafts' => Recipe::query()->where('content_origin', 'content_factory_draft')->count(),
            'verifiedRecipes' => Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count(),
            'blogDrafts' => BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'draft')->count(),
            'publishedBlogs' => BlogPost::query()->publiclyVisible()->count(),
            'recipeBatches' => ContentFactoryBatch::query()->where('content_type', 'recipe')->count(),
            'blogBatches' => ContentFactoryBatch::query()->where('content_type', 'blog')->count(),
            'clusters' => ContentTopicCluster::query()->count(),
            'waiting' => ContentFactoryReview::query()->where('status', 'pending')->count(),
            'failed' => ContentFactoryReview::query()->whereIn('status', ['failed', 'rejected'])->count(),
            'metrics' => $enrichment->audit(),
            'factCheckIssues' => ContentFactoryIssue::query()->where('status', 'pending')->count(),
            'reviews' => $reviews,
        ]);
    }

    public function show(ContentFactoryReview $review): View
    {
        $this->admin();
        $entity = $this->entity($review);
        abort_if($entity === null, 404);

        return view('admin.content-factory.show', [
            'review' => $review->load('batch', 'reviewer'),
            'entity' => $entity,
            'reviews' => ContentFactoryReview::query()->where('entity_type', $review->entity_type)->where('entity_public_id', $review->entity_public_id)->get(),
            'issues' => ContentFactoryIssue::query()->where('entity_type', $review->entity_type)->where('entity_public_id', $review->entity_public_id)->orderByDesc('risk_level')->get(),
        ]);
    }

    public function factChecks(Request $request, ContentFactoryFactCheckService $factChecks): View
    {
        $this->admin();
        $issues = ContentFactoryIssue::query()
            ->with(['batch', 'reviewer'])
            ->when($request->filled('risk'), fn ($query) => $query->where('risk_domain', $request->string('risk')->toString()))
            ->when($request->filled('severity'), fn ($query) => $query->where('risk_level', $request->string('severity')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByDesc('priority_score')->paginate(30)->withQueryString();

        return view('admin.content-factory.fact-checks', ['issues' => $issues, 'metrics' => $factChecks->inventory()]);
    }

    public function factCheckShow(ContentFactoryIssue $issue, ContentFactoryFactCheckService $factChecks): View
    {
        $this->admin();

        $previous = ContentFactoryIssue::query()->where('priority_score', '>', $issue->priority_score)->orderBy('priority_score')->first();
        $next = ContentFactoryIssue::query()->where('priority_score', '<', $issue->priority_score)->orderByDesc('priority_score')->first();

        return view('admin.content-factory.fact-check-show', ['issue' => $issue->load(['batch', 'reviewer']), 'evidencePack' => $factChecks->evidencePack($issue), 'revision' => $factChecks->proposeRevision($issue), 'previous' => $previous, 'next' => $next, 'auditLogs' => AuditLog::query()->where('auditable_type', ContentFactoryIssue::class)->where('auditable_id', $issue->id)->latest()->limit(20)->get()]);
    }

    public function factCheckAction(Request $request, ContentFactoryIssue $issue): RedirectResponse
    {
        $data = $request->validate(['action' => ['required', 'in:approve,reject,request_more_source,not_applicable,defer,reopen,note'], 'note' => ['nullable', 'string', 'max:3000'], 'version' => ['required', 'date']]);
        $this->factCheckAuthority($issue, $data['action']);
        if ($issue->updated_at?->toIso8601String() !== $data['version']) {
            return back()->withErrors(['issue' => 'Kayıt başka bir inceleyen tarafından güncellendi. Sayfayı yenileyin.']);
        }
        $status = match ($data['action']) {
            'approve' => 'approved_correction', 'reject' => 'rejected', 'request_more_source' => 'source_required', 'not_applicable' => 'not_applicable', 'defer' => 'deferred', 'reopen' => 'reopened', default => $issue->status
        };
        $issue->update(['status' => $status, 'reviewed_by_user_id' => auth()->id(), 'resolution_note' => $data['note'] ?? $issue->resolution_note, 'resolved_at' => in_array($status, ['approved_correction', 'rejected', 'not_applicable'], true) ? now() : null]);
        AuditLog::query()->create(['user_id' => auth()->id(), 'action' => 'content_factory.fact_check.'.$data['action'], 'auditable_type' => ContentFactoryIssue::class, 'auditable_id' => $issue->id, 'metadata' => ['issue_public_id' => $issue->public_id, 'risk_level' => $issue->risk_level, 'auto_publish' => false]]);

        return back()->with('status', 'İnsan kararı kaydedildi; içerik taslak olarak kaldı.');
    }

    public function revisionAction(Request $request, ContentFactoryRevision $revision, ContentFactoryRevisionService $revisions): RedirectResponse
    {
        $action = $request->validate(['action' => ['required', 'in:approve,apply,rollback,reopen']])['action'];
        $actor = $request->user();
        abort_unless($actor !== null, 403);
        match ($action) {
            'approve' => $revisions->approve($revision, $actor),
            'apply' => $revisions->apply($revision, $actor),
            'rollback' => $revisions->rollback($revision, $actor),
            'reopen' => $revisions->reopen($revision, $actor),
            default => abort(422),
        };

        return back()->with('status', 'Revision işlemi kaydedildi; yayın/doğrulama durumu değiştirilmedi.');
    }

    public function duplicateGroups(ContentFactoryDuplicateGroupService $groups): View
    {
        abort_unless(auth()->user()?->can('run-duplicate-dry-run'), 403);
        $items = ContentFactoryIssue::query()->whereNotNull('group_key')->orderBy('group_key')->get()->groupBy('group_key')->map(function ($issues, $key): array {
            $master = $issues->sortByDesc('priority_score')->first();

            return ['key' => $key, 'master' => $master, 'children' => $issues->count(), 'recipes' => $issues->where('entity_type', Recipe::class)->count(), 'blogs' => $issues->where('entity_type', BlogPost::class)->count(), 'conflicts' => $issues->filter(fn (ContentFactoryIssue $issue) => (bool) data_get($issue->evidence_pack, 'context_conflict'))->count()];
        })->values();

        return view('admin.content-factory.duplicate-groups', ['groups' => $items]);
    }

    public function duplicateGroup(string $groupKey): View
    {
        abort_unless(auth()->user()?->can('run-duplicate-bulk'), 403);
        $issues = ContentFactoryIssue::query()->where('group_key', $groupKey)->with('batch')->orderByDesc('priority_score')->get();
        abort_if($issues->isEmpty(), 404);

        return view('admin.content-factory.duplicate-group-show', ['groupKey' => $groupKey, 'master' => $issues->first(), 'issues' => $issues, 'latestRun' => ContentFactoryBulkRun::query()->where('group_key', $groupKey)->latest()->first()]);
    }

    public function duplicateDryRun(string $groupKey, ContentFactoryDuplicateGroupService $groups): RedirectResponse
    {
        abort_unless(auth()->user()?->can('rescan-content-readiness'), 403);
        $run = $groups->dryRun($groupKey, auth()->user());

        return redirect()->route('admin.content-factory.duplicate-groups.show', $groupKey)->with('status', "Dry-run {$run->public_id} oluşturuldu; seçili kayıtlar için 30 dakika geçerlidir.");
    }

    public function duplicatePropose(Request $request, ContentFactoryBulkRun $run, ContentFactoryDuplicateGroupService $groups): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['issues' => ['required', 'array', 'min:1', 'max:100'], 'issues.*' => ['string', 'size:26']]);
        $result = $groups->proposePartial($run, $data['issues'], auth()->user());

        return back()->with('status', "{$result['proposed_revisions']} ayrı revision önerisi oluşturuldu; yayınlama yapılmadı.");
    }

    public function readiness(ContentFactoryBatchReadinessService $readiness): View
    {
        $this->admin();
        $batches = ContentFactoryBatch::query()->orderBy('content_type')->orderBy('sequence')->get()->map(fn (ContentFactoryBatch $batch) => $readiness->batch($batch));

        return view('admin.content-factory.readiness', ['batches' => $batches]);
    }

    public function readinessShow(ContentFactoryBatch $batch, ContentFactoryBatchReadinessService $readiness): View
    {
        $this->admin();

        return view('admin.content-factory.readiness-show', ['batch' => $batch, 'readiness' => $readiness->batch($batch), 'issues' => ContentFactoryIssue::query()->where('content_factory_batch_id', $batch->id)->orderByDesc('priority_score')->paginate(30)]);
    }

    public function readinessRescan(ContentFactoryBatchReadinessService $readiness): RedirectResponse
    {
        $this->admin();
        $readiness->scan();

        return back()->with('status', 'Batch readiness yeniden hesaplandı; sonuç insan editoryal onayı değildir.');
    }

    public function approve(ContentFactoryReview $review): RedirectResponse
    {
        $this->admin();
        abort_unless($review->stage === 'human_review', 422, 'Bu aşama insan onayı için uygun değildir.');
        $entity = $this->entity($review);
        if ($entity === null) {
            return back()->with('error', 'İlgili taslak bulunamadı; güvenli olarak onaylanmadı.');
        }
        if ($review->status !== 'human_approved') {
            $review->update(['status' => 'human_approved', 'reviewed_by_user_id' => auth()->id(), 'reviewed_at' => now()]);
            $entity->forceFill(['content_factory_state' => 'human_approved', 'human_approved_by_user_id' => auth()->id(), 'human_approved_at' => now()])->save();
            AuditLog::query()->create(['user_id' => auth()->id(), 'action' => 'content_factory.human_approved', 'auditable_type' => $review->entity_type, 'auditable_id' => $entity->id, 'metadata' => ['review_public_id' => $review->public_id, 'entity_public_id' => $review->entity_public_id, 'auto_publish' => false]]);
        }

        return back()->with('status', 'İnsan onayı kaydedildi. İçerik hâlâ taslak durumundadır; yayınlanmadı.');
    }

    public function reject(ContentFactoryReview $review): RedirectResponse
    {
        $this->admin();
        abort_unless($review->stage === 'human_review', 422, 'Bu aşama insan reddi için uygun değildir.');
        $review->update(['status' => 'rejected', 'editorial_comment' => request()->string('comment')->trim()->toString() ?: null, 'reviewed_by_user_id' => auth()->id(), 'reviewed_at' => now()]);
        if ($entity = $this->entity($review)) {
            $entity->forceFill(['content_factory_state' => 'rejected', 'editorial_status' => 'editorial_failed'])->save();
            AuditLog::query()->create(['user_id' => auth()->id(), 'action' => 'content_factory.rejected', 'auditable_type' => $review->entity_type, 'auditable_id' => $entity->id, 'metadata' => ['review_public_id' => $review->public_id, 'entity_public_id' => $review->entity_public_id]]);
        }

        return back()->with('status', 'Taslak reddedildi; otomatik yayın yapılmadı.');
    }

    public function requestRevision(Request $request, ContentFactoryReview $review): RedirectResponse
    {
        $this->admin();
        abort_unless($review->stage === 'human_review', 422, 'Bu aşama düzeltme istemi için uygun değildir.');
        $data = $request->validate(['comment' => ['required', 'string', 'min:10', 'max:3000']]);
        $review->update(['status' => 'revision_requested', 'editorial_comment' => $data['comment'], 'reviewed_by_user_id' => auth()->id(), 'reviewed_at' => now()]);
        if ($entity = $this->entity($review)) {
            $entity->forceFill(['content_factory_state' => 'revision_requested', 'editorial_status' => 'revision_requested'])->save();
            $this->audit('content_factory.revision_requested', $review, $entity);
        }

        return back()->with('status', 'Düzeltme istemi kaydedildi. İçerik taslak olarak kaldı.');
    }

    private function entity(ContentFactoryReview $review): Recipe|BlogPost|null
    {
        return match ($review->entity_type) {
            Recipe::class => Recipe::query()->where('public_id', $review->entity_public_id)->first(),
            BlogPost::class => BlogPost::query()->where('public_id', $review->entity_public_id)->first(),
            default => null,
        };
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->can('access-content-factory'), 403);
    }

    private function factCheckAuthority(ContentFactoryIssue $issue, string $action): void
    {
        abort_unless(auth()->user()?->can('review-fact-check', [$issue, $action]), 403);
    }

    private function audit(string $action, ContentFactoryReview $review, Recipe|BlogPost $entity): void
    {
        AuditLog::query()->create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $review->entity_type, 'auditable_id' => $entity->id, 'metadata' => ['review_public_id' => $review->public_id, 'entity_public_id' => $review->entity_public_id, 'auto_publish' => false]]);
    }
}
