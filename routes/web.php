<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DataQualityController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\IngredientController as AdminIngredientController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\NutritionController as AdminNutritionController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\RecipeStepController as AdminRecipeStepController;
use App\Http\Controllers\CookingSessionController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\IngredientExplorerController;
use App\Http\Controllers\PantryController;
use App\Http\Controllers\RecipeController;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Route;

Route::get('/', [RecipeController::class, 'index'])->name('home');
Route::get('/health', HealthController::class)->name('health');
Route::get('/tarifler', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/tarifler/{recipe:slug}', [RecipeController::class, 'show'])->name('recipes.show');
Route::post('/tarifler/{recipe:slug}/ai', [RecipeController::class, 'ai'])->middleware('throttle:ai')->name('recipes.ai');

Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/ne-pisirebilirim', [IngredientExplorerController::class, 'index'])->name('ingredients.explore');
    Route::post('/tarifler/{recipe:slug}/favori', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/dolabim', [PantryController::class, 'index'])->name('pantry.index');
    Route::post('/dolabim', [PantryController::class, 'store'])->name('pantry.store');
    Route::delete('/dolabim/{pantryItem}', [PantryController::class, 'destroy'])->name('pantry.destroy');
    Route::post('/tarifler/{recipe:slug}/pisir', [CookingSessionController::class, 'start'])->name('cooking-sessions.start');
    Route::get('/pisir/{cookingSession}', [CookingSessionController::class, 'show'])->name('cooking-sessions.show');
    Route::post('/pisir/{cookingSession}/adimlar/{step}/tamamla', [CookingSessionController::class, 'completeStep'])->name('cooking-sessions.steps.complete');
    Route::delete('/pisir/{cookingSession}/adimlar/{step}/tamamla', [CookingSessionController::class, 'undoStep'])->name('cooking-sessions.steps.undo');
    Route::post('/pisir/{cookingSession}/duraklat', [CookingSessionController::class, 'pause'])->name('cooking-sessions.pause');
    Route::post('/pisir/{cookingSession}/devam', [CookingSessionController::class, 'resume'])->name('cooking-sessions.resume');
    Route::post('/pisir/{cookingSession}/tamamla', [CookingSessionController::class, 'complete'])->name('cooking-sessions.complete');
    Route::post('/pisir/{cookingSession}/adimlar/{step}/zamanlayici', [CookingSessionController::class, 'createTimer'])->name('cooking-sessions.timers.create');
    Route::post('/pisir/{cookingSession}/zamanlayicilar/{timer}/duraklat', [CookingSessionController::class, 'pauseTimer'])->name('cooking-sessions.timers.pause');
    Route::post('/pisir/{cookingSession}/zamanlayicilar/{timer}/devam', [CookingSessionController::class, 'resumeTimer'])->name('cooking-sessions.timers.resume');
});

Route::prefix('yonetim')->name('admin.')->middleware(['auth', 'verified', 'can:access-admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('sistem-durumu/queue-yenile', [AdminDashboardController::class, 'refreshQueue'])->name('system-status.refresh-queue');
    Route::post('sistem-durumu/scheduler-yenile', [AdminDashboardController::class, 'refreshScheduler'])->name('system-status.refresh-scheduler');
    Route::post('sistem-durumu/cache-yenile', [AdminDashboardController::class, 'refreshCache'])->name('system-status.refresh-cache');
    Route::get('basarisiz-isler', [FailedJobController::class, 'index'])->name('failed-jobs.index');
    Route::get('basarisiz-isler/{uuid}', [FailedJobController::class, 'show'])->name('failed-jobs.show');
    Route::post('basarisiz-isler/{uuid}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
    Route::post('basarisiz-isler/toplu-yeniden-dene', [FailedJobController::class, 'retryMany'])->name('failed-jobs.retry-many');
    Route::delete('basarisiz-isler/{uuid}', [FailedJobController::class, 'forget'])->name('failed-jobs.forget');
    Route::get('medya', [AdminMediaController::class, 'index'])->name('media.index');
    Route::get('medya/yukle', [AdminMediaController::class, 'create'])->name('media.create');
    Route::post('medya', [AdminMediaController::class, 'store'])->name('media.store');
    Route::post('medya/orphan-tara', [AdminMediaController::class, 'scanOrphans'])->name('media.scan-orphans');
    Route::post('medya/gecici-temizle', [AdminMediaController::class, 'cleanupTemporary'])->name('media.cleanup-temporary');
    Route::get('medya/{publicId}', [AdminMediaController::class, 'show'])->name('media.show');
    Route::get('medya/{publicId}/indir', [AdminMediaController::class, 'downloadPrivate'])->name('media.download-private');
    Route::post('medya/{publicId}/thumbnail', [AdminMediaController::class, 'regenerate'])->name('media.regenerate');
    Route::delete('medya/{publicId}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
    Route::get('veri-kalitesi', [DataQualityController::class, 'index'])->name('data-quality.index');
    Route::get('veri-kalitesi/{issue}', [DataQualityController::class, 'show'])->name('data-quality.show');
    Route::post('veri-kalitesi/tara', [DataQualityController::class, 'scan'])->name('data-quality.scan');
    Route::post('veri-kalitesi/{issue}/coz', [DataQualityController::class, 'resolve'])->name('data-quality.resolve');
    Route::post('veri-kalitesi/{issue}/yeniden-ac', [DataQualityController::class, 'reopen'])->name('data-quality.reopen');
    Route::get('beslenme', [AdminNutritionController::class, 'index'])->name('nutrition.index');
    Route::get('beslenme/snapshots/{snapshot}', [AdminNutritionController::class, 'snapshot'])->name('nutrition.snapshots.show');
    Route::get('beslenme/gecmis', [AdminNutritionController::class, 'history'])->name('nutrition.history');
    Route::post('beslenme/nutrients', [AdminNutritionController::class, 'saveNutrient'])->name('nutrition.nutrients.store');
    Route::put('beslenme/nutrients/{nutrient}', [AdminNutritionController::class, 'saveNutrient'])->name('nutrition.nutrients.update');
    Route::post('beslenme/allergens', [AdminNutritionController::class, 'saveAllergen'])->name('nutrition.allergens.store');
    Route::put('beslenme/allergens/{allergen}', [AdminNutritionController::class, 'saveAllergen'])->name('nutrition.allergens.update');
    Route::post('beslenme/dietary-tags', [AdminNutritionController::class, 'saveDietaryTag'])->name('nutrition.dietary-tags.store');
    Route::put('beslenme/dietary-tags/{tag}', [AdminNutritionController::class, 'saveDietaryTag'])->name('nutrition.dietary-tags.update');
    Route::post('beslenme/malzemeler/{ingredient}/facts', [AdminNutritionController::class, 'saveFact'])->name('nutrition.facts.store');
    Route::post('beslenme/ingredient-allergens', [AdminNutritionController::class, 'saveRelation'])->name('nutrition.relations.store');
    Route::post('beslenme/ingredient-allergens/{relation}/toggle', [AdminNutritionController::class, 'toggleRelation'])->name('nutrition.relations.toggle');
    Route::post('beslenme/recipe-allergen-overrides', [AdminNutritionController::class, 'saveOverride'])->name('nutrition.overrides.store');
    Route::post('beslenme/recipe-allergen-overrides/{override}/toggle', [AdminNutritionController::class, 'toggleOverride'])->name('nutrition.overrides.toggle');
    Route::post('beslenme/settings', [AdminNutritionController::class, 'saveSetting'])->name('nutrition.settings.store');
    Route::post('beslenme/tarifler/{recipe}/invalidate', [AdminNutritionController::class, 'invalidate'])->name('nutrition.snapshots.invalidate');
    Route::post('beslenme/tarifler/{recipe}/recalculate', [AdminNutritionController::class, 'recalculate'])->name('nutrition.snapshots.recalculate');
    Route::resource('tarifler', AdminRecipeController::class)->names('recipes')->parameters(['tarifler' => 'recipe'])->except(['show']);
    Route::get('tarifler/public/{publicId}/duzenle', function (string $publicId, AdminRecipeController $controller) {
        $recipe = Recipe::query()->where('public_id', $publicId)->firstOrFail();

        return $controller->edit($recipe);
    })->name('recipes.edit-public');
    Route::get('malzemeler', [AdminIngredientController::class, 'index'])->name('ingredients.index');
    Route::get('malzemeler/public/{publicId}/duzenle', function (string $publicId, AdminIngredientController $controller) {
        $ingredient = Ingredient::query()->where('public_id', $publicId)->firstOrFail();

        return $controller->edit($ingredient);
    })->name('ingredients.edit-public');
    Route::get('malzemeler/{ingredient}/duzenle', [AdminIngredientController::class, 'edit'])->name('ingredients.edit');
    Route::put('malzemeler/{ingredient}', [AdminIngredientController::class, 'update'])->name('ingredients.update');
    Route::post('malzemeler/{ingredient}/aliaslar', [AdminIngredientController::class, 'storeAlias'])->name('ingredients.aliases.store');
    Route::post('malzemeler/{ingredient}/alternatifler', [AdminIngredientController::class, 'storeSubstitution'])->name('ingredients.substitutions.store');
    Route::get('tarifler/{recipe}/adimlar', [AdminRecipeStepController::class, 'index'])->name('recipes.steps.index');
    Route::get('tarifler/{recipe}/adimlar/ekle', [AdminRecipeStepController::class, 'create'])->name('recipes.steps.create');
    Route::post('tarifler/{recipe}/adimlar', [AdminRecipeStepController::class, 'store'])->name('recipes.steps.store');
    Route::get('tarifler/{recipe}/adimlar/{step}/duzenle', [AdminRecipeStepController::class, 'edit'])->name('recipes.steps.edit');
    Route::put('tarifler/{recipe}/adimlar/{step}', [AdminRecipeStepController::class, 'update'])->name('recipes.steps.update');
    Route::delete('tarifler/{recipe}/adimlar/{step}', [AdminRecipeStepController::class, 'destroy'])->name('recipes.steps.destroy');
    Route::post('tarifler/{recipe}/adimlar/sirala', [AdminRecipeStepController::class, 'reorder'])->name('recipes.steps.reorder');
});

require __DIR__.'/auth.php';
