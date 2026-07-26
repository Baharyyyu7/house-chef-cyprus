<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateRecipeNutritionJob;
use App\Models\Allergen;
use App\Models\AuditLog;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class NutritionController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.nutrition.index', ['nutrients' => Nutrient::query()->orderBy('sort_order')->get(), 'allergens' => Allergen::query()->orderBy('sort_order')->get(), 'tags' => DietaryTag::query()->orderBy('sort_order')->get(), 'ingredients' => Ingredient::query()->withCount('nutritionFacts')->orderBy('name')->get(), 'recipes' => Recipe::query()->withCount('nutritionSnapshots')->orderBy('title')->get()]);
    }

    public function saveNutrient(Request $request, ?Nutrient $nutrient = null): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['code' => ['required', 'alpha_dash', 'max:80', Rule::unique('nutrients', 'code')->ignore($nutrient)], 'name' => ['required', 'string', 'max:120'], 'category' => ['nullable', 'string', 'max:50'], 'unit' => ['required', 'string', 'max:20'], 'decimal_precision' => ['required', 'integer', 'between:0,6'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']]);
        $nutrient ??= new Nutrient(['public_id' => (string) Str::ulid()]);
        $nutrient->fill($data)->save();
        $this->audit('nutrition.nutrient.saved', $nutrient);

        return back()->with('status', 'Besin öğesi kaydedildi.');
    }

    public function saveAllergen(Request $request, ?Allergen $allergen = null): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['code' => ['required', 'alpha_dash', 'max:80', Rule::unique('allergens', 'code')->ignore($allergen)], 'name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']]);
        $allergen ??= new Allergen(['public_id' => (string) Str::ulid()]);
        $allergen->fill($data)->save();
        $this->audit('nutrition.allergen.saved', $allergen);

        return back()->with('status', 'Alerjen kaydedildi.');
    }

    public function saveDietaryTag(Request $request, ?DietaryTag $tag = null): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['code' => ['required', 'alpha_dash', 'max:80', Rule::unique('dietary_tags', 'code')->ignore($tag)], 'name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string'], 'rule_type' => ['required', 'in:manual,threshold,derived'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']]);
        $tag ??= new DietaryTag(['public_id' => (string) Str::ulid()]);
        $tag->fill($data)->save();
        $this->audit('nutrition.dietary_tag.saved', $tag);

        return back()->with('status', 'Diyet etiketi kaydedildi.');
    }

    public function saveFact(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['nutrient_id' => ['required', 'exists:nutrients,id'], 'value' => ['required', 'decimal:0,4', 'min:0'], 'basis_type' => ['required', 'in:per_100g,per_100ml,per_count,per_portion'], 'basis_amount' => ['required', 'decimal:0,3', 'gt:0'], 'source_type' => ['required', 'in:editorial,imported,calculated,ai_estimated'], 'source_name' => ['nullable', 'string', 'max:255'], 'confidence_score' => ['required', 'integer', 'between:0,100'], 'origin' => ['required', 'in:verified,calculated,imported,editorial,ai_estimated'], 'verified_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string']]);
        if ($data['origin'] === 'verified' && (! data_get($data, 'source_name') || ! data_get($data, 'verified_at'))) {
            return back()->withErrors(['source_name' => 'Doğrulanmış veri için kaynak ve doğrulama tarihi zorunludur.']);
        }
        $ingredient->nutritionFacts()->updateOrCreate(['nutrient_id' => $data['nutrient_id'], 'basis_type' => $data['basis_type'], 'basis_amount' => $data['basis_amount'], 'source_type' => $data['source_type']], $data);
        $this->audit('nutrition.fact.saved', $ingredient);

        return back()->with('status', 'Besin değeri kaydedildi.');
    }

    public function invalidate(Request $request, Recipe $recipe, NutritionService $nutrition): RedirectResponse
    {
        $this->authorizeAdmin();
        $nutrition->invalidate($recipe);
        $this->audit('nutrition.snapshot.invalidated', $recipe);

        return back()->with('status', 'Snapshot geçersiz kılındı.');
    }

    public function recalculate(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorizeAdmin();
        RecalculateRecipeNutritionJob::dispatch($recipe->id);
        $this->audit('nutrition.recalculation.queued', $recipe);

        return back()->with('status', 'Yeniden hesaplama sıraya alındı.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    private function audit(string $action, object $model): void
    {
        AuditLog::query()->create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $model::class, 'auditable_id' => $model->id, 'metadata' => ['at' => now()->toIso8601String()]]);
    }
}
