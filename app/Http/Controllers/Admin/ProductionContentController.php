<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentQualityProfile;
use App\Services\ContentGraduationService;
use App\Services\ProductionContentReadinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductionContentController extends Controller
{
    public function index(Request $request, ProductionContentReadinessService $readiness): View
    {
        abort_unless($request->user()?->can('access-production-content-operations'), 403);

        $profiles = ContentQualityProfile::query()
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('publication_status', $request->string('status')->toString()))
            ->when($request->filled('media'), fn ($query) => $query->where('media_status', $request->string('media')->toString()))
            ->when($request->filled('q'), fn ($query) => $query->where('entity_public_id', 'like', '%'.$request->string('q')->toString().'%'))
            ->orderBy('readiness_score')->paginate(30)->withQueryString();

        return view('admin.production-content.index', ['metrics' => $readiness->dashboard(), 'profiles' => $profiles]);
    }

    public function rescan(Request $request, ProductionContentReadinessService $readiness): RedirectResponse
    {
        abort_unless($request->user()?->can('rescan-production-content'), 403);
        $result = $readiness->scan();

        return back()->with('status', "Production kalite taraması tamamlandı: {$result['recipes']} tarif ve {$result['blogs']} blog. Otomatik yayın yapılmadı.");
    }

    public function advance(Request $request, ContentQualityProfile $profile, ContentGraduationService $graduation): RedirectResponse
    {
        abort_unless($request->user()?->can('advance-production-graduation'), 403);
        $data = $request->validate(['stage' => ['required', 'string', 'max:50']]);
        $graduation->advance($profile, $data['stage'], $request->user());

        return back()->with('status', 'İçerik zorunlu inceleme aşamasına alındı. Bu işlem yayın veya doğrulama yapmaz.');
    }

    public function approve(Request $request, ContentQualityProfile $profile, ContentGraduationService $graduation): RedirectResponse
    {
        abort_unless($request->user()?->can('final-production-content-approval'), 403);
        $graduation->approve($profile, $request->user());

        return back()->with('status', 'İnsan final onayı kaydedildi. Yayın ayrı ve açık bir admin aksiyonudur.');
    }

    public function publish(Request $request, ContentQualityProfile $profile, ContentGraduationService $graduation): RedirectResponse
    {
        abort_unless($request->user()?->can('publish-production-content'), 403);
        $graduation->publish($profile, $request->user());

        return back()->with('status', 'İçerik yetkili insan aksiyonuyla yayınlandı.');
    }
}
