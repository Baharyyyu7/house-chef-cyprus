<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FavoriteController extends Controller
{
    public function index(): View
    {
        return view('favorites.index', [
            'recipes' => auth()->user()->favoriteRecipes()
                ->with(['cuisine', 'category'])
                ->where('recipes.status', 'published')
                ->latest('favorites.created_at')
                ->paginate(12),
        ]);
    }

    public function toggle(Request $request, Recipe $recipe): RedirectResponse
    {
        abort_unless($recipe->status === 'published' || $request->user()?->isAdmin(), 404);
        $request->user()->favoriteRecipes()->toggle($recipe->id);

        return back()->with('status', 'Favorileriniz güncellendi.');
    }
}
