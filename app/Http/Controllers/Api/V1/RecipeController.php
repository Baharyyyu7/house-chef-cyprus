<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Search\RecipeSearchInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request, RecipeSearchInterface $search)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'cuisine' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'ingredient' => ['nullable', 'string', 'max:26'],
            'difficulty' => ['nullable', 'string', 'max:50'],
            'max_time' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'min_protein' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'max_calories' => ['nullable', 'numeric', 'min:1', 'max:100000'],
            'verified_only' => ['nullable', 'boolean'],
            'dietary_tag' => ['nullable', 'exists:dietary_tags,code'],
            'exclude_allergens' => ['nullable', 'array', 'max:20'],
            'exclude_allergens.*' => ['distinct', 'exists:allergens,public_id'],
            'sort' => ['nullable', 'in:newest,time'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $filters['q'] = $filters['search'] ?? null;
        $filters['max_minutes'] = $filters['max_time'] ?? null;
        $filters['exclude_allergens'] = $filters['exclude_allergens'] ?? [];

        return RecipeResource::collection($search->search($filters));
    }

    public function show(Recipe $recipe)
    {
        abort_unless($recipe->status === 'published', 404);

        return new RecipeResource($recipe->load(['cuisine', 'category', 'ingredients']));
    }
}
