<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePantryItemRequest;
use App\Models\Ingredient;
use App\Models\PantryItem;
use App\Models\Unit;
use App\Services\Measurements\IngredientConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class PantryController extends Controller
{
    public function index(): View
    {
        $editing = request()->integer('edit');

        return view('pantry.index', [
            'items' => auth()->user()->pantryItems()->with('ingredient')->orderBy('expires_at')->get(),
            'ingredients' => Ingredient::orderBy('name')->get(),
            'units' => Unit::query()->where('is_active', true)->where('is_user_visible', true)->orderBy('sort_order')->get(),
            'editingItem' => $editing > 0 ? auth()->user()->pantryItems()->find($editing) : null,
        ]);
    }

    public function store(StorePantryItemRequest $request, IngredientConversionService $conversions): RedirectResponse
    {
        $data = $request->safe()->except('ingredient_id');
        $ingredient = Ingredient::query()->findOrFail($request->integer('ingredient_id'));
        if ($request->filled('quantity') && $request->filled('unit')) {
            $resolved = $conversions->resolve($ingredient, (string) $request->input('unit'), (string) $request->input('quantity'));
            if ($resolved['quantity'] !== null) {
                $data += ['canonical_quantity' => $resolved['quantity'], 'canonical_unit' => $resolved['unit'], 'conversion_confidence' => $resolved['confidence']];
            } else {
                $data += ['canonical_quantity' => null, 'canonical_unit' => null, 'conversion_confidence' => 'unknown'];
            }
        }
        auth()->user()->pantryItems()->updateOrCreate(['ingredient_id' => $request->integer('ingredient_id')], $data);

        return redirect()->route('pantry.index')->with('status', 'Malzeme dolabınız güncellendi.');
    }

    public function destroy(PantryItem $pantryItem): RedirectResponse
    {
        abort_unless($pantryItem->user_id === auth()->id(), 403);
        $pantryItem->delete();

        return back()->with('status', 'Malzeme kaldırıldı.');
    }
}
