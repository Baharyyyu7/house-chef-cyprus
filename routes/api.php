<?php

use App\Http\Controllers\Api\V1\IngredientNutritionController;
use App\Http\Controllers\Api\V1\IngredientSubstitutionController;
use App\Http\Controllers\Api\V1\RecipeAllergenController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\RecipeMatchController;
use App\Http\Controllers\Api\V1\RecipeNutritionController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\RequireJsonApi;
use App\Models\Allergen;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Nutrient;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::get('health', HealthController::class);
    Route::get('status', HealthController::class);
    Route::get('version', fn () => response()->json(['data' => ['version' => 'v0.4-foundation-d', 'locale' => app()->getLocale()]]));
    Route::get('recipes', [RecipeController::class, 'index']);
    Route::get('recipes/{recipe:public_id}', [RecipeController::class, 'show']);
    Route::get('recipes/{recipe:public_id}/nutrition', RecipeNutritionController::class);
    Route::get('recipes/{recipe:public_id}/allergens', RecipeAllergenController::class);
    foreach (['ingredients' => Ingredient::class, 'cuisines' => Cuisine::class, 'categories' => Category::class] as $path => $model) {
        Route::get($path, fn () => $model::query()->select(['public_id', 'name', 'slug'])->orderBy('name')->paginate(50));
    }
    Route::get('ingredients/{ingredient:public_id}/substitutions', IngredientSubstitutionController::class);
    Route::get('ingredients/{ingredient:public_id}/nutrition', IngredientNutritionController::class);
    Route::get('nutrients', fn () => response()->json(['data' => Nutrient::query()->where('is_active', true)->orderBy('sort_order')->get(['public_id', 'code', 'name', 'category', 'unit', 'decimal_precision', 'daily_reference_value'])]));
    Route::get('allergens', fn () => response()->json(['data' => Allergen::query()->where('is_active', true)->orderBy('name')->get(['public_id', 'code', 'name'])]));
    Route::get('dietary-tags', fn () => response()->json(['data' => DietaryTag::query()->where('is_active', true)->orderBy('name')->get(['public_id', 'code', 'name', 'description'])]));
    Route::post('recipe-match', RecipeMatchController::class)->middleware(RequireJsonApi::class);
});
