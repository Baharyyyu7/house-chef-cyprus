<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\RecipeStepController as AdminRecipeStepController;
use App\Http\Controllers\CookingSessionController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PantryController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RecipeController::class, 'index'])->name('home');
Route::get('/tarifler', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/tarifler/{recipe:slug}', [RecipeController::class, 'show'])->name('recipes.show');
Route::post('/tarifler/{recipe:slug}/ai', [RecipeController::class, 'ai'])->middleware('throttle:ai')->name('recipes.ai');

Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::middleware(['auth', 'verified'])->group(function (): void {
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
    Route::resource('tarifler', AdminRecipeController::class)->names('recipes')->parameters(['tarifler' => 'recipe'])->except(['show']);
    Route::get('tarifler/{recipe}/adimlar', [AdminRecipeStepController::class, 'index'])->name('recipes.steps.index');
    Route::get('tarifler/{recipe}/adimlar/ekle', [AdminRecipeStepController::class, 'create'])->name('recipes.steps.create');
    Route::post('tarifler/{recipe}/adimlar', [AdminRecipeStepController::class, 'store'])->name('recipes.steps.store');
    Route::get('tarifler/{recipe}/adimlar/{step}/duzenle', [AdminRecipeStepController::class, 'edit'])->name('recipes.steps.edit');
    Route::put('tarifler/{recipe}/adimlar/{step}', [AdminRecipeStepController::class, 'update'])->name('recipes.steps.update');
    Route::delete('tarifler/{recipe}/adimlar/{step}', [AdminRecipeStepController::class, 'destroy'])->name('recipes.steps.destroy');
    Route::post('tarifler/{recipe}/adimlar/sirala', [AdminRecipeStepController::class, 'reorder'])->name('recipes.steps.reorder');
});

require __DIR__.'/auth.php';
