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

        return view('admin.seo.index', [
            'issues' => SeoIssue::query()->whereNull('resolved_at')->latest('last_detected_at')->limit(8)->get(),
            'opportunities' => ContentOpportunity::query()->where('status', 'open')->orderByDesc('priority')->limit(8)->get(),
            'briefs' => ContentBrief::query()->latest()->limit(5)->get(),
            'postsAwaitingReview' => BlogPost::query()->whereIn('editorial_status', ['draft', 'in_review', 'pending'])->count(),
            'providerHealth' => app(SeoProviderHealthService::class)->snapshot(),
            'score' => $authority->snapshot()['score'],
            'authority' => $authority->snapshot(),
        ]);
    }

    public function issues(Request $request): View
    {
        $this->admin();
        $issues = SeoIssue::query()->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->string('severity')->toString()))->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))->when($request->filled('status'), fn ($query) => $request->string('status')->toString() === 'resolved' ? $query->whereNotNull('resolved_at') : $query->whereNull('resolved_at'))->latest('last_detected_at')->paginate(30)->withQueryString();

        return view('admin.seo.issues', compact('issues'));
    }

    public function opportunities(): View
    {
        $this->admin();

        return view('admin.seo.opportunities', ['opportunities' => ContentOpportunity::query()->orderByDesc('priority')->paginate(30)]);
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
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
