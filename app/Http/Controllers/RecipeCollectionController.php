<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class RecipeCollectionController extends Controller
{
    public function index(Request $request): View
    {
        return view('collections.index', [
            'collections' => $request->user()->recipeCollections()->withCount('recipes')->latest()->paginate(12),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('recipe_collections')->where('user_id', $request->user()->id)],
            'description' => ['nullable', 'string', 'max:280'],
        ]);

        $collection = $request->user()->recipeCollections()->create($data);

        return redirect()->route('collections.show', $collection)->with('status', 'Koleksiyon oluşturuldu.');
    }

    public function show(Request $request, RecipeCollection $recipeCollection): View
    {
        Gate::authorize('view', $recipeCollection);

        return view('collections.show', [
            'collection' => $recipeCollection,
            'recipes' => $recipeCollection->recipes()
                ->with(['cuisine', 'category', 'media'])
                ->where('recipes.status', 'published')
                ->where('recipes.verification_status', 'verified')
                ->latest('recipe_collection_items.created_at')
                ->paginate(12),
        ]);
    }

    public function update(Request $request, RecipeCollection $recipeCollection): RedirectResponse
    {
        Gate::authorize('update', $recipeCollection);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('recipe_collections')->where('user_id', $request->user()->id)->ignore($recipeCollection)],
            'description' => ['nullable', 'string', 'max:280'],
        ]);
        $recipeCollection->update($data);

        return back()->with('status', 'Koleksiyon güncellendi.');
    }

    public function destroy(Request $request, RecipeCollection $recipeCollection): RedirectResponse
    {
        Gate::authorize('delete', $recipeCollection);
        $recipeCollection->delete();

        return redirect()->route('collections.index')->with('status', 'Koleksiyon silindi.');
    }

    public function add(Request $request, RecipeCollection $recipeCollection, Recipe $recipe): RedirectResponse
    {
        Gate::authorize('update', $recipeCollection);
        abort_unless($recipe->status === 'published' && $recipe->verification_status === 'verified', 404);
        $recipeCollection->recipes()->syncWithoutDetaching([$recipe->id]);

        return back()->with('status', 'Tarif koleksiyona eklendi.');
    }

    public function remove(Request $request, RecipeCollection $recipeCollection, Recipe $recipe): RedirectResponse
    {
        Gate::authorize('update', $recipeCollection);
        $recipeCollection->recipes()->detach($recipe->id);

        return back()->with('status', 'Tarif koleksiyondan çıkarıldı.');
    }
}
