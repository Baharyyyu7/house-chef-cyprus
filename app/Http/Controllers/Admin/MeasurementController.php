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
        $units = Unit::query()->when($request->string('q')->toString(), fn ($query, $q) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))->orderBy('sort_order')->paginate(30)->withQueryString();

        return view('admin.measurements.units', compact('units'));
    }

    public function conversions(Request $request): View
    {
        $conversions = IngredientUnitConversion::query()->with('ingredient')->when($request->integer('ingredient_id'), fn ($query, $id) => $query->where('ingredient_id', $id))->when($request->string('measure')->toString(), fn ($query, $measure) => $query->where('household_measure_code', $measure))->latest()->paginate(30)->withQueryString();

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
        return $request->validate(['ingredient_id' => ['required', 'exists:ingredients,id'], 'household_measure_code' => ['required', 'exists:units,code'], 'household_quantity' => ['required', 'numeric', 'gt:0'], 'canonical_quantity' => ['required', 'numeric', 'gt:0'], 'canonical_unit' => ['required', 'in:g,ml,adet'], 'source_type' => ['required', 'in:seed_demo,editorial_estimate,editorial,official,manual'], 'source_name' => ['required', 'string', 'max:255'], 'source_url' => ['nullable', 'url', 'max:2048'], 'verification_status' => ['required', 'in:verified,high,medium,low,unknown'], 'confidence_score' => ['required', 'integer', 'min:0', 'max:100'], 'conversion_method' => ['required', 'in:exact,ingredient_specific,density_based,generic_estimate,manual_override,not_convertible'], 'notes' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean']]);
    }
}
