<?php

declare(strict_types=1);

namespace App\Services\Measurements;

use App\Models\Unit;
use Illuminate\Translation\MessageSelector;

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

        if (! $this->turkce()) {
            // "yarım" ve "çeyrek" yabancı sayfada Türkçe kırıntı olarak
            // kalıyordu; kesir işareti her dilde okunur.
            if ($unit?->supports_fractions && $label !== null) {
                $isaret = ['çeyrek' => '¼', 'yarım' => '½', 'üç çeyrek' => '¾'][$label];

                return $whole === 0 ? $isaret : $whole.' '.$isaret;
            }

            return app()->getLocale() === 'en' ? $value : str_replace('.', ',', $value);
        }

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
        $yabanci = $this->turkce() ? null : ($this->sozluk('birimler')[$unit->code] ?? null);

        if ($yabanci !== null) {
            return $yabanci === '' ? '' : (new MessageSelector)->choose($yabanci, $this->cogulSayisi($quantity), app()->getLocale());
        }

        return $isPlural ? ($unit->plural_name ?: $unit->name) : ($unit->singular_name ?: $unit->name);
    }

    /**
     * Hazırlık notunun o dildeki karşılığı; sözlükte yoksa null.
     *
     * Not ("rendelenmiş", "halka doğranmış") tarif satırında Türkçe
     * duruyordu ve yabancı sayfada "4 adet rendelenmiş tomatoes" diye
     * basılıyordu. Karşılığı olmayan not yabancı dilde hiç basılmaz.
     */
    public function hazirlik(?string $not): ?string
    {
        $not = trim((string) $not);

        if ($not === '' || $this->turkce()) {
            return $not === '' ? null : $not;
        }

        return $this->sozluk('hazirlik')[mb_strtolower($not)] ?? null;
    }

    private function turkce(): bool
    {
        return app()->getLocale() === (string) config('locales.default', 'tr');
    }

    /** @return array<string, string> */
    private function sozluk(string $grup): array
    {
        $satirlar = trans($grup);

        return is_array($satirlar) ? $satirlar : [];
    }

    /**
     * Kesirli miktar Lehçe ve Rusçada ikinci biçimi alır ("1 ½ łyżki",
     * "½ стакана"); İngilizce, Almanca ve Portekizcede birden küçükse
     * tekil, büyükse çoğul.
     */
    private function cogulSayisi(string|int|float|null $miktar): int
    {
        $sayi = $miktar === null ? 1.0 : (float) $miktar;

        if (floor($sayi) === $sayi) {
            return (int) $sayi;
        }

        return $sayi < 1 && ! in_array(app()->getLocale(), ['pl', 'ru'], true) ? 1 : 2;
    }
}
