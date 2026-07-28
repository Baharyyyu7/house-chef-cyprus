<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientSubstitution;
use App\Services\IngredientAliasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $ingredients = Ingredient::query()
            ->withCount(['aliases', 'substitutions', 'unitConversions', 'nutritionFacts', 'allergenDetails'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$request->string('q')->toString().'%')
                ->orWhere('canonical_name', 'like', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('admin.ingredients.index', [
            'ingredients' => $ingredients,
            'categories' => Ingredient::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.edit', ['ingredient' => $ingredient->load(['aliases', 'substitutions.substitute']), 'ingredients' => Ingredient::query()->whereKeyNot($ingredient->id)->orderBy('name')->get()]);
    }

    public function editPublic(string $publicId): View
    {
        return $this->edit(Ingredient::query()->where('public_id', $publicId)->firstOrFail());
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update($request->validate(['category' => ['nullable', 'string', 'max:100'], 'is_active' => ['nullable', 'boolean'], 'is_perishable' => ['nullable', 'boolean'], 'typical_shelf_life_days' => ['nullable', 'integer', 'min:0'], 'storage_type' => ['nullable', 'in:pantry,fridge,freezer,other'], 'notes' => ['nullable', 'string']]));

        return back()->with('status', 'Malzeme güncellendi.');
    }

    public function storeAlias(Request $request, Ingredient $ingredient, IngredientAliasService $aliases): RedirectResponse
    {
        $data = $request->validate(['alias' => ['required', 'string', 'max:255']]);
        $aliases->add($ingredient, $data['alias']);

        return back()->with('status', 'Alias eklendi.');
    }

    public function storeSubstitution(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $data = $request->validate(['substitute_ingredient_id' => ['required', 'exists:ingredients,id', 'different:ingredient_id'], 'substitution_type' => ['required', 'in:direct,flavor_similar,texture_similar,dietary,emergency'], 'ratio' => ['required', 'numeric', 'gt:0'], 'confidence_score' => ['required', 'numeric', 'min:0', 'max:100'], 'is_verified' => ['nullable', 'boolean']]);
        abort_if((int) $data['substitute_ingredient_id'] === $ingredient->id, 422);
        IngredientSubstitution::query()->updateOrCreate(['ingredient_id' => $ingredient->id, 'substitute_ingredient_id' => $data['substitute_ingredient_id'], 'substitution_type' => $data['substitution_type']], $data + ['is_active' => true]);

        return back()->with('status', 'Alternatif eklendi.');
    }
}
