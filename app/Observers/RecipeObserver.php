<?php

namespace App\Observers;

use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;

final class RecipeObserver
{
    public function updated(Recipe $recipe): void
    {
        if ($recipe->wasChanged()) {
            DB::afterCommit(function () use ($recipe): void {
                app(NutritionService::class)->invalidate($recipe);
            });
        }
    }
}
