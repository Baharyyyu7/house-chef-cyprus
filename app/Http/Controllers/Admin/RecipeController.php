<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecipeRequest;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        $recipes = Recipe::query()
            ->with(['cuisine:id,name', 'category:id,name'])
            ->withCount(['recipeIngredients', 'steps', 'media', 'provenances', 'nutritionSnapshots'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($inner) => $inner
                ->where('title', 'like', '%'.$request->string('q')->toString().'%')
                ->orWhere('slug', 'like', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('verification_status'), fn ($query) => $query->where('verification_status', $request->string('verification_status')->toString()))
            ->when($request->filled('cuisine'), fn ($query) => $query->where('cuisine_id', $request->integer('cuisine')))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('image_status'), fn ($query) => $query->where('image_status', $request->string('image_status')->toString()))
            ->when($request->filled('published'), fn ($query) => $request->boolean('published') ? $query->whereNotNull('published_at') : $query->whereNull('published_at'))
            ->when($request->filled('sort'), function ($query) use ($request): void {
                match ($request->string('sort')->toString()) {
                    'oldest' => $query->oldest(),
                    'completeness' => $query->orderBy('completeness_score'),
                    'title' => $query->orderBy('title'),
                    default => $query->latest(),
                };
            })
            ->paginate(20)
            ->withQueryString();

        return view('admin.recipes.index', [
            'recipes' => $recipes,
            'cuisines' => Cuisine::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.recipes.form', ['recipe' => new Recipe, 'cuisines' => Cuisine::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreRecipeRequest $request): RedirectResponse
    {
        Recipe::create($request->validated());

        return redirect()->route('admin.recipes.index')->with('status', 'Tarif oluşturuldu.');
    }

    public function edit(Recipe $recipe): View
    {
        return view('admin.recipes.form', ['recipe' => $recipe, 'cuisines' => Cuisine::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function editPublic(string $publicId): View
    {
        return $this->edit(Recipe::query()->where('public_id', $publicId)->firstOrFail());
    }

    public function update(StoreRecipeRequest $request, Recipe $recipe): RedirectResponse
    {
        $recipe->update($request->validated());

        return redirect()->route('admin.recipes.index')->with('status', 'Tarif güncellendi.');
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();

        return back()->with('status', 'Tarif arşivlendi.');
    }
}
