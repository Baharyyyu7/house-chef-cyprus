<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Unit;
use App\Services\Measurements\IngredientConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class RecipeIngredientController extends Controller
{
    public function index(Recipe $recipe): View
    {
        return view('admin.recipes.ingredients', ['recipe' => $recipe->load('recipeIngredients.ingredient.unitConversions'), 'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(), 'units' => Unit::query()->where('is_active', true)->where('is_user_visible', true)->orderBy('sort_order')->get()]);
    }

    public function indexPublic(string $publicId): View
    {
        return $this->index(Recipe::query()->where('public_id', $publicId)->firstOrFail());
    }

    public function store(Request $request, Recipe $recipe, IngredientConversionService $conversions): RedirectResponse
    {
        $data = $this->validated($request);
        $data = $this->withCanonical($data, $conversions);
        DB::transaction(function () use ($recipe, $data): void {
            $recipe->recipeIngredients()->create($data + ['sort_order' => ((int) $recipe->recipeIngredients()->max('sort_order')) + 1]);
        });

        return back()->with('status', 'Tarif malzemesi eklendi.');
    }

    public function update(Request $request, Recipe $recipe, RecipeIngredient $line, IngredientConversionService $conversions): RedirectResponse
    {
        abort_unless($line->recipe_id === $recipe->id, 404);
        $data = $this->withCanonical($this->validated($request), $conversions);
        DB::transaction(fn () => $line->update($data));

        return back()->with('status', 'Tarif malzemesi güncellendi.');
    }

    public function destroy(Recipe $recipe, RecipeIngredient $line): RedirectResponse
    {
        abort_unless($line->recipe_id === $recipe->id, 404);
        DB::transaction(fn () => $line->delete());

        return back()->with('status', 'Tarif malzemesi kaldırıldı.');
    }

    public function reorder(Request $request, Recipe $recipe): RedirectResponse
    {
        $data = $request->validate(['items' => ['required', 'array', 'max:100'], 'items.*.public_id' => ['required', 'string'], 'items.*.group_name' => ['nullable', 'string', 'max:100'], 'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:9999']]);
        DB::transaction(function () use ($data, $recipe): void {
            foreach ($data['items'] as $item) {
                $line = $recipe->recipeIngredients()->where('public_id', $item['public_id'])->first();
                if ($line) {
                    $line->update(['group_name' => $item['group_name'] ?: null, 'sort_order' => $item['sort_order']]);
                }
            }
        });

        return back()->with('status', 'Grup ve sıralama güncellendi.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate(['ingredient_id' => ['required', 'exists:ingredients,id'], 'display_quantity' => ['required', 'numeric', 'gt:0', 'max:100000'], 'display_measure_code' => ['required', 'exists:units,code'], 'canonical_quantity' => ['nullable', 'numeric', 'gt:0', 'max:1000000'], 'canonical_unit' => ['nullable', 'in:g,ml,adet'], 'preparation_note' => ['nullable', 'string', 'max:255'], 'is_optional' => ['nullable', 'boolean'], 'group_name' => ['nullable', 'string', 'max:100'], 'scaling_behavior' => ['required', 'in:scalable,limited_scaling,non_scalable,taste_adjusted,manual_review'], 'scaling_note' => ['nullable', 'string', 'max:255'], 'manual_override' => ['nullable', 'boolean'], 'manual_override_reason' => ['nullable', 'string', 'max:255']]);
        $unit = Unit::query()->where('code', $data['display_measure_code'])->where('is_active', true)->where('is_user_visible', true)->first();
        abort_if($unit === null, 422, 'Pasif veya geçersiz ölçü seçilemez.');
        if (($data['manual_override'] ?? false) && empty($data['manual_override_reason'])) {
            abort(422, 'Manuel override nedeni zorunludur.');
        }

        return $data;
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private function withCanonical(array $data, IngredientConversionService $conversions): array
    {
        $ingredient = Ingredient::query()->findOrFail($data['ingredient_id']);
        $automatic = $conversions->resolve($ingredient, $data['display_measure_code'], $data['display_quantity']);
        $manual = (bool) ($data['manual_override'] ?? false);
        if (! $manual && $automatic['quantity'] === null) {
            abort(422, 'Bu ölçü için dönüşüm bulunamadı; canonical değer ve override nedeni girin.');
        }
        if ($manual && (empty($data['canonical_quantity']) || empty($data['canonical_unit']))) {
            abort(422, 'Manuel override için canonical miktar ve birim zorunludur.');
        }

        return [
            'ingredient_id' => $ingredient->id, 'quantity' => $data['display_quantity'], 'unit' => $data['display_measure_code'], 'display_quantity' => $data['display_quantity'], 'display_measure_code' => $data['display_measure_code'], 'canonical_quantity' => $manual ? $data['canonical_quantity'] : $automatic['quantity'], 'canonical_unit' => $manual ? $data['canonical_unit'] : $automatic['unit'], 'grams' => ($manual ? $data['canonical_unit'] : $automatic['unit']) === 'g' ? ($manual ? $data['canonical_quantity'] : $automatic['quantity']) : null, 'ingredient_unit_conversion_id' => $manual ? null : $automatic['conversion']?->id, 'conversion_confidence' => $manual ? 'medium' : $automatic['confidence'], 'conversion_method' => $manual ? 'manual_override' : $automatic['method'], 'manual_override_reason' => $data['manual_override_reason'] ?? null, 'preparation_note' => $data['preparation_note'] ?? null, 'is_optional' => (bool) ($data['is_optional'] ?? false), 'is_required' => ! (bool) ($data['is_optional'] ?? false), 'group_name' => $data['group_name'] ?? null, 'scaling_behavior' => $data['scaling_behavior'], 'scaling_note' => $data['scaling_note'] ?? null,
        ];
    }
}
