<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Services\IngredientMatchingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class IngredientExplorerController extends Controller
{
    public function index(Request $request, IngredientMatchingService $matching): View
    {
        $selected = array_values(array_unique(array_filter($request->input('ingredients', []), 'is_string')));
        if ($request->boolean('use_pantry')) {
            $selected = $request->user()->pantryItems()->where('is_available', true)->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))->with('ingredient')->get()->pluck('ingredient.public_id')->all();
        }

        return view('ingredients.explore', ['ingredients' => Ingredient::query()->where('is_active', true)->with('aliases')->orderBy('name')->get(), 'selected' => $selected, 'results' => $selected === [] ? collect() : $matching->findRecipes($selected, $request->input('excluded', []), $request->boolean('exact_only'), $request->boolean('include_substitutions', true), min(10, max(0, $request->integer('max_missing', 2))))]);
    }
}
