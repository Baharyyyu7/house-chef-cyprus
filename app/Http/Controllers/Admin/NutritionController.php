<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateRecipeNutritionJob;
use App\Models\Allergen;
use App\Models\AuditLog;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\IngredientAllergenDetail;
use App\Models\Nutrient;
use App\Models\NutritionRecalculationRun;
use App\Models\NutritionSetting;
use App\Models\Recipe;
use App\Models\RecipeAllergenOverride;
use App\Models\RecipeNutritionSnapshot;
use App\Services\NutritionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class NutritionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $relations = IngredientAllergenDetail::query()->with(['ingredient', 'allergen'])->when($request->ingredient_id, fn ($q, $v) => $q->where('ingredient_id', $v))->when($request->allergen_id, fn ($q, $v) => $q->where('allergen_id', $v))->when($request->relation_type, fn ($q, $v) => $q->where('relation_type', $v))->when($request->has('verified'), fn ($q) => $q->where('is_verified', $request->boolean('verified')))->get();

        return view('admin.nutrition.index', ['nutrients' => Nutrient::query()->orderBy('sort_order')->get(), 'allergens' => Allergen::query()->orderBy('sort_order')->get(), 'tags' => DietaryTag::query()->orderBy('sort_order')->get(), 'ingredients' => Ingredient::query()->withCount('nutritionFacts')->orderBy('name')->get(), 'recipes' => Recipe::query()->withCount('nutritionSnapshots')->orderBy('title')->get(), 'relations' => $relations, 'overrides' => RecipeAllergenOverride::query()->with(['allergen'])->get(), 'settings' => NutritionSetting::query()->orderBy('key')->get(), 'runs' => NutritionRecalculationRun::query()->with('recipe')->latest()->limit(20)->get(), 'snapshots' => RecipeNutritionSnapshot::query()->with('recipe')->latest('calculated_at')->get()]);
    }

    public function snapshot(RecipeNutritionSnapshot $snapshot): View
    {
        $this->authorizeAdmin();

        return view('admin.nutrition.snapshot', compact('snapshot'));
    }

    public function history(): View
    {
        $this->authorizeAdmin();

        return view('admin.nutrition.history', ['runs' => NutritionRecalculationRun::query()->with('recipe')->latest()->paginate(50)]);
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

    public function saveRelation(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['ingredient_id' => ['required', 'exists:ingredients,id'], 'allergen_id' => ['required', 'exists:allergens,id'], 'relation_type' => ['required', 'in:contains,may_contain,cross_contamination_risk,derived_from'], 'confidence_score' => ['required', 'integer', 'between:0,100'], 'source_type' => ['required', 'string', 'max:30'], 'source_name' => ['nullable', 'string', 'max:255'], 'is_verified' => ['nullable', 'boolean'], 'notes' => ['nullable', 'string']]);
        if (($data['is_verified'] ?? false) && empty($data['source_name'])) {
            return back()->withErrors(['source_name' => 'Doğrulanmış ilişki için kaynak zorunludur.']);
        } IngredientAllergenDetail::query()->updateOrCreate(['ingredient_id' => $data['ingredient_id'], 'allergen_id' => $data['allergen_id']], $data);
        $this->audit('nutrition.ingredient_allergen.saved', (object) ['id' => $data['ingredient_id']]);

        return back()->with('status', 'Malzeme alerjen ilişkisi kaydedildi.');
    }

    public function toggleRelation(IngredientAllergenDetail $relation): RedirectResponse
    {
        $this->authorizeAdmin();
        $relation->update(['is_active' => ! $relation->is_active]);
        $this->audit('nutrition.ingredient_allergen.toggled', $relation);

        return back()->with('status', $relation->is_active ? 'İlişki yeniden etkinleştirildi.' : 'İlişki pasif yapıldı.');
    }

    public function saveOverride(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['recipe_id' => ['required', 'exists:recipes,id'], 'allergen_id' => ['required', 'exists:allergens,id'], 'risk_type' => ['required', 'in:contains,does_not_contain,may_contain,uncertain'], 'derived_from' => ['required', 'string', 'max:255'], 'source_type' => ['required', 'string', 'max:30'], 'source_name' => ['nullable', 'string', 'max:255'], 'is_verified' => ['nullable', 'boolean'], 'notes' => ['nullable', 'string']]);
        RecipeAllergenOverride::query()->updateOrCreate(['recipe_id' => $data['recipe_id'], 'allergen_id' => $data['allergen_id']], $data + ['is_active' => true, 'updated_by' => auth()->id(), 'updated_at' => now()]);
        $recipe = Recipe::findOrFail($data['recipe_id']);
        app(NutritionService::class)->invalidate($recipe);
        $this->audit('nutrition.recipe_allergen_override.saved', $recipe);

        return back()->with('status', 'Tarif alerjen override kaydedildi.');
    }

    public function toggleOverride(RecipeAllergenOverride $override): RedirectResponse
    {
        $this->authorizeAdmin();
        $override->update(['is_active' => ! $override->is_active, 'updated_by' => auth()->id(), 'updated_at' => now()]);
        $this->audit('nutrition.recipe_allergen_override.toggled', $override);

        return back()->with('status', $override->is_active ? 'Override yeniden etkinleştirildi.' : 'Override pasif yapıldı.');
    }

    public function saveSetting(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validate(['key' => ['required', 'in:high_protein_min_grams_per_serving,low_calorie_max_kcal_per_serving,ketogenic_max_net_carbs_per_serving,ketogenic_min_fat_ratio,confidence_required_for_verified_result,completeness_required_for_verified_result'], 'value' => ['required', 'decimal:0,4', 'min:0'], 'unit' => ['nullable', 'string', 'max:20']]);
        NutritionSetting::query()->updateOrCreate(['key' => $data['key']], $data);
        $this->audit('nutrition.setting.saved', (object) ['id' => 0]);

        return back()->with('status', 'Beslenme eşiği kaydedildi.');
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
        $run = NutritionRecalculationRun::query()->create(['recipe_id' => $recipe->id, 'triggered_by' => auth()->id(), 'status' => 'pending']);
        RecalculateRecipeNutritionJob::dispatch($recipe->public_id, $run->id);
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
