<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecipeStepRequest;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Services\RecipeStepService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RecipeStepController extends Controller
{
    public function index(Recipe $recipe): View
    {
        return view('admin.recipe-steps.index', ['recipe' => $recipe->load('steps')]);
    }

    public function create(Recipe $recipe): View
    {
        return view('admin.recipe-steps.form', ['recipe' => $recipe, 'step' => new RecipeStep]);
    }

    public function store(StoreRecipeStepRequest $request, Recipe $recipe, RecipeStepService $steps): RedirectResponse
    {
        $steps->create($recipe, $request->validated());

        return redirect()->route('admin.recipes.steps.index', $recipe)->with('status', 'Adım eklendi.');
    }

    public function edit(Recipe $recipe, RecipeStep $step): View
    {
        $this->ensureStepRecipe($recipe, $step);

        return view('admin.recipe-steps.form', compact('recipe', 'step'));
    }

    public function update(StoreRecipeStepRequest $request, Recipe $recipe, RecipeStep $step, RecipeStepService $steps): RedirectResponse
    {
        $this->ensureStepRecipe($recipe, $step);
        $steps->update($step, $request->validated());

        return redirect()->route('admin.recipes.steps.index', $recipe)->with('status', 'Adım güncellendi.');
    }

    public function destroy(Recipe $recipe, RecipeStep $step, RecipeStepService $steps): RedirectResponse
    {
        $this->ensureStepRecipe($recipe, $step);
        $steps->delete($step);

        return back()->with('status', 'Adım silindi ve sıra düzenlendi.');
    }

    public function reorder(Request $request, Recipe $recipe, RecipeStepService $steps): RedirectResponse
    {
        $validated = $request->validate(['step_ids' => ['required', 'array'], 'step_ids.*' => ['integer']]);
        $steps->reorder($recipe, $validated['step_ids']);

        return back()->with('status', 'Adım sırası güncellendi.');
    }

    private function ensureStepRecipe(Recipe $recipe, RecipeStep $step): void
    {
        abort_unless($step->recipe_id === $recipe->id, 404);
    }
}
