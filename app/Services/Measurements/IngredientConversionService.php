<?php

declare(strict_types=1);

namespace App\Services\Measurements;

use App\Models\Ingredient;
use App\Models\IngredientUnitConversion;

final class IngredientConversionService
{
    /** @return array{quantity:?string,unit:?string,confidence:string,method:string,verified:bool,conversion:?IngredientUnitConversion} */
    public function resolve(Ingredient $ingredient, string $measureCode, string|int|float $quantity): array
    {
        if (bccomp((string) $quantity, '0', 4) < 0) {
            throw new \InvalidArgumentException('Miktar negatif olamaz.');
        }
        $conversion = $ingredient->unitConversions()->where('household_measure_code', $measureCode)->where('is_active', true)->orderByDesc('confidence_score')->first();
        if ($conversion === null || bccomp((string) $conversion->household_quantity, '0', 4) <= 0) {
            return ['quantity' => null, 'unit' => null, 'confidence' => 'unknown', 'method' => 'not_convertible', 'verified' => false, 'conversion' => null];
        }
        $result = bcdiv(bcmul((string) $conversion->canonical_quantity, (string) $quantity, 6), (string) $conversion->household_quantity, 4);

        return ['quantity' => $result, 'unit' => $conversion->canonical_unit, 'confidence' => $conversion->verification_status, 'method' => $conversion->conversion_method, 'verified' => $conversion->verification_status === 'verified', 'conversion' => $conversion];
    }
}
