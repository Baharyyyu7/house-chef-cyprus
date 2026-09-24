<?php

declare(strict_types=1);

namespace App\Http\Controllers\Localized;

use App\Http\Controllers\Controller;
use App\Models\RecipeTranslation;
use App\Services\Localization\LanguageAlternates;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class LocalizedRecipeController extends Controller
{
    public function index(Request $request, LanguageAlternates $alternates): View
    {
        $locale = app()->getLocale();
        $search = trim($request->string('q')->toString());
        $category = trim($request->string('category')->toString());

        $recipes = RecipeTranslation::query()->publiclyVisible($locale)
            ->with(['recipe' => fn ($query) => $query->with(['media' => fn ($media) => $media->where('visibility', 'public')])])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $nested) => $nested
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('excerpt', 'like', '%'.$search.'%')
                ->orWhere('cuisine_label', 'like', '%'.$search.'%')))
            ->when($category !== '', fn (Builder $query) => $query->where('category_label', $category))
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        $categories = RecipeTranslation::query()->publiclyVisible($locale)->whereNotNull('category_label')
            ->distinct()->orderBy('category_label')->pluck('category_label');

        return view('localized.'.$locale.'.recipes.index', [
            'recipes' => $recipes,
            'categories' => $categories,
            'search' => $search,
            'activeCategory' => $category,
            'metadata' => $this->metadata($request, 'Cypriot & Mediterranean Recipes in English · House Chef Cyprus', 'Tested Cypriot, Turkish and Mediterranean home recipes in English: halloumi breakfasts, sheftalia, lentil soup, meze and more, with clear steps and metric measures.', route($locale.'.recipes.index')),
            'alternates' => $request->query() === [] ? $alternates->forRequest($request) : [],
        ]);
    }

    public function show(Request $request, string $slug, LanguageAlternates $alternates, SeoSchemaService $schemaService): View
    {
        $locale = app()->getLocale();
        $translation = RecipeTranslation::query()->publiclyVisible($locale)->where('slug', $slug)
            ->with(['recipe' => fn ($query) => $query->with(['media' => fn ($media) => $media->where('visibility', 'public')])])
            ->firstOrFail();
        $recipe = $translation->recipe;
        abort_if($recipe === null, 404);

        $heroMedia = $recipe->media->first(fn ($media) => $media->isApprovedRealMedia());
        $related = RecipeTranslation::query()->publiclyVisible($locale)
            ->whereKeyNot($translation->id)
            ->with(['recipe' => fn ($query) => $query->with(['media' => fn ($media) => $media->where('visibility', 'public')])])
            ->orderByRaw('case when category_label = ? then 0 when cuisine_label = ? then 1 else 2 end', [(string) $translation->category_label, (string) $translation->cuisine_label])
            ->orderBy('title')
            ->limit(4)
            ->get();

        return view('localized.'.$locale.'.recipes.show', [
            'translation' => $translation,
            'recipe' => $recipe,
            'heroMedia' => $heroMedia,
            'relatedRecipes' => $related,
            'turkishUrl' => route('recipes.show', $recipe),
            'schema' => $schemaService->localizedRecipe($translation, $heroMedia?->publicUrl() ?: ''),
            'metadata' => $this->metadata($request, $translation->meta_title ?: $translation->title.' Recipe · House Chef Cyprus', $translation->meta_description ?: $translation->excerpt, route($locale.'.recipes.show', $translation->slug)),
            'alternates' => $alternates->forRecipeTranslation($translation),
        ]);
    }

    /** @return array{title:string,description:string,canonical:string,robots:string} */
    private function metadata(Request $request, string $title, ?string $description, string $canonical): array
    {
        return [
            'title' => Str::limit($title, 70, ''),
            'description' => Str::limit((string) $description, 160, ''),
            'canonical' => $canonical,
            'robots' => ! config('seo.indexing_enabled') ? 'noindex,nofollow' : ($request->query() === [] ? 'index,follow' : 'noindex,follow'),
        ];
    }
}
