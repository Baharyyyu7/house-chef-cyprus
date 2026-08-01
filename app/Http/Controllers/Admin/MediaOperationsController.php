<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecipeMediaCandidate;
use App\Services\Media\MediaExcellenceService;
use App\Services\Media\PexelsEditorialReviewService;
use App\Services\Media\UnsplashEditorialReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class MediaOperationsController extends Controller
{
    public function index(Request $request, MediaExcellenceService $media): View
    {
        Gate::authorize('access-media-operations');
        $inventory = $media->inventory();
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $rows = collect(array_merge($inventory['recipes'], $inventory['blogs']))
            ->when($type !== '', fn ($rows) => $rows->where('entity_type', $type))
            ->when($status !== '', fn ($rows) => $rows->where('status', $status))->values();

        return view('admin.media-operations.index', [
            'health' => $media->health(),
            'rows' => $rows,
            'statuses' => [
                'no_media', 'branded_fallback', 'candidate_search_required', 'candidates_ready', 'selected_private',
                'fidelity_review', 'license_review', 'attribution_review', 'optimization_pending',
                'ready_for_human_approval', 'approved_real_media', 'rejected', 'unavailable', 'blocked',
            ],
            'candidates' => RecipeMediaCandidate::query()->with('recipe')->whereIn('status', ['pending_review', 'selected_for_review', 'private_imported'])->latest('scanned_at')->limit(20)->get(),
        ]);
    }

    public function selectCandidate(Request $request, RecipeMediaCandidate $candidate, PexelsEditorialReviewService $pexels, UnsplashEditorialReviewService $unsplash): RedirectResponse
    {
        Gate::authorize('select-media-candidate', $candidate);
        if ($candidate->provider === 'pexels') {
            $pexels->select($candidate->load('recipe'), $request->user());
        } elseif ($candidate->provider === 'unsplash') {
            $unsplash->select($candidate->load('recipe'), $request->user());
        } else {
            abort(422, 'Bu sağlayıcı için seçim yalnız ilgili özel review akışından yapılır.');
        }

        return back()->with('status', 'Aday seçildi; private import ve bağımsız final onay hâlâ gereklidir.');
    }

    public function rejectCandidate(Request $request, RecipeMediaCandidate $candidate, PexelsEditorialReviewService $pexels, UnsplashEditorialReviewService $unsplash): RedirectResponse
    {
        Gate::authorize('review-media-candidate');
        if ($candidate->provider === 'pexels') {
            $pexels->reject($candidate->load('recipe'), $request->user());
        } elseif ($candidate->provider === 'unsplash') {
            $unsplash->reject($candidate->load('recipe'), $request->user());
        } else {
            abort(422, 'Bu sağlayıcı için ret yalnız ilgili özel review akışından yapılır.');
        }

        return back()->with('status', 'Aday reddedildi; public alan değişmedi.');
    }
}
