<?php

declare(strict_types=1);

namespace App\Services\Measurements;

use App\Models\Unit;

final class HouseholdMeasurementFormatter
{
    public function quantity(string|int|float|null $quantity, ?Unit $unit = null): string
    {
        if ($quantity === null || bccomp((string) $quantity, '0', 4) === 0) {
            return '';
        }

        $precision = $unit === null ? 4 : $unit->precision;
        $value = rtrim(rtrim(number_format((float) $quantity, $precision, '.', ''), '0'), '.');
        $whole = (int) floor((float) $value);
        $fraction = (float) $value - $whole;
        $label = match (round($fraction, 2)) {
            0.25 => 'çeyrek', 0.5 => 'yarım', 0.75 => 'üç çeyrek', default => null,
        };

        if ($unit?->supports_fractions && $label !== null) {
            return $whole === 0 ? ucfirst($label) : $whole.' '.$label;
        }

        return str_replace('.', ',', $value);
    }

    public function measureLabel(Unit|string|null $unit, string|int|float|null $quantity = null): string
    {
        if (is_string($unit)) {
            return $unit;
        }
        if ($unit === null) {
            return '';
        }
        $isPlural = $quantity !== null && bccomp((string) $quantity, '1', 4) !== 0;

        return $isPlural ? ($unit->plural_name ?: $unit->name) : ($unit->singular_name ?: $unit->name);
    }
}
