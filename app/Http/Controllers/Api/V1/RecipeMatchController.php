<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\IngredientMatchingService;
use Illuminate\Http\Request;

final class RecipeMatchController extends Controller
{
    public function __invoke(Request $request, IngredientMatchingService $matching)
    {
        $validated = $request->validate(['ingredients' => ['required', 'array', 'max:50'], 'ingredients.*' => ['string', 'max:26'], 'excluded' => ['sometimes', 'array', 'max:50'], 'excluded.*' => ['string', 'max:26'], 'exact_only' => ['sometimes', 'boolean'], 'include_substitutions' => ['sometimes', 'boolean'], 'max_missing' => ['sometimes', 'integer', 'min:0', 'max:10']]);
        $results = $matching->findRecipes($validated['ingredients'], $validated['excluded'] ?? [], (bool) ($validated['exact_only'] ?? false), (bool) ($validated['include_substitutions'] ?? true), (int) ($validated['max_missing'] ?? 2));

        return response()->json(['data' => $results->map(fn (array $result) => collect($result)->except(['recipe', 'rank'])->all())->values()]);
    }
}
