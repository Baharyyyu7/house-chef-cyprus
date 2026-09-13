<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContentBrief;
use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\SeoBacklinkCandidate;
use App\Models\SeoIssue;
use App\Services\Seo\ContentGapService;
use App\Services\Seo\OrganicGrowthService;
use App\Services\Seo\SeoAuditService;
use App\Services\Seo\SeoAuthorityService;
use App\Services\Seo\SeoProviderHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class SeoOperationsController extends Controller
{
    public function index(SeoAuthorityService $authority, OrganicGrowthService $growth): View
    {
        $this->admin();

        // Tek ölçüm, iki alan. Eskiden snapshot() iki kez çağrılıyordu.
        $olcum = $authority->snapshot();

        return view('admin.seo.index', [
            'issues' => SeoIssue::query()->whereNull('resolved_at')->latest('last_detected_at')->limit(8)->get(),
            'opportunities' => ContentOpportunity::query()->where('status', 'open')->orderByDesc('priority')->limit(8)->get(),
            'briefs' => ContentBrief::query()->latest()->limit(5)->get(),
            'postsAwaitingReview' => BlogPost::query()->whereIn('editorial_status', ['draft', 'in_review', 'pending'])->count(),
            'providerHealth' => app(SeoProviderHealthService::class)->snapshot(),
            'score' => $olcum['score'],
            'authority' => $olcum,
            'growth' => $growth->snapshot(),
            'systemRuns' => IntegrityReport::query()->where('guard', 'like', 'seo:system:%')->latest('id')->limit(21)->get(),
        ]);
    }

    public function issues(Request $request): View
    {
        $this->admin();
        $request->validate([
            'severity' => ['nullable', Rule::in(['critical', 'error', 'warning', 'info'])],
            'category' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(['open', 'resolved'])],
            'fingerprint' => ['nullable', 'string', 'max:64'],
        ]);
        $issues = SeoIssue::query()
            ->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->string('severity')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->input('status') === 'resolved', fn ($query) => $query->whereNotNull('resolved_at'), fn ($query) => $query->whereNull('resolved_at'))
            ->when($request->filled('fingerprint'), fn ($query) => $query->where('fingerprint', $request->string('fingerprint')->toString()))
            ->orderByRaw("CASE severity WHEN 'critical' THEN 0 WHEN 'error' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->latest('last_detected_at')->orderBy('id')->paginate(30)->withQueryString();

        return view('admin.seo.issues', compact('issues'));
    }

    public function opportunities(Request $request): View
    {
        $this->admin();

        $tab = $request->query('tab', 'backlinks');
        $status = $request->query('status');
        $search = $request->query('q');

        $query = ContentOpportunity::query();

        if ($tab === 'backlinks') {
            $query->where('kind', 'backlink_prospect');
        } elseif ($tab === 'gaps') {
            $query->where('kind', '!=', 'backlink_prospect');
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('normalized_key', 'like', "%{$search}%");
            });
        }

        $opportunities = $query->orderByDesc('priority')->orderBy('id')->paginate(30)->withQueryString();

        $stats = [
            'total_backlinks' => ContentOpportunity::query()->where('kind', 'backlink_prospect')->count(),
            'contacted_backlinks' => ContentOpportunity::query()->where('kind', 'backlink_prospect')->where('status', 'contacted')->count(),
            'won_backlinks' => ContentOpportunity::query()->where('kind', 'backlink_prospect')->where('status', 'won')->count(),
            'open_backlinks' => ContentOpportunity::query()->where('kind', 'backlink_prospect')->where('status', 'open')->count(),
            'total_gaps' => ContentOpportunity::query()->where('kind', '!=', 'backlink_prospect')->count(),
        ];

        $dailyTargets = app(\App\Services\Seo\AutonomousGrowthEngine::class)->getDailyTargetsSnapshot();

        return view('admin.seo.opportunities', compact('opportunities', 'stats', 'tab', 'dailyTargets'));
    }

    public function updateOpportunityStatus(Request $request, ContentOpportunity $opportunity): RedirectResponse
    {
        $this->admin();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,contacted,won,declined,resolved,reviewing,completed,dismissed'],
        ]);

        $opportunity->update([
            'status' => $validated['status'],
            'reviewed_at' => now(),
        ]);

        $statusLabels = [
            'open' => 'Açık',
            'contacted' => 'İletişime Geçildi',
            'won' => 'Kazanıldı (Backlink Aktif)',
            'declined' => 'Reddedildi',
            'resolved' => 'Tamamlandı',
            'reviewing' => 'İnceleniyor',
            'completed' => 'Tamamlandı',
            'dismissed' => 'Reddedildi',
        ];

        $label = $statusLabels[$validated['status']] ?? $validated['status'];

        return back()->with('status', "Fırsat durumu güncellendi: {$label}");
    }

    public function growth(Request $request, OrganicGrowthService $service): View
    {
        $this->admin();
        $request->validate(['status' => ['nullable', Rule::in(array_keys(SeoBacklinkCandidate::STATUSES))]]);

        return view('admin.seo.growth', [
            'growth' => $service->snapshot(),
            'providerHealth' => app(SeoProviderHealthService::class)->snapshot(),
            'statuses' => SeoBacklinkCandidate::STATUSES,
            'runs' => IntegrityReport::query()->where('guard', 'seo:growth-refresh')->latest('id')->limit(7)->get(),
            'candidates' => SeoBacklinkCandidate::query()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
                ->latest('id')->paginate(30)->withQueryString(),
            'autonomousTargets' => app(\App\Services\Seo\AutonomousGrowthEngine::class)->getDailyTargetsSnapshot(),
            'backlinkMarkets' => app(\App\Services\Seo\BacklinkMarketService::class)->pazarDagilimi(app(\App\Services\Seo\BacklinkGrowthService::class)->prospects()),
            'localeCoverage' => app(\App\Services\Seo\LocaleGrowthService::class)->icerikKapsami(),
        ]);
    }

    public function refreshGrowth(Request $request, OrganicGrowthService $service): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['limit' => ['nullable', 'integer', 'between:1,1000']]);
        try {
            $result = $service->refresh((int) ($data['limit'] ?? 1000));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['growth' => 'Kuyruk yenilenemedi. Koşu geçmişine hata kaydedildi.']);
        }

        return back()->with('status', $result['status'] === 'busy'
            ? 'Büyüme kuyruğu zaten yenileniyor.'
            : "Büyüme kuyruğu yenilendi: {$result['opportunities_created']} yeni iş, {$result['candidates_created']} araştırma adayı.");
    }

    public function importBacklinks(Request $request, OrganicGrowthService $service): RedirectResponse
    {
        $this->admin();
        $request->validate(['csv' => ['required', 'file', 'max:'.OrganicGrowthService::MAX_IMPORT_KB, 'extensions:csv', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel']]);
        $result = $service->importCsv($request->file('csv'), (string) $request->user()->public_id);

        return back()->with('status', "CSV alındı: {$result['created']} yeni araştırma adayı, {$result['duplicates']} mevcut kayıt.");
    }

    public function reviewBacklink(Request $request, SeoBacklinkCandidate $candidate, OrganicGrowthService $service): RedirectResponse
    {
        $this->admin();
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(SeoBacklinkCandidate::STATUSES))],
            'review_note' => ['nullable', 'string', 'max:2000', 'required_if:status,observed,contacted'],
        ]);
        DB::transaction(function () use ($candidate, $data, $request, $service): void {
            $candidate = SeoBacklinkCandidate::query()->lockForUpdate()->findOrFail($candidate->id);
            if (! in_array($data['status'], SeoBacklinkCandidate::TRANSITIONS[$candidate->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Bu durum geçişi uygun değil. Önce kaydı incelemeye alın.']);
            }
            if ($data['status'] === 'observed' && $service->candidateUrls($candidate->source_url, $candidate->target_url) === null) {
                throw ValidationException::withMessages(['status' => 'Gözlem kaydı için geçerli bir kaynak ve hedef URL gerekli.']);
            }
            $candidate->update([
                'status' => $data['status'], 'review_note' => $data['review_note'] ?? null,
                'reviewed_at' => now(), 'reviewed_by_user_id' => $request->user()->id,
            ]);
        });

        return back()->with('status', 'İnceleme kararı kaydedildi.');
    }

    public function reviewOpportunity(Request $request, ContentOpportunity $opportunity): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'reviewing', 'completed', 'dismissed'])]]);
        $opportunity->update(['status' => $data['status'], 'reviewed_at' => now()]);

        return back()->with('status', 'İş inceleme kararı kaydedildi.');
    }

    public function runAudit(SeoAuditService $service): RedirectResponse
    {
        $this->admin();
        $result = $service->run();

        return back()->with('status', "SEO denetimi tamamlandı: skor {$result['score']}, açık sorun {$result['open']}.");
    }

    public function refreshOpportunities(ContentGapService $service): RedirectResponse
    {
        $this->admin();
        $result = $service->refresh();

        return back()->with('status', "Fırsatlar güncellendi: {$result['created']} yeni kayıt.");
    }

    public function resolve(SeoIssue $issue): RedirectResponse
    {
        $this->admin();
        $issue->update(['resolved_at' => now()]);

        return back()->with('status', 'SEO sorunu çözüldü olarak işaretlendi.');
    }

    public function reopen(SeoIssue $issue): RedirectResponse
    {
        $this->admin();
        $issue->update(['resolved_at' => null, 'reopened_at' => now()]);

        return back()->with('status', 'SEO sorunu yeniden açıldı.');
    }

    private function admin(): void
    {
        $this->authorizeAdminOperations();
    }
}
