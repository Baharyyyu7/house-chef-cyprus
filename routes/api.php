<?php

use App\Http\Controllers\Api\V1\AgentToolController;
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
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\RequireJsonApi;
use Illuminate\Support\Facades\Route;

Route::post('stripe/webhook', StripeWebhookController::class)->name('api.stripe.webhook');

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

    // WebMCP / AI Agent Tools Endpoints
    Route::prefix('agent')->group(function (): void {
        Route::get('tools', [AgentToolController::class, 'tools'])->name('api.agent.tools');
        Route::post('tools/search', [AgentToolController::class, 'search'])->name('api.agent.tools.search');
        Route::post('tools/find-by-ingredients', [AgentToolController::class, 'findByIngredients'])->name('api.agent.tools.find-by-ingredients');
        Route::post('tools/recipe', [AgentToolController::class, 'recipe'])->name('api.agent.tools.recipe');
        Route::post('tools/adjust-servings', [AgentToolController::class, 'adjustServings'])->name('api.agent.tools.adjust-servings');
        Route::post('tools/cooking-step', [AgentToolController::class, 'cookingStep'])->name('api.agent.tools.cooking-step');
        Route::post('tools/switch-language', [AgentToolController::class, 'switchLanguage'])->name('api.agent.tools.switch-language');
        // Kullanıcının kendi favorisine yazar: tarayıcı oturumu ve CSRF ile.
        Route::post('tools/save-recipe', [AgentToolController::class, 'saveRecipe'])->middleware('browser-session')->name('api.agent.tools.save-recipe');
    });

    // Oturumsuz 'auth' her isteği 401'e düşürüyordu; bkz. bootstrap/app.php.
    Route::middleware(['browser-session', 'auth'])->prefix('personalization')->group(function (): void {
        Route::get('profile', [PersonalizationController::class, 'profile']);
        Route::patch('profile', [PersonalizationController::class, 'updateProfile']);
        Route::get('taste-profile', [PersonalizationController::class, 'tasteProfile']);
        Route::get('recommendations', [PersonalizationController::class, 'recommendations']);
        Route::post('recipes/{recipe:public_id}/feedback', [PersonalizationController::class, 'feedback']);
        Route::post('recipes/{recipe:public_id}/unhide', [PersonalizationController::class, 'unhide']);
        Route::post('meal-plans', [PersonalizationController::class, 'mealPlan']);
        Route::post('meal-plans/{plan:public_id}/shopping-lists', [PersonalizationController::class, 'shopping']);
        Route::patch('shopping-lists/{list:public_id}', [PersonalizationController::class, 'toggleShopping']);
        Route::post('shopping-lists/{list:public_id}/items', [PersonalizationController::class, 'addShoppingItem']);
        Route::get('expiry-insights', [PersonalizationController::class, 'expiryInsights']);
        Route::get('status', [PersonalizationController::class, 'status']);
        Route::post('disable', [PersonalizationController::class, 'disable']);
        Route::post('clear-learned-data', [PersonalizationController::class, 'clear']);
        Route::post('export-requests', [PersonalizationController::class, 'requestExport']);
    });
});
