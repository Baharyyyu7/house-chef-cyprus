<?php

use App\Http\Controllers\Api\V1\RecipeController;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::get('health', fn () => response()->json(['data' => ['status' => 'ok', 'locale' => app()->getLocale()]]));
    Route::get('recipes', [RecipeController::class, 'index']);
    Route::get('recipes/{recipe:public_id}', [RecipeController::class, 'show']);
    foreach (['ingredients' => Ingredient::class, 'cuisines' => Cuisine::class, 'categories' => Category::class] as $path => $model) {
        Route::get($path, fn () => $model::query()->select(['public_id', 'name', 'slug'])->orderBy('name')->paginate(50));
    } Route::post('recipe-match', function (Request $r) {
        $ids = $r->validate(['ingredients' => ['required', 'array']])['ingredients'];

        return Recipe::query()->with('ingredients')->where('status', 'published')->whereHas('ingredients', fn ($q) => $q->whereIn('public_id', $ids))->get()->map(fn ($recipe) => ['public_id' => $recipe->public_id, 'title' => $recipe->title]);
    });
});
