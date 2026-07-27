<?php

namespace App\Observers;

use App\Models\IngredientUnitConversion;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;

final class IngredientUnitConversionObserver
{
    public function saved(IngredientUnitConversion $conversion): void
    {
        $this->invalidateRecipes($conversion);
    }

    public function deleted(IngredientUnitConversion $conversion): void
    {
        $this->invalidateRecipes($conversion);
    }

    private function invalidateRecipes(IngredientUnitConversion $conversion): void
    {
        DB::afterCommit(function () use ($conversion): void {
            $conversion->recipeIngredients()->with('recipe')->get()->each(fn ($line) => $line->recipe && app(NutritionService::class)->invalidate($line->recipe));
        });
    }
}
