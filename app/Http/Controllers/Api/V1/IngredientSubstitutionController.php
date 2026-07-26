<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;

final class IngredientSubstitutionController extends Controller
{
    public function __invoke(Ingredient $ingredient)
    {
        return response()->json(['data' => $ingredient->substitutions()->with('substitute')->where('is_active', true)->get()->map(fn ($substitution) => ['substitution_type' => $substitution->substitution_type, 'ratio' => (string) $substitution->ratio, 'confidence_score' => (float) $substitution->confidence_score, 'is_verified' => $substitution->is_verified, 'ingredient' => ['public_id' => $substitution->substitute->public_id, 'name' => $substitution->substitute->name]])]);
    }
}
