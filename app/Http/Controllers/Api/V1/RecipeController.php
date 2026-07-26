<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $q = Recipe::query()->with(['cuisine', 'category'])->where('status', 'published')->when($request->search, fn ($b, $v) => $b->where('title', 'like', "%$v%"))->when($request->cuisine, fn ($b, $v) => $b->whereHas('cuisine', fn ($q) => $q->where('slug', $v)))->when($request->category, fn ($b, $v) => $b->whereHas('category', fn ($q) => $q->where('slug', $v)))->when($request->ingredient, fn ($b, $v) => $b->whereHas('ingredients', fn ($q) => $q->where('public_id', $v)))->when($request->difficulty, fn ($b, $v) => $b->where('difficulty', $v))->when($request->max_time, fn ($b, $v) => $b->whereRaw('(prep_minutes+cook_minutes+rest_minutes)<=?', [$v]))->when($request->dietary_tag, fn ($b, $v) => $b->whereHas('dietaryTags', fn ($tags) => $tags->where('code', $v)))->when($request->exclude_allergen, fn ($b, $v) => $b->whereDoesntHave('recipeIngredients.ingredient.allergenDetails.allergen', fn ($allergens) => $allergens->where('code', $v)));
        if ($request->sort === 'time') {
            $q->orderByRaw('(prep_minutes+cook_minutes+rest_minutes)');
        } else {
            $q->latest();
        }

        return RecipeResource::collection($q->paginate(min(50, max(1, (int) $request->integer('per_page', 20)))));
    }

    public function show(Recipe $recipe)
    {
        abort_unless($recipe->status === 'published', 404);

        return new RecipeResource($recipe->load(['cuisine', 'category', 'ingredients']));
    }
}
