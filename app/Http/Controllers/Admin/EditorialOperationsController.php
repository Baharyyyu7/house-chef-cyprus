<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContentFactoryEvidence;
use App\Models\EditorialReviewBatch;
use App\Models\EditorialReviewPack;
use App\Models\SourceResearchQueueItem;
use App\Models\User;
use App\Services\EditorialOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class EditorialOperationsController extends Controller
{
    public function index(EditorialOperationsService $operations): View
    {
        $this->access();

        return view('admin.editorial-operations.index', ['metrics' => $operations->metrics(), 'workload' => $operations->workload()]);
    }

    public function batches(Request $request): View
    {
        $this->access();
        $batches = EditorialReviewBatch::query()->withCount('issues')->with('reviewer')
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByDesc('priority_score')->paginate(25)->withQueryString();

        return view('admin.editorial-operations.batches', compact('batches'));
    }

    public function batch(EditorialReviewBatch $batch): View
    {
        $this->access();
        $batch->load(['issues' => fn ($query) => $query->orderByDesc('priority_score'), 'reviewer']);

        return view('admin.editorial-operations.batch-show', compact('batch'));
    }

    public function assignBatch(Request $request, EditorialReviewBatch $batch): RedirectResponse
    {
        abort_unless($request->user()?->can('assign-editorial-work'), 403);
        $reviewer = $this->reviewerFromRequest($request);
        $batch->update(['assigned_reviewer_id' => $reviewer?->id, 'status' => $reviewer ? 'in_review' : 'planned', 'started_at' => $reviewer ? ($batch->started_at ?? now()) : null]);
        $this->audit('editorial_operations.batch_assigned', $batch, ['reviewer_public_id' => $reviewer?->public_id]);

        return back()->with('status', $reviewer ? 'Reviewer atandı; otomatik onay/yayın yapılmadı.' : 'Reviewer ataması kaldırıldı.');
    }

    public function sources(Request $request): View
    {
        $this->access();
        $items = SourceResearchQueueItem::query()->with('issue')
            ->when($request->filled('risk'), fn ($query) => $query->where('risk_level', $request->string('risk')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('reviewer_status', $request->string('status')->toString()))
            ->orderByRaw("case risk_level when 'critical' then 4 when 'high' then 3 when 'medium' then 2 else 1 end desc")
            ->paginate(30)->withQueryString();

        return view('admin.editorial-operations.sources', compact('items'));
    }

    public function source(SourceResearchQueueItem $item): View
    {
        $this->access();
        abort_unless(request()->user()?->can('manage-source-research'), 403);

        return view('admin.editorial-operations.source-show', ['item' => $item->load('issue')]);
    }

    public function saveSource(Request $request, SourceResearchQueueItem $item): RedirectResponse
    {
        abort_unless($request->user()?->can('manage-source-research'), 403);
        $data = $request->validate([
            'candidate_source_title' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:180'],
            'url' => ['required', 'url', 'max:2048'],
            'publication_date' => ['nullable', 'date'],
            'source_type' => ['required', 'string', 'max:60'],
            'evidence_summary' => ['required', 'string', 'min:20', 'max:3000'],
            'supported_claim' => ['required', 'string', 'max:1000'],
            'conflicting_evidence' => ['nullable', 'string', 'max:2000'],
            'confidence_score' => ['required', 'integer', 'min:0', 'max:100'],
            'freshness_requirement' => ['nullable', 'string', 'max:80'],
        ]);
        $item->update([...$data, 'accessed_at' => now(), 'reviewer_status' => 'evidence_ready']);
        $issue = $item->issue;
        $evidence = ContentFactoryEvidence::query()->firstOrNew(['content_factory_issue_id' => $issue->id, 'url' => $data['url']]);
        if (! $evidence->exists) {
            $evidence->public_id = (string) Str::ulid();
        }
        $evidence->fill([
            'title' => $data['candidate_source_title'], 'publisher' => $data['publisher'], 'published_at' => $data['publication_date'] ?? null,
            'accessed_at' => now(), 'source_type' => $data['source_type'], 'trust_level' => $issue->risk_level,
            'supported_claim' => $data['supported_claim'], 'confidence_score' => $data['confidence_score'], 'conflicting_evidence' => $data['conflicting_evidence'] ?? null,
            'freshness_requirement' => $data['freshness_requirement'] ?? $item->freshness_requirement, 'review_status' => 'evidence_ready',
            'summary' => $data['evidence_summary'], 'usage_note' => 'Kısa özgün özet ve referans; kaynak metni kopyalanmaz.',
        ]);
        $evidence->save();
        $issue->update(['evidence_pack' => ['source_queue_public_id' => $item->public_id, 'source_reference_ready' => true, 'human_decision_required' => true, 'auto_publish' => false]]);
        $this->audit('editorial_operations.source_evidence_saved', null, ['source_queue_public_id' => $item->public_id, 'issue_public_id' => $issue->public_id]);

        return redirect()->route('admin.editorial-operations.sources.index')->with('status', 'Kaynak referansı kaydedildi; insan fact-check kararı hâlâ zorunludur.');
    }

    public function assignSource(Request $request, SourceResearchQueueItem $item): RedirectResponse
    {
        abort_unless($request->user()?->can('assign-editorial-work'), 403);
        $reviewer = $this->reviewerFromRequest($request);
        $item->update(['assigned_reviewer_id' => $reviewer?->id]);
        $this->audit('editorial_operations.source_assigned', null, ['source_queue_public_id' => $item->public_id, 'reviewer_public_id' => $reviewer?->public_id]);

        return back()->with('status', $reviewer ? 'Kaynak araştırması gerçek reviewer’a atandı.' : 'Kaynak araştırması ataması kaldırıldı.');
    }

    public function packs(Request $request): View
    {
        $this->access();
        $packs = EditorialReviewPack::query()->with(['batch', 'reviewer'])
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('content_type')->orderBy('id')->paginate(30)->withQueryString();

        return view('admin.editorial-operations.packs', compact('packs'));
    }

    public function pack(EditorialReviewPack $pack): View
    {
        $this->access();
        abort_unless(request()->user()?->can('review-editorial-pack', [$pack, 'overview', 'view']), 403);
        $pack->load(['batch', 'sections', 'reviewer']);

        return view('admin.editorial-operations.pack-show', compact('pack'));
    }

    public function section(Request $request, EditorialReviewPack $pack, EditorialOperationsService $operations): RedirectResponse
    {
        $data = $request->validate(['section' => ['required', 'in:overview,editorial,fact_check,evidence,seo,media,storage'], 'action' => ['required', 'in:approve_section,reject_section,request_revision,request_source,not_applicable,defer,note'], 'note' => ['nullable', 'string', 'max:3000']]);
        $operations->decideSection($pack, $data['section'], $data['action'], $data['note'] ?? null, $request->user());

        return back()->with('status', 'Bölüm kararı kaydedildi; final insan onayı ve yayın ayrı işlemlerdir.');
    }

    public function assignPack(Request $request, EditorialReviewPack $pack): RedirectResponse
    {
        abort_unless($request->user()?->can('assign-editorial-work'), 403);
        $reviewer = $this->reviewerFromRequest($request);
        $pack->update(['assigned_reviewer_id' => $reviewer?->id]);
        $this->audit('editorial_operations.pack_assigned', null, ['pack_public_id' => $pack->public_id, 'reviewer_public_id' => $reviewer?->public_id]);

        return back()->with('status', $reviewer ? 'Review paketi gerçek reviewer’a atandı.' : 'Review paketi ataması kaldırıldı.');
    }

    public function finalApprovals(Request $request): View
    {
        $this->access();
        $packs = EditorialReviewPack::query()->where('status', 'ready_for_final_human_approval')->with('batch')->paginate(30)->withQueryString();

        return view('admin.editorial-operations.final-approvals', compact('packs'));
    }

    public function media(Request $request): View
    {
        abort_unless($request->user()?->can('view-editorial-media'), 403);
        $packs = EditorialReviewPack::query()->where('content_type', 'recipe')->orderBy('id')->paginate(30)->withQueryString();

        return view('admin.editorial-operations.media', compact('packs'));
    }

    public function workload(EditorialOperationsService $operations): View
    {
        $this->access();

        return view('admin.editorial-operations.workload', ['workload' => $operations->workload()]);
    }

    public function refresh(EditorialOperationsService $operations): RedirectResponse
    {
        abort_unless(request()->user()?->can('assign-editorial-work'), 403);
        $result = $operations->refresh();
        $this->audit('editorial_operations.refreshed', null, $result);

        return back()->with('status', "Operasyon kuyruğu yenilendi: {$result['batches']} batch, {$result['source_queue']} kaynak kaydı. Otomatik onay/yayın yapılmadı.");
    }

    private function access(): void
    {
        abort_unless(auth()->user()?->can('access-editorial-operations'), 403);
    }

    private function reviewerFromRequest(Request $request): ?User
    {
        $data = $request->validate(['reviewer_public_id' => ['nullable', 'string', 'size:26']]);
        $reviewer = $data['reviewer_public_id'] ? User::query()->where('public_id', $data['reviewer_public_id'])->firstOrFail() : null;
        if ($reviewer !== null && (! $reviewer->isContentFactoryStaff() || $reviewer->isSystemReviewer())) {
            abort(422, 'Yalnız gerçek yetkili reviewer atanabilir.');
        }

        return $reviewer;
    }

    /** @param array<string,mixed> $metadata */
    private function audit(string $action, ?EditorialReviewBatch $batch, array $metadata): void
    {
        AuditLog::query()->create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => EditorialReviewBatch::class, 'auditable_id' => $batch?->id, 'metadata' => [...$metadata, 'auto_publish' => false]]);
    }
}
