<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'cuisine' => ['nullable', 'exists:cuisines,slug'],
            'category' => ['nullable', 'exists:categories,slug'],
            'max_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'sort' => ['nullable', 'in:newest,time'],
        ]);
        $query = auth()->user()->favoriteRecipes()
            ->with(['cuisine', 'category', 'media'])
            ->where('recipes.status', 'published')
            ->where('recipes.verification_status', 'verified');
        if (filled($filters['q'] ?? null)) {
            $query->where(fn ($builder) => $builder->where('recipes.title', 'like', '%'.$filters['q'].'%')->orWhere('recipes.excerpt', 'like', '%'.$filters['q'].'%'));
        }
        if (filled($filters['cuisine'] ?? null)) {
            $query->whereHas('cuisine', fn ($builder) => $builder->where('slug', $filters['cuisine']));
        }
        if (filled($filters['category'] ?? null)) {
            $query->whereHas('category', fn ($builder) => $builder->where('slug', $filters['category']));
        }
        if (filled($filters['max_minutes'] ?? null)) {
            $query->whereRaw('(prep_minutes + cook_minutes + rest_minutes) <= ?', [(int) $filters['max_minutes']]);
        }
        ($filters['sort'] ?? 'newest') === 'time'
            ? $query->orderByRaw('prep_minutes + cook_minutes + rest_minutes')
            : $query->latest('favorites.created_at');

        return view('favorites.index', [
            'recipes' => $query->paginate(12)->withQueryString(),
            'cuisines' => Cuisine::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function toggle(Request $request, Recipe $recipe): RedirectResponse
    {
        abort_unless($recipe->status === 'published' || $request->user()?->isAdmin(), 404);
        $request->user()->favoriteRecipes()->toggle($recipe->id);

        return back()->with('status', 'Favorileriniz güncellendi.');
    }
}
