<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Search\RecipeSearchInterface;
use App\Http\Controllers\Controller;
use App\Services\IngredientMatchingService;
use Illuminate\Http\Request;

final class RecipeMatchController extends Controller
{
    public function __invoke(Request $request, IngredientMatchingService $matching, RecipeSearchInterface $search)
    {
        $validated = $request->validate(['ingredients' => ['sometimes', 'array', 'max:50'], 'ingredients.*' => ['string', 'max:26'], 'excluded' => ['sometimes', 'array', 'max:50'], 'excluded.*' => ['string', 'max:26'], 'exact_only' => ['sometimes', 'boolean'], 'include_substitutions' => ['sometimes', 'boolean'], 'max_missing' => ['sometimes', 'integer', 'min:0', 'max:10'], 'filters' => ['sometimes', 'array'], 'filters.min_protein' => ['nullable', 'numeric', 'min:0'], 'filters.max_calories' => ['nullable', 'numeric', 'min:1'], 'filters.verified_only' => ['nullable', 'boolean'], 'filters.dietary_tag' => ['nullable', 'exists:dietary_tags,code'], 'filters.exclude_allergen' => ['nullable', 'exists:allergens,public_id'], 'filters.per_page' => ['nullable', 'integer', 'min:1', 'max:50'], 'filters.page' => ['nullable', 'integer', 'min:1'], 'filters.sort' => ['nullable', 'in:newest,time']]);
        if (isset($validated['filters'])) {
            $recipes = $search->search($validated['filters']);

            return response()->json(['data' => collect($recipes->items())->map(fn ($recipe) => ['recipe_public_id' => $recipe->public_id, 'title' => $recipe->title])->values(), 'meta' => ['current_page' => $recipes->currentPage(), 'per_page' => $recipes->perPage(), 'total' => $recipes->total()]]);
        }
        abort_unless(isset($validated['ingredients']), 422);
        $results = $matching->findRecipes($validated['ingredients'], $validated['excluded'] ?? [], (bool) ($validated['exact_only'] ?? false), (bool) ($validated['include_substitutions'] ?? true), (int) ($validated['max_missing'] ?? 2));

        return response()->json(['data' => $results->map(fn (array $result) => collect($result)->except(['recipe', 'rank'])->all())->values()]);
    }
}
