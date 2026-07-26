<?php

namespace App\Services\Units;

use App\Models\Unit;
use DomainException;

final class UnitConversionService
{
    public function convert(string $amount, Unit $from, Unit $to, int $scale = 3): string
    {
        if (bccomp($amount, '0', $scale) <= 0) {
            throw new DomainException('Miktar sıfırdan büyük olmalı.');
        } if ($from->unit_type !== $to->unit_type || ! $from->conversion_factor || ! $to->conversion_factor) {
            throw new DomainException('Uyumsuz veya güvenilir olmayan birim dönüşümü.');
        }

return bcdiv(bcmul($amount, (string) $from->conversion_factor, $scale + 4), (string) $to->conversion_factor, $scale);
    }
}
