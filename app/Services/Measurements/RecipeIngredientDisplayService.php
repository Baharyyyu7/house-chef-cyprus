<?php

declare(strict_types=1);

namespace App\Services\Measurements;

use App\Models\RecipeIngredient;
use App\Models\Unit;

final class RecipeIngredientDisplayService
{
    /** @var array<string, Unit|null> Porsiyon haritası aynı satırları 20 kez biçimliyor; birim bir kez okunur. */
    private array $birimler = [];

    public function __construct(private readonly HouseholdMeasurementFormatter $formatter) {}

    /** @return list<array{line: RecipeIngredient, group: string, group_label: string|null, formatted: string, display_quantity: string|null, measure_label: string, canonical_quantity: string|null, canonical_unit: string|null, is_approximate: bool, scaling_note: string|null, measure_code: string|null, canonical_text: string}> */
    public function forRecipe(iterable $lines, int $baseServings, int $servings): array
    {
        $factor = bcdiv((string) max(1, $servings), (string) max(1, $baseServings), 4);

        return collect($lines)->sortBy(fn (RecipeIngredient $line): string => sprintf('%05d-%05d', $line->recipe_ingredient_group_id === null ? 0 : (int) $line->group->sort_order, (int) $line->sort_order))->map(function (RecipeIngredient $line) use ($factor): array {
            $quantity = $line->display_quantity ?? $line->quantity;
            $scaled = $quantity === null ? null : bcmul((string) $quantity, $factor, 4);
            $unit = $line->display_measure_code ? ($this->birimler[$line->display_measure_code] ??= Unit::query()->where('code', $line->display_measure_code)->first()) : null;
            $etiket = $this->formatter->measureLabel($unit, $scaled);
            $ad = $this->formatter->adSayiyla((string) $line->ingredient?->name, $scaled, $etiket);
            // Yabancı dilde not adın arkasına virgülle gelir: sıfat olarak
            // başa konsa Almanca, Lehçe, Rusça ve Portekizcede adla cinsiyet
            // ve sayı uyumu gerekirdi ("4 Tomaten, gerieben"). Portekizce not
            // virgülden sonra da uyar ("1 cebola, picada"), o yüzden ad verilir.
            $hazirlik = $this->formatter->hazirlik($line->preparation_note ?: $line->display_note ?: $line->preparation, $ad);
            $display = app()->getLocale() === (string) config('locales.default', 'tr')
                ? trim(implode(' ', array_filter([$this->formatter->quantity($scaled, $unit), $etiket, $hazirlik, $line->ingredient?->name])))
                : $this->formatter->yabanciSatir(trim(implode(' ', array_filter([$this->formatter->quantity($scaled, $unit), $etiket]))), $ad, $etiket !== '').($hazirlik === null ? '' : (app()->getLocale() === 'fa' ? '، ' : ', ').$hazirlik);
            $canonical = $line->canonical_quantity ?? $line->grams;
            $canonicalScaled = $canonical === null ? null : bcmul((string) $canonical, $factor, 4);
            $canonicalUnit = $line->canonical_unit ?: ($line->grams !== null ? 'g' : null);
            // İkinci ölçü satırı ("≈ 360 g") yalnız ana satırdan farklı bir birim
            // söylüyorsa; "2 diş" altında "2 dis" ve adet tekrarları basılmaz.
            $canonicalText = '';
            if ($canonicalScaled !== null && $this->formatter->ikinciSatirGerekli($canonicalUnit, $line->display_measure_code)) {
                $kod = (string) $canonicalUnit;
                $etiket = $this->formatter->birimKodu($kod, $canonicalScaled);
                $canonicalText = $etiket === '' ? '' : $this->formatter->kanonikMiktar($canonicalScaled, $kod).' '.$etiket;
            }

            $grup = $line->recipe_ingredient_group_id === null ? ($line->group_name ?: 'Malzemeler') : $line->group->name;

            return ['line' => $line, 'group' => $grup, 'group_label' => $this->formatter->grup($grup), 'formatted' => $display, 'display_quantity' => $scaled, 'measure_label' => $this->formatter->measureLabel($unit, $scaled), 'canonical_quantity' => $canonicalScaled, 'canonical_unit' => $canonicalUnit, 'canonical_text' => $canonicalText, 'is_approximate' => in_array($line->conversion_confidence, ['low', 'medium', 'unknown'], true), 'scaling_note' => $line->scaling_note, 'measure_code' => $line->display_measure_code];
        })->values()->all();
    }
}
