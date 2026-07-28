<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientUnitConversion;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class MeasurementController extends Controller
{
    public function units(Request $request): View
    {
        $units = Unit::query()
            ->when($request->string('q')->toString(), fn ($query, $q) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$q}%")->orWhere('singular_name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))
            ->when($request->string('category')->toString(), fn ($query, $value) => $query->where('unit_type', $value))
            ->when($request->filled('fractions'), fn ($query) => $query->where('supports_fractions', $request->boolean('fractions')))
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->when($request->filled('canonical'), fn ($query) => $query->where('is_canonical', $request->boolean('canonical')))
            ->when($request->filled('visible'), fn ($query) => $query->where('is_user_visible', $request->boolean('visible')))
            ->orderBy('sort_order')->paginate(30)->withQueryString();

        return view('admin.measurements.units', compact('units'));
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        Unit::query()->create($this->validatedUnit($request) + ['public_id' => (string) Str::ulid()]);

        return back()->with('status', 'Ölçü birimi kaydedildi.');
    }

    public function updateUnit(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validatedUnit($request, $unit));

        return back()->with('status', 'Ölçü birimi güncellendi.');
    }

    public function toggleUnit(Unit $unit): RedirectResponse
    {
        $unit->update(['is_active' => ! $unit->is_active]);

        return back()->with('status', $unit->is_active ? 'Ölçü birimi aktifleştirildi.' : 'Ölçü birimi pasifleştirildi.');
    }

    public function conversions(Request $request): View
    {
        $conversions = IngredientUnitConversion::query()->with('ingredient')
            ->when($request->integer('ingredient_id'), fn ($query, $id) => $query->where('ingredient_id', $id))
            ->when($request->string('measure')->toString(), fn ($query, $measure) => $query->where('household_measure_code', $measure))
            ->when($request->string('verification')->toString(), fn ($query, $value) => $query->where('verification_status', $value))
            ->when($request->string('source_type')->toString(), fn ($query, $value) => $query->where('source_type', $value))
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->when($request->filled('confidence'), fn ($query) => $query->where('confidence_score', '<=', $request->integer('confidence')))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.measurements.conversions', ['conversions' => $conversions, 'ingredients' => Ingredient::query()->orderBy('name')->get(), 'units' => Unit::query()->where('is_user_visible', true)->where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        IngredientUnitConversion::query()->create($data + ['public_id' => (string) Str::ulid()]);

        return back()->with('status', 'Malzeme dönüşümü kaydedildi.');
    }

    public function update(Request $request, IngredientUnitConversion $conversion): RedirectResponse
    {
        $conversion->update($this->validated($request));

        return back()->with('status', 'Malzeme dönüşümü güncellendi.');
    }

    public function toggle(IngredientUnitConversion $conversion): RedirectResponse
    {
        $conversion->update(['is_active' => ! $conversion->is_active]);

        return back()->with('status', $conversion->is_active ? 'Dönüşüm aktifleştirildi.' : 'Dönüşüm pasifleştirildi.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate(['ingredient_id' => ['required', 'exists:ingredients,id'], 'household_measure_code' => ['required', 'exists:units,code'], 'household_quantity' => ['required', 'numeric', 'gt:0'], 'canonical_quantity' => ['required', 'numeric', 'gt:0'], 'canonical_unit' => ['required', 'in:g,ml,adet'], 'density_g_per_ml' => ['nullable', 'numeric', 'gt:0'], 'source_type' => ['required', 'in:seed_demo,editorial_estimate,editorial,official,manual'], 'source_name' => ['required', 'string', 'max:255'], 'source_url' => ['nullable', 'url', 'max:2048'], 'verification_status' => ['required', 'in:verified,high,medium,low,unknown'], 'confidence_score' => ['required', 'integer', 'min:0', 'max:100'], 'conversion_method' => ['required', 'in:exact,ingredient_specific,density_based,generic_estimate,manual_override,not_convertible'], 'notes' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean']]);
    }

    /** @return array<string, mixed> */
    private function validatedUnit(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'code' => ['required', 'alpha_dash', 'max:40', 'unique:units,code'.($unit ? ','.$unit->id : '')],
            'singular_name' => ['required', 'string', 'max:120'],
            'plural_name' => ['nullable', 'string', 'max:120'],
            'short_label' => ['nullable', 'string', 'max:30'],
            'unit_type' => ['required', 'in:mass,volume,count,household,other'],
            'precision' => ['required', 'integer', 'between:0,6'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'supports_fractions' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_user_visible' => ['nullable', 'boolean'],
            'is_canonical' => ['nullable', 'boolean'],
        ]) + ['name' => $request->string('singular_name')->toString()];
    }
}
