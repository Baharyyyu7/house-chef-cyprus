<?php

declare(strict_types=1);

namespace App\Http\Controllers\Localized;

use App\Http\Controllers\Controller;
use App\Models\BlogPostTranslation;
use App\Models\RecipeTranslation;
use App\Services\Localization\LanguageAlternates;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LocalizedPageController extends Controller
{
    public function home(Request $request, LanguageAlternates $alternates): View
    {
        $locale = app()->getLocale();
        $recipes = RecipeTranslation::query()->publiclyVisible($locale)
            ->with(['recipe' => fn ($query) => $query->with(['media' => fn ($media) => $media->where('visibility', 'public')])])
            ->get();
        $withPhoto = fn (RecipeTranslation $translation): bool => $translation->recipe?->media->contains(fn ($media) => $media->isApprovedRealMedia()) ?? false;
        $sorted = $recipes->sortByDesc(fn (RecipeTranslation $translation): int => $withPhoto($translation) ? 1 : 0)->values();

        $used = [];
        $pick = function (callable $filter) use ($sorted, &$used) {
            $selection = $sorted->filter(fn (RecipeTranslation $translation): bool => ! in_array($translation->id, $used, true) && $filter($translation))->take(4)->values();
            $used = [...$used, ...$selection->pluck('id')->all()];

            return $selection;
        };
        $label = fn (RecipeTranslation $translation, string $field): string => mb_strtolower((string) $translation->{$field});

        return view('localized.'.$locale.'.home', [
            'sections' => [
                'cyprus' => $pick(fn (RecipeTranslation $translation): bool => str_contains($label($translation, 'cuisine_label'), 'cypriot')),
                'quick' => $pick(fn (RecipeTranslation $translation): bool => $translation->totalMinutes() <= 30),
                'breakfast' => $pick(fn (RecipeTranslation $translation): bool => str_contains($label($translation, 'category_label'), 'breakfast')),
                'mains' => $pick(fn (RecipeTranslation $translation): bool => str_contains($label($translation, 'category_label'), 'main')),
                'desserts' => $pick(fn (RecipeTranslation $translation): bool => str_contains($label($translation, 'category_label'), 'dessert')),
            ],
            'recipeCount' => $recipes->count(),
            'latestPosts' => BlogPostTranslation::query()->publiclyVisible($locale)->with(['post' => fn ($query) => $query->with('heroMedia')])->latest('id')->take(3)->get(),
            'metadata' => [
                'title' => 'House Chef Cyprus · Cypriot & Mediterranean Recipes in English',
                'description' => 'Authentic Cypriot, Turkish and Mediterranean home recipes in English: halloumi, sheftalia, meze, soups and desserts with clear steps, metric measures and honest notes.',
                'canonical' => route($locale.'.home'),
                'robots' => ! config('seo.indexing_enabled') ? 'noindex,nofollow' : ($request->query() === [] ? 'index,follow' : 'noindex,follow'),
            ],
            'alternates' => $request->query() === [] ? $alternates->forRequest($request) : [],
        ]);
    }

    public function about(Request $request, LanguageAlternates $alternates): View
    {
        $locale = app()->getLocale();

        return view('localized.'.$locale.'.about', [
            'recipeCount' => RecipeTranslation::query()->publiclyVisible($locale)->count(),
            'metadata' => [
                'title' => 'About House Chef Cyprus · Editorial Recipe Kitchen',
                'description' => 'House Chef Cyprus is an independent editorial recipe site from Cyprus. Learn how our Cypriot and Mediterranean recipes are written, checked and kept up to date.',
                'canonical' => route($locale.'.about'),
                'robots' => ! config('seo.indexing_enabled') ? 'noindex,nofollow' : ($request->query() === [] ? 'index,follow' : 'noindex,follow'),
            ],
            'alternates' => $alternates->forRequest($request),
        ]);
    }
}
