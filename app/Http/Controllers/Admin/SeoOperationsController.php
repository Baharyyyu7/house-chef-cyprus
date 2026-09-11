<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContentBrief;
use App\Models\ContentOpportunity;
use App\Models\SeoIssue;
use App\Services\Seo\ContentGapService;
use App\Services\Seo\SeoAuditService;
use App\Services\Seo\SeoAuthorityService;
use App\Services\Seo\SeoProviderHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SeoOperationsController extends Controller
{
    public function index(SeoAuthorityService $authority): View
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
        ]);
    }

    public function issues(Request $request): View
    {
        $this->admin();
        $issues = SeoIssue::query()->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->string('severity')->toString()))->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))->when($request->filled('status'), fn ($query) => $request->string('status')->toString() === 'resolved' ? $query->whereNotNull('resolved_at') : $query->whereNull('resolved_at'))->latest('last_detected_at')->paginate(30)->withQueryString();

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

        $opportunities = $query->orderByDesc('priority')->paginate(30)->withQueryString();

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
            'status' => ['required', 'string', 'in:open,contacted,won,declined,resolved'],
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
        ];

        $label = $statusLabels[$validated['status']] ?? $validated['status'];

        return back()->with('status', "Fırsat durumu güncellendi: {$label}");
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
