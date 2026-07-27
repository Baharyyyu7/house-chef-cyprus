<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientGroup;
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
        return view('admin.recipes.ingredients', ['recipe' => $recipe->load(['recipeIngredients.ingredient.unitConversions', 'ingredientGroups.ingredients.ingredient']), 'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(), 'units' => Unit::query()->where('is_active', true)->where('is_user_visible', true)->orderBy('sort_order')->get()]);
    }

    public function indexPublic(string $publicId): View
    {
        return $this->index(Recipe::query()->where('public_id', $publicId)->firstOrFail());
    }

    public function store(Request $request, Recipe $recipe, IngredientConversionService $conversions): RedirectResponse
    {
        $data = $this->validated($request);
        $data = $this->withCanonical($data, $conversions, $recipe);
        DB::transaction(function () use ($recipe, $data): void {
            $recipe->recipeIngredients()->create($data + ['sort_order' => ((int) $recipe->recipeIngredients()->max('sort_order')) + 1]);
        });

        return back()->with('status', 'Tarif malzemesi eklendi.');
    }

    public function update(Request $request, Recipe $recipe, RecipeIngredient $line, IngredientConversionService $conversions): RedirectResponse
    {
        abort_unless($line->recipe_id === $recipe->id, 404);
        $data = $this->withCanonical($this->validated($request), $conversions, $recipe);
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

    public function storeGroup(Request $request, Recipe $recipe): RedirectResponse
    {
        $name = trim((string) $request->validate(['name' => ['required', 'string', 'max:100']])['name']);
        abort_if($name === '', 422, 'Grup adı boş olamaz.');
        DB::transaction(function () use ($recipe, $name): void {
            $group = $recipe->ingredientGroups()->firstOrCreate(['name' => $name], ['sort_order' => ((int) $recipe->ingredientGroups()->max('sort_order')) + 1]);
            $this->audit('recipe_ingredient_group.saved', $group);
        });

        return back()->with('status', 'Grup kaydedildi.');
    }

    public function updateGroup(Request $request, Recipe $recipe, RecipeIngredientGroup $group): RedirectResponse
    {
        abort_unless($group->recipe_id === $recipe->id, 404);
        $name = trim((string) $request->validate(['name' => ['required', 'string', 'max:100']])['name']);
        abort_if($name === '', 422, 'Grup adı boş olamaz.');
        if ($recipe->ingredientGroups()->where('name', $name)->whereKeyNot($group->id)->exists()) {
            return back()->withErrors(['name' => 'Bu isimde bir malzeme grubu zaten var.']);
        }
        DB::transaction(function () use ($group, $name): void {
            $group->update(['name' => $name]);
            $this->audit('recipe_ingredient_group.updated', $group);
        });

        return back()->with('status', 'Grup adı güncellendi.');
    }

    public function destroyGroup(Recipe $recipe, RecipeIngredientGroup $group): RedirectResponse
    {
        abort_unless($group->recipe_id === $recipe->id, 404);
        DB::transaction(function () use ($recipe, $group): void {
            $default = $recipe->ingredientGroups()->firstOrCreate(['name' => 'Malzemeler'], ['sort_order' => 0]);
            $group->ingredients()->update(['recipe_ingredient_group_id' => $default->id, 'group_name' => $default->name]);
            if ($group->id !== $default->id) {
                $group->delete();
                $this->audit('recipe_ingredient_group.deleted', $group);
            }
        });

        return back()->with('status', 'Grup silindi; malzemeler varsayılan gruba taşındı.');
    }

    public function reorderGroup(Request $request, Recipe $recipe, RecipeIngredientGroup $group): RedirectResponse
    {
        abort_unless($group->recipe_id === $recipe->id, 404);
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        DB::transaction(function () use ($direction, $recipe, $group): void {
            $target = $recipe->ingredientGroups()
                ->when($direction === 'up', fn ($query) => $query->where('sort_order', '<', $group->sort_order)->orderByDesc('sort_order'), fn ($query) => $query->where('sort_order', '>', $group->sort_order)->orderBy('sort_order'))
                ->first();
            if ($target !== null) {
                [$groupOrder, $targetOrder] = [$group->sort_order, $target->sort_order];
                $group->update(['sort_order' => $targetOrder]);
                $target->update(['sort_order' => $groupOrder]);
            }
            $this->audit('recipe_ingredient_group.reordered', $group);
        });

        return back()->with('status', 'Grup sırası güncellendi.');
    }

    public function move(Request $request, Recipe $recipe, RecipeIngredient $line): RedirectResponse
    {
        abort_unless($line->recipe_id === $recipe->id, 404);
        $data = $request->validate(['group_public_id' => ['nullable', 'string', 'max:26'], 'direction' => ['nullable', 'in:up,down']]);
        DB::transaction(function () use ($data, $recipe, $line): void {
            if (! empty($data['group_public_id'])) {
                $group = $recipe->ingredientGroups()->where('public_id', $data['group_public_id'])->firstOrFail();
                $line->update(['recipe_ingredient_group_id' => $group->id, 'group_name' => $group->name]);
                $this->audit('recipe_ingredient.moved', $line);
            } if (! empty($data['direction'])) {
                $target = $recipe->recipeIngredients()->where('recipe_ingredient_group_id', $line->recipe_ingredient_group_id)->when($data['direction'] === 'up', fn ($q) => $q->where('sort_order', '<', $line->sort_order)->orderByDesc('sort_order'), fn ($q) => $q->where('sort_order', '>', $line->sort_order)->orderBy('sort_order'))->first();
                if ($target) {
                    $old = $line->sort_order;
                    $line->update(['sort_order' => $target->sort_order]);
                    $target->update(['sort_order' => $old]);
                }
                $this->audit('recipe_ingredient.reordered', $line);
            }
        });

        return back()->with('status', 'Malzeme grubu veya sırası güncellendi.');
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
    private function withCanonical(array $data, IngredientConversionService $conversions, Recipe $recipe): array
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

        $groupName = trim((string) ($data['group_name'] ?? '')) ?: 'Malzemeler';
        $group = $recipe->ingredientGroups()->firstOrCreate(['name' => $groupName], ['sort_order' => ((int) $recipe->ingredientGroups()->max('sort_order')) + 1]);

        return [
            'ingredient_id' => $ingredient->id, 'quantity' => $data['display_quantity'], 'unit' => $data['display_measure_code'], 'display_quantity' => $data['display_quantity'], 'display_measure_code' => $data['display_measure_code'], 'canonical_quantity' => $manual ? $data['canonical_quantity'] : $automatic['quantity'], 'canonical_unit' => $manual ? $data['canonical_unit'] : $automatic['unit'], 'grams' => ($manual ? $data['canonical_unit'] : $automatic['unit']) === 'g' ? ($manual ? $data['canonical_quantity'] : $automatic['quantity']) : null, 'ingredient_unit_conversion_id' => $manual ? null : $automatic['conversion']?->id, 'conversion_confidence' => $manual ? 'medium' : $automatic['confidence'], 'conversion_method' => $manual ? 'manual_override' : $automatic['method'], 'manual_override_reason' => $data['manual_override_reason'] ?? null, 'preparation_note' => $data['preparation_note'] ?? null, 'is_optional' => (bool) ($data['is_optional'] ?? false), 'is_required' => ! (bool) ($data['is_optional'] ?? false), 'recipe_ingredient_group_id' => $group->id, 'group_name' => $group->name, 'scaling_behavior' => $data['scaling_behavior'], 'scaling_note' => $data['scaling_note'] ?? null,
        ];
    }

    private function audit(string $action, object $model): void
    {
        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->id,
            'metadata' => ['public_id' => $model->public_id ?? null],
        ]);
    }
}
