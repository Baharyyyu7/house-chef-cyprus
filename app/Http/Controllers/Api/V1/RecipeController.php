<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;

final class RecipeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Recipe::query()->where('status', 'published')->select(['public_id', 'title', 'slug', 'excerpt', 'servings'])->paginate(20)]);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        abort_unless($recipe->status === 'published', 404);

        return response()->json(['data' => $recipe->load(['cuisine', 'category', 'ingredients', 'steps'])]);
    }
}
