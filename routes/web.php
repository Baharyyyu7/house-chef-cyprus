<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
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
});

Route::prefix('yonetim')->name('admin.')->middleware(['auth', 'verified', 'can:access-admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('tarifler', AdminRecipeController::class)->except(['show']);
});

require __DIR__.'/auth.php';
