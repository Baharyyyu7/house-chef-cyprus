<?php

namespace App\Observers;

use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;

final class RecipeObserver
{
    public function updated(Recipe $recipe): void
    {
        if ($recipe->wasChanged('servings')) {
            DB::afterCommit(fn () => app(NutritionService::class)->invalidate($recipe));
        }
    }
}
