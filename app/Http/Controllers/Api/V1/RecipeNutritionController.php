<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Http\Request;

final class RecipeNutritionController extends Controller
{
    public function __invoke(Request $request, Recipe $recipe, NutritionService $nutrition)
    {
        abort_unless($recipe->status === 'published', 404);

        return response()->json(['data' => $nutrition->snapshot($recipe, max(1, min(24, (int) $request->integer('servings', $recipe->servings))))]);
    }
}
