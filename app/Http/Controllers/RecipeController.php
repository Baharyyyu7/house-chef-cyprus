<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\AI\AiProviderInterface;
use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Allergen;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\DietaryCompatibilityService;
use App\Services\IngredientMatchingService;
use App\Services\Measurements\RecipeIngredientDisplayService;
use App\Services\NutritionService;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request, RecipeSearchInterface $search)
    {
        $filters = $request->only([
            'q', 'cuisine', 'category', 'difficulty', 'dietary_tag', 'exclude_allergens',
            'max_minutes', 'min_protein', 'max_calories', 'sort', 'page',
        ]);

        return view('recipes.index', [
            'recipes' => $search->search($filters),
            'cuisines' => Cuisine::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'dietaryTags' => DietaryTag::orderBy('name')->get(),
            'allergens' => Allergen::orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, Recipe $recipe, NutritionService $nutrition, IngredientMatchingService $matching, AllergenAnalysisService $allergens, DietaryCompatibilityService $dietary, RecipeIngredientDisplayService $ingredientDisplay)
    {
        abort_unless(($recipe->status === 'published' && $recipe->verification_status === 'verified') || $request->user()?->isAdmin(), 404);
        $recipe->load(['cuisine', 'category', 'ingredients', 'recipeIngredients.ingredient', 'recipeIngredients.group', 'steps', 'media' => fn ($query) => $query->where('visibility', 'public'), 'provenances.contentSource']);
        $servings = max(1, (int) $request->integer('servings', $recipe->servings));

        $nutritionResult = $nutrition->snapshot($recipe, $servings);

        return view('recipes.show', [
            'recipe' => $recipe,
            'servings' => $servings,
            'nutrition' => $nutritionResult,
            'allergenAnalysis' => $allergens->analyse($recipe),
            'dietaryCompatibility' => $dietary->assess($recipe, $servings),
            'match' => $request->user() ? $matching->match($recipe, $request->user()) : null,
            'isFavorited' => $request->user()?->favoriteRecipes()->whereKey($recipe->id)->exists() ?? false,
            'ingredientLines' => $ingredientDisplay->forRecipe($recipe->recipeIngredients, (int) $recipe->servings, $servings),
            'relatedRecipes' => Recipe::query()
                ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status'])
                ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($query) => $query->select(['id', 'mediable_type', 'mediable_id', 'disk', 'path', 'alt_text', 'is_placeholder', 'verification_status', 'sort_order'])->where('visibility', 'public'), 'dietaryTags:id,code,name'])
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->whereKeyNot($recipe->id)
                ->where(function ($query) use ($recipe): void {
                    $query->where('category_id', $recipe->category_id)
                        ->orWhere('cuisine_id', $recipe->cuisine_id);
                })
                ->orderByRaw('case when category_id = ? then 0 else 1 end', [$recipe->category_id])
                ->limit(4)
                ->get(),
        ]);
    }

    public function ai(Request $request, Recipe $recipe, AiProviderInterface $provider)
    {
        $validated = $request->validate(['question' => ['required', 'string', 'max:1000']]);

        return response()->json(['answer' => $provider->answer($validated['question'], $recipe)]);
    }
}
