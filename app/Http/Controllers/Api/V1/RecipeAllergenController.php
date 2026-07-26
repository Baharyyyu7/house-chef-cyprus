<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;

final class RecipeAllergenController extends Controller
{
    public function __invoke(Recipe $recipe, AllergenAnalysisService $analysis)
    {
        abort_unless($recipe->status === 'published', 404);

        return response()->json(['data' => $analysis->analyse($recipe)]);
    }
}
