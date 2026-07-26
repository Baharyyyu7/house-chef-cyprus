<?php

namespace App\Observers;

use App\Models\Ingredient;
use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;

final class NutritionFactObserver
{
    public function saved(NutritionFact $fact): void
    {
        $this->invalidate($fact);
    }

    public function deleted(NutritionFact $fact): void
    {
        $this->invalidate($fact);
    }

    private function invalidate(NutritionFact $fact): void
    {
        if ($fact->nutritionable_type !== Ingredient::class) {
            return;
        } foreach (RecipeIngredient::query()->where('ingredient_id', $fact->nutritionable_id)->pluck('recipe_id')->unique() as $id) {
            DB::afterCommit(function () use ($id): void {
                if ($recipe = Recipe::query()->find($id)) {
                    app(NutritionService::class)->invalidate($recipe);
                }
            });
        }
    }
}
