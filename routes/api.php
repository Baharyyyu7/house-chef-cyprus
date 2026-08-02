<?php

use App\Http\Controllers\Api\V1\IngredientNutritionController;
use App\Http\Controllers\Api\V1\IngredientSubstitutionController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\PersonalizationController;
use App\Http\Controllers\Api\V1\RecipeAllergenController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\RecipeMatchController;
use App\Http\Controllers\Api\V1\RecipeNutritionController;
use App\Http\Controllers\Api\V1\VersionController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\RequireJsonApi;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::get('health', HealthController::class);
    Route::get('status', HealthController::class);
    Route::get('version', VersionController::class);
    Route::get('recipes', [RecipeController::class, 'index']);
    Route::get('recipes/{recipe:public_id}', [RecipeController::class, 'show']);
    Route::get('recipes/{recipe:public_id}/nutrition', RecipeNutritionController::class);
    Route::get('recipes/{recipe:public_id}/allergens', RecipeAllergenController::class);
    Route::get('ingredients', [LookupController::class, 'ingredients']);
    Route::get('cuisines', [LookupController::class, 'cuisines']);
    Route::get('categories', [LookupController::class, 'categories']);
    Route::get('ingredients/{ingredient:public_id}/substitutions', IngredientSubstitutionController::class);
    Route::get('ingredients/{ingredient:public_id}/nutrition', IngredientNutritionController::class);
    Route::get('nutrients', [LookupController::class, 'nutrients']);
    Route::get('allergens', [LookupController::class, 'allergens']);
    Route::get('dietary-tags', [LookupController::class, 'dietaryTags']);
    Route::post('recipe-match', RecipeMatchController::class)->middleware(RequireJsonApi::class);

    Route::middleware('auth')->prefix('personalization')->group(function (): void {
        Route::get('profile', [PersonalizationController::class, 'profile']);
        Route::patch('profile', [PersonalizationController::class, 'updateProfile']);
        Route::get('recommendations', [PersonalizationController::class, 'recommendations']);
        Route::post('recipes/{recipe:public_id}/feedback', [PersonalizationController::class, 'feedback']);
        Route::post('meal-plans', [PersonalizationController::class, 'mealPlan']);
        Route::post('meal-plans/{plan:public_id}/shopping-lists', [PersonalizationController::class, 'shopping']);
        Route::patch('shopping-lists/{list:public_id}', [PersonalizationController::class, 'toggleShopping']);
        Route::get('status', [PersonalizationController::class, 'status']);
        Route::post('disable', [PersonalizationController::class, 'disable']);
        Route::post('clear-learned-data', [PersonalizationController::class, 'clear']);
        Route::post('export-requests', [PersonalizationController::class, 'requestExport']);
    });
});
