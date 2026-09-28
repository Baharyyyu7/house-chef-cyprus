<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DietaryTag;
use App\Models\Recipe;

final class DietaryCompatibilityService
{
    /** @var array<string, string> */
    private const ADLAR = [
        'vegan' => 'Vegan',
        'vegetarian' => 'Vejetaryen',
        'gluten_free' => 'Glutensiz',
        'lactose_free' => 'Laktozsuz',
        'dairy_free' => 'Sütsüz',
        'high_protein' => 'Yüksek protein',
        'low_calorie' => 'Düşük kalorili',
        'ketogenic' => 'Ketojenik',
        'halal_suitable' => 'Helal uygun',
    ];

    public function __construct(private readonly NutritionService $nutrition, private readonly AllergenAnalysisService $allergens) {}

    /** @return array<string, array<string, string|bool>> */
    public function assess(Recipe $recipe, ?int $servings = null): array
    {
        $nutrition = $this->nutrition->calculate($recipe, $servings);
        $allergens = $this->allergens->analyse($recipe);
        $codes = $recipe->relationLoaded('dietaryTags') ? $recipe->dietaryTags->pluck('code')->all() : $recipe->dietaryTags()->pluck('code')->all();
        // Veri yoksa data_get '0' donduruyordu ve bu, esigin altinda kalan
        // gercek bir olcum gibi degerlendiriliyordu: beslenme verisi hic
        // olmayan tarif "Yuksek protein: uygun degil" diye etiketleniyordu.
        // Veri yoklugundan olumsuz sonuc cikarmak, bilinmedigini soylemekten
        // daha kotu - okuyan bunu olculmus bir sonuc saniyor.
        $proteinHam = data_get($nutrition, 'nutrients.protein.per_serving');
        $kaloriHam = data_get($nutrition, 'nutrients.energy_kcal.per_serving');
        $protein = (string) ($proteinHam ?? '0');
        $calories = (string) ($kaloriHam ?? '0');
        $proteinBilinmiyor = $proteinHam === null || bccomp($protein, '0', 4) === 0;
        $kaloriBilinmiyor = $kaloriHam === null || bccomp($calories, '0', 4) === 0;
        $contains = collect($allergens['groups']['contains'])->pluck('name')->map(fn ($name) => mb_strtolower($name))->all();
        $tag = fn (string $code, string $fallback = 'uncertain'): string => in_array($code, $codes, true) ? 'manually_verified' : $fallback;

        // Sablon ham kodu baslik formatina ceviriyordu ve Turkce bir sitede
        // "Gluten Free", "High Protein" yaziyordu. Ad burada sabit: tabloya
        // baglamak, satir eksikse sessizce Ingilizceye dusmek demekti.
        // Tablodaki ad varsa onu kullaniyoruz, yoksa buradaki karsilik.
        $adlar = DietaryTag::query()->pluck('name', 'code');

        $satirlar = [
            'vegan' => ['status' => $tag('vegan', 'uncertain'), 'reason' => 'İçerik metadatası doğrulaması gerekir.'],
            'vegetarian' => ['status' => $tag('vegetarian', 'uncertain'), 'reason' => 'İçerik metadatası doğrulaması gerekir.'],
            'gluten_free' => ['status' => $tag('gluten_free', in_array('gluten içeren tahıllar', $contains, true) ? 'incompatible' : 'uncertain'), 'reason' => 'Çapraz bulaşma ayrıca değerlendirilmelidir.'],
            'lactose_free' => ['status' => $tag('lactose_free', in_array('süt', $contains, true) ? 'incompatible' : 'uncertain'), 'reason' => 'Ürün etiketi doğrulanmalıdır.'],
            'dairy_free' => ['status' => $tag('dairy_free', in_array('süt', $contains, true) ? 'incompatible' : 'uncertain'), 'reason' => 'Ürün etiketi doğrulanmalıdır.'],
            'high_protein' => ['status' => $proteinBilinmiyor ? 'uncertain' : (bccomp($protein, (string) config('nutrition.high_protein_per_serving', 20), 4) >= 0 ? 'compatible' : 'incompatible'), 'reason' => 'Porsiyon başına protein eşiği.'],
            'low_calorie' => ['status' => $kaloriBilinmiyor ? 'uncertain' : (bccomp($calories, (string) config('nutrition.low_calorie_per_serving', 400), 4) <= 0 ? 'compatible' : 'incompatible'), 'reason' => 'Porsiyon başına enerji eşiği.'],
            'ketogenic' => ['status' => 'uncertain', 'reason' => 'Net karbonhidrat ve makro dağılımı için doğrulanmış veri gerekir.'],
            'halal_suitable' => ['status' => $tag('halal_suitable'), 'reason' => 'Sertifikasyon veya kaynak doğrulaması gerekir.'],
        ];

        foreach ($satirlar as $kod => $satir) {
            // Tablodaki ve buradaki adlar Türkçe; yabancı sayfada "No verified
            // data for: Vegan, Vejetaryen, Glutensiz" basılıyordu.
            $satirlar[$kod]['label'] = turkce_sayfa()
                ? ($adlar[$kod] ?? self::ADLAR[$kod] ?? str($kod)->replace('_', ' ')->title()->value())
                : __('diet.'.$kod);
        }

        return $satirlar;
    }
}
