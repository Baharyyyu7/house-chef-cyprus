<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;

final class FavoriteController extends Controller
{
    public function toggle(Recipe $recipe): RedirectResponse
    {
        auth()->user()->favoriteRecipes()->toggle($recipe->id);

        return back()->with('status', 'Favorileriniz güncellendi.');
    }
}
