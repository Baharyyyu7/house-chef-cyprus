<?php

declare(strict_types=1);

namespace App\Services\Measurements;

use App\Models\RecipeIngredient;
use App\Models\Unit;

final class RecipeIngredientDisplayService
{
    public function __construct(private readonly HouseholdMeasurementFormatter $formatter) {}

    /** @return list<array{line: RecipeIngredient, group: string, formatted: string, display_quantity: string|null, measure_label: string, canonical_quantity: string|null, canonical_unit: string|null, is_approximate: bool, scaling_note: string|null}> */
    public function forRecipe(iterable $lines, int $baseServings, int $servings): array
    {
        $factor = bcdiv((string) max(1, $servings), (string) max(1, $baseServings), 4);

        return collect($lines)->sortBy('sort_order')->map(function (RecipeIngredient $line) use ($factor): array {
            $quantity = $line->display_quantity ?? $line->quantity;
            $scaled = $quantity === null ? null : bcmul((string) $quantity, $factor, 4);
            $unit = $line->display_measure_code ? Unit::query()->where('code', $line->display_measure_code)->first() : null;
            $display = trim(implode(' ', array_filter([$this->formatter->quantity($scaled, $unit), $this->formatter->measureLabel($unit, $scaled), $line->preparation_note ?: $line->display_note ?: $line->preparation, $line->ingredient?->name])));
            $canonical = $line->canonical_quantity ?? $line->grams;

            return ['line' => $line, 'group' => $line->group_name ?: 'Malzemeler', 'formatted' => $display, 'display_quantity' => $scaled, 'measure_label' => $this->formatter->measureLabel($unit, $scaled), 'canonical_quantity' => $canonical === null ? null : bcmul((string) $canonical, $factor, 4), 'canonical_unit' => $line->canonical_unit ?: ($line->grams !== null ? 'g' : null), 'is_approximate' => in_array($line->conversion_confidence, ['low', 'medium', 'unknown'], true), 'scaling_note' => $line->scaling_note];
        })->values()->all();
    }
}
