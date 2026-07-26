<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;

final class IngredientNutritionController extends Controller
{
    public function __invoke(Ingredient $ingredient)
    {
        $ingredient->load('nutritionFacts.nutrient');

        return response()->json(['data' => ['public_id' => $ingredient->public_id, 'name' => $ingredient->name, 'facts' => $ingredient->nutritionFacts->map(fn ($fact) => ['nutrient' => ['public_id' => $fact->nutrient->public_id, 'code' => $fact->nutrient->code, 'name' => $fact->nutrient->name, 'unit' => $fact->nutrient->unit], 'amount' => (string) $fact->value, 'basis_type' => $fact->basis_type, 'basis_amount' => (string) $fact->basis_amount, 'origin' => $fact->origin, 'confidence_score' => $fact->confidence_score, 'source_name' => $fact->source_name])]]);
    }
}
