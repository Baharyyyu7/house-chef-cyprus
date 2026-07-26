<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePantryItemRequest;
use App\Models\Ingredient;
use App\Models\PantryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class PantryController extends Controller
{
    public function index(): View
    {
        return view('pantry.index', ['items' => auth()->user()->pantryItems()->with('ingredient')->orderBy('expires_at')->get(), 'ingredients' => Ingredient::orderBy('name')->get()]);
    }

    public function store(StorePantryItemRequest $request): RedirectResponse
    {
        auth()->user()->pantryItems()->updateOrCreate(['ingredient_id' => $request->integer('ingredient_id')], $request->safe()->except('ingredient_id'));

        return back()->with('status', 'Malzeme dolaba eklendi.');
    }

    public function destroy(PantryItem $pantryItem): RedirectResponse
    {
        abort_unless($pantryItem->user_id === auth()->id(), 403);
        $pantryItem->delete();

        return back()->with('status', 'Malzeme kaldırıldı.');
    }
}
