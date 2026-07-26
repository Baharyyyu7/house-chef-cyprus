<?php

use App\Http\Controllers\Api\V1\IngredientSubstitutionController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\RecipeMatchController;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::get('health', fn () => response()->json(['data' => ['status' => 'ok', 'locale' => app()->getLocale()]]));
    Route::get('recipes', [RecipeController::class, 'index']);
    Route::get('recipes/{recipe:public_id}', [RecipeController::class, 'show']);
    foreach (['ingredients' => Ingredient::class, 'cuisines' => Cuisine::class, 'categories' => Category::class] as $path => $model) {
        Route::get($path, fn () => $model::query()->select(['public_id', 'name', 'slug'])->orderBy('name')->paginate(50));
    }
    Route::get('ingredients/{ingredient:public_id}/substitutions', IngredientSubstitutionController::class);
    Route::post('recipe-match', RecipeMatchController::class);
});
