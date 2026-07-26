<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Services\DataQualityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class DataQualityController extends Controller
{
    public function index(Request $request, DataQualityService $quality): View
    {
        $this->admin();
        $issues = DataQualityIssue::query()->when($request->severity, fn ($q, $v) => $q->where('severity', $v))->when($request->issue_type, fn ($q, $v) => $q->where('issue_type', $v))->when($request->entity_type, fn ($q, $v) => $q->where('entity_type', $v))->when($request->status, fn ($q, $v) => $q->where('resolution_status', $v))->when($request->q, fn ($q, $v) => $q->where('message', 'like', "%$v%"))->latest('last_detected_at')->paginate(30)->withQueryString();
        $open = DataQualityIssue::where('resolution_status', 'open');

        return view('admin.data-quality.index', ['issues' => $issues, 'score' => $quality->score(), 'categoryScores' => $quality->categoryScores(), 'counts' => ['critical' => (clone $open)->where('severity', 'critical')->count(), 'error' => (clone $open)->where('severity', 'error')->count(), 'warning' => (clone $open)->where('severity', 'warning')->count()], 'lastScan' => DataQualityIssue::max('last_detected_at'), 'schedulerStatus' => Cache::get('data_quality.last_scan')]);
    }

    public function show(DataQualityIssue $issue): View
    {
        $this->admin();

        return view('admin.data-quality.show', ['issue' => $issue, 'entityLink' => $this->entityLink($issue)]);
    }

    public function resolve(DataQualityIssue $issue): RedirectResponse
    {
        $this->admin();
        $issue->update(['resolution_status' => 'resolved', 'resolved_at' => now()]);

        return back()->with('status', 'Issue çözüldü olarak işaretlendi.');
    }

    public function reopen(DataQualityIssue $issue): RedirectResponse
    {
        $this->admin();
        $issue->update(['resolution_status' => 'open', 'resolved_at' => null]);

        return back()->with('status', 'Issue yeniden açıldı.');
    }

    public function scan(DataQualityService $quality): RedirectResponse
    {
        $this->admin();
        $quality->scan();

        return back()->with('status', 'Veri kalite taraması tamamlandı.');
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    /** @return array{label:string,url:string}|null */
    private function entityLink(DataQualityIssue $issue): ?array
    {
        return match ($issue->entity_type) {
            Recipe::class => Recipe::query()->where('public_id', $issue->entity_public_id)->exists()
                ? ['label' => 'Tarifi yönet', 'url' => route('admin.recipes.edit-public', $issue->entity_public_id)] : null,
            Ingredient::class => Ingredient::query()->where('public_id', $issue->entity_public_id)->exists()
                ? ['label' => 'Malzemeyi yönet', 'url' => route('admin.ingredients.edit-public', $issue->entity_public_id)] : null,
            NutritionFact::class => ['label' => 'Beslenme yönetimine git', 'url' => route('admin.nutrition.index')],
            default => null,
        };
    }
}
