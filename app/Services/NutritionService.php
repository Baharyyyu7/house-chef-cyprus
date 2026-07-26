<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;

final class NutritionService
{
    /** @return array<string, mixed> */
    public function perServing(Recipe $recipe, int $servings): array
    {
        $factor = max(1, $servings) / max(1, $recipe->servings);

        return collect($recipe->nutrition ?? [])->map(fn ($value) => is_numeric($value) ? round((float) $value * $factor, 1) : $value)->all();
    }
}
