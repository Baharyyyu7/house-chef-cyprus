<?php

namespace App\Observers;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;

final class RecipeIngredientObserver
{
    public function saved(RecipeIngredient $line): void
    {
        $this->invalidate($line->recipe_id);
    }

    public function deleted(RecipeIngredient $line): void
    {
        $this->invalidate($line->recipe_id);
    }

    private function invalidate(int $id): void
    {
        DB::afterCommit(function () use ($id): void {
            if ($recipe = Recipe::query()->find($id)) {
                app(NutritionService::class)->invalidate($recipe);
            }
        });
    }
}
