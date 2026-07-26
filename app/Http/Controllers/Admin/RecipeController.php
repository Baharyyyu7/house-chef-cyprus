<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecipeRequest;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class RecipeController extends Controller
{
    public function index(): View
    {
        return view('admin.recipes.index', ['recipes' => Recipe::with('cuisine')->latest()->paginate(20)]);
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
