<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteFeature;
use App\Services\SiteFeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SiteFeatureController extends Controller
{
    public function index(SiteFeatureService $features): View
    {
        return view('admin.site-features.index', [
            'definitions' => SiteFeatureService::DEFINITIONS,
            'states' => $features->states(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $enabled = array_intersect((array) $request->input('enabled', []), array_keys(SiteFeatureService::DEFINITIONS));

        foreach (array_keys(SiteFeatureService::DEFINITIONS) as $key) {
            SiteFeature::query()->updateOrCreate(['key' => $key], [
                'enabled' => in_array($key, $enabled, true),
                'updated_by_user_id' => $request->user()->id,
            ]);
        }

        return to_route('admin.site-features.index')->with('status', 'Geliştirme görünürlükleri güncellendi.');
    }
}
