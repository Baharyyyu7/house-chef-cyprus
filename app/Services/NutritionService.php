<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;

final class NutritionService
{
    /** @return array<string, float|string> */
    public function perServing(Recipe $recipe, int $servings): array
    {
        $factor = max(1, $servings) / max(1, $recipe->servings);
        $nutrition = [];

        foreach ($recipe->nutrition ?? [] as $nutrient => $value) {
            $nutrition[$nutrient] = is_numeric($value) ? round((float) $value * $factor, 1) : (string) $value;
        }

        return $nutrition;
    }
}
