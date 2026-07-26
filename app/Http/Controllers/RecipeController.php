<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\AI\AiProviderInterface;
use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\IngredientMatchingService;
use App\Services\NutritionService;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request, RecipeSearchInterface $search)
    {
        return view('recipes.index', ['recipes' => $search->search($request->only(['q', 'cuisine', 'diet', 'max_minutes'])), 'cuisines' => Cuisine::orderBy('name')->get()]);
    }

    public function show(Request $request, Recipe $recipe, NutritionService $nutrition, IngredientMatchingService $matching)
    {
        abort_unless($recipe->status === 'published' || $request->user()?->isAdmin(), 404);
        $recipe->load(['cuisine', 'category', 'ingredients', 'steps', 'media']);
        $servings = max(1, (int) $request->integer('servings', $recipe->servings));

        return view('recipes.show', ['recipe' => $recipe, 'servings' => $servings, 'nutrition' => $nutrition->perServing($recipe, $servings), 'match' => $request->user() ? $matching->match($recipe, $request->user()) : null]);
    }

    public function ai(Request $request, Recipe $recipe, AiProviderInterface $provider)
    {
        $validated = $request->validate(['question' => ['required', 'string', 'max:1000']]);

        return response()->json(['answer' => $provider->answer($validated['question'], $recipe)]);
    }
}
