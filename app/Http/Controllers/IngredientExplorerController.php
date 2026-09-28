<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Allergen;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Services\IngredientMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class IngredientExplorerController extends Controller
{
    public function index(Request $request, IngredientMatchingService $matching, RecipeSearchInterface $search): View
    {
        $validated = $request->validate([
            'ingredients' => ['nullable', 'array', 'max:50'],
            'ingredients.*' => ['string', 'max:32'],
            'excluded' => ['nullable', 'array', 'max:50'],
            'excluded.*' => ['string', 'max:32'],
            'use_pantry' => ['nullable', 'boolean'],
            'exact_only' => ['nullable', 'boolean'],
            'include_substitutions' => ['nullable', 'boolean'],
            'max_missing' => ['nullable', 'integer', 'min:0', 'max:10'],
            'min_protein' => ['nullable', 'numeric', 'min:0'],
            'max_calories' => ['nullable', 'numeric', 'min:1'],
            'verified_only' => ['nullable', 'boolean'],
            'dietary_tag' => ['nullable', 'exists:dietary_tags,code'],
            'exclude_allergens' => ['nullable', 'array', 'max:20'],
            'exclude_allergens.*' => ['distinct', 'exists:allergens,public_id'],
            'max_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'sort' => ['nullable', 'in:newest,time'],
        ]);

        $selected = array_values(array_unique(array_filter($validated['ingredients'] ?? [], 'is_string')));
        // Sayfa herkese açıktır: misafirler malzemeleriyle eşleştirme yapabilir,
        // dolap entegrasyonu yalnız oturum açmış üyeler için çalışır.
        $user = $request->user();
        $pantryItems = $user === null
            ? collect()
            : $user->pantryItems()->where('is_available', true)->with('ingredient')->orderBy('expires_at')->get();
        $availablePantryItems = $pantryItems->filter(fn ($item) => $item->expires_at === null || Carbon::parse($item->expires_at)->isToday() || Carbon::parse($item->expires_at)->isFuture());
        if ($user !== null && $request->boolean('use_pantry')) {
            $selected = $availablePantryItems->pluck('ingredient.public_id')->all();
        }

        $results = $selected === [] ? collect() : $matching->findRecipes(
            $selected,
            $validated['excluded'] ?? [],
            $request->boolean('exact_only'),
            $request->boolean('include_substitutions', true),
            min(10, max(0, $request->integer('max_missing', 2))),
        );

        $searchFilters = collect($validated)->only(['min_protein', 'max_calories', 'dietary_tag', 'exclude_allergens', 'max_minutes', 'sort'])->filter(fn ($value) => $value !== null && $value !== [])->all();
        if ($request->boolean('verified_only')) {
            $searchFilters['verified_only'] = true;
        }
        if ($searchFilters !== [] && $results->isNotEmpty()) {
            $eligible = collect($search->search([...$searchFilters, 'per_page' => 50])->items())->keyBy('id');
            $results = $results->filter(fn (array $result) => $eligible->has($result['recipe']->id))->values();
            if (($searchFilters['sort'] ?? null) === 'time') {
                $results = $results->sortBy(fn (array $result) => $result['recipe']->totalMinutes())->values();
            }
        }

        $ingredients = Ingredient::dileGoreSirala(Ingredient::query()->yayindaKullanilan()->with('aliases')->get());

        return view('ingredients.explore', [
            'ingredients' => $ingredients,
            // Boş ekranda kullanıcıya somut bir başlangıç sunar: en çok tarifte geçen malzemeler.
            'popularIngredients' => Ingredient::query()
                ->yayindaKullanilan()
                ->withCount('recipeIngredients')
                ->orderByDesc('recipe_ingredients_count')
                ->orderBy('name')
                ->limit(10)
                ->get(),
            'allergens' => Allergen::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'dietaryTags' => DietaryTag::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'pantryItems' => $pantryItems,
            'availablePantryCount' => $availablePantryItems->count(),
            'expiredPantryCount' => $pantryItems->count() - $availablePantryItems->count(),
            'selected' => $selected,
            'results' => $results,
        ]);
    }
}
