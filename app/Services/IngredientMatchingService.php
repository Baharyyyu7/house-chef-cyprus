<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;

final class IngredientMatchingService
{
    /** @return array{required: int, available: int, missing: int, status: string} */
    public function match(Recipe $recipe, User $user): array
    {
        $owned = $user->pantryItems()->pluck('ingredient_id')->all();
        $required = $recipe->recipeIngredients()->where('is_optional', false)->get();
        $available = $required->whereIn('ingredient_id', $owned)->count();
        $missing = $required->count() - $available;

        return ['required' => $required->count(), 'available' => $available, 'missing' => $missing, 'status' => $missing === 0 ? 'Tam yapılabilir' : ($missing <= 2 ? "{$missing} malzeme eksik" : 'Alternatifle yapılabilir')];
    }
}
