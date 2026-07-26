<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;

final class DietaryCompatibilityService
{
    public function __construct(private readonly NutritionService $nutrition, private readonly AllergenAnalysisService $allergens) {}

    /** @return array<string, array<string, string|bool>> */
    public function assess(Recipe $recipe, ?int $servings = null): array
    {
        $nutrition = $this->nutrition->calculate($recipe, $servings);
        $allergens = $this->allergens->analyse($recipe);
        $codes = $recipe->dietaryTags()->pluck('code')->all();
        $protein = (string) data_get($nutrition, 'nutrients.protein.per_serving', '0');
        $calories = (string) data_get($nutrition, 'nutrients.energy_kcal.per_serving', '0');
        $contains = collect($allergens['groups']['contains'])->pluck('name')->map(fn ($name) => mb_strtolower($name))->all();
        $tag = fn (string $code, string $fallback = 'uncertain'): string => in_array($code, $codes, true) ? 'manually_verified' : $fallback;

        return [
            'vegan' => ['status' => $tag('vegan', 'uncertain'), 'reason' => 'İçerik metadatası doğrulaması gerekir.'],
            'vegetarian' => ['status' => $tag('vegetarian', 'uncertain'), 'reason' => 'İçerik metadatası doğrulaması gerekir.'],
            'gluten_free' => ['status' => $tag('gluten_free', in_array('gluten içeren tahıllar', $contains, true) ? 'incompatible' : 'uncertain'), 'reason' => 'Çapraz bulaşma ayrıca değerlendirilmelidir.'],
            'lactose_free' => ['status' => $tag('lactose_free', in_array('süt', $contains, true) ? 'incompatible' : 'uncertain'), 'reason' => 'Ürün etiketi doğrulanmalıdır.'],
            'dairy_free' => ['status' => $tag('dairy_free', in_array('süt', $contains, true) ? 'incompatible' : 'uncertain'), 'reason' => 'Ürün etiketi doğrulanmalıdır.'],
            'high_protein' => ['status' => bccomp($protein, (string) config('nutrition.high_protein_per_serving', 20), 4) >= 0 ? 'compatible' : 'incompatible', 'reason' => 'Porsiyon başına protein eşiği.'],
            'low_calorie' => ['status' => bccomp($calories, (string) config('nutrition.low_calorie_per_serving', 400), 4) <= 0 && $calories !== '0' ? 'compatible' : 'incompatible', 'reason' => 'Porsiyon başına enerji eşiği.'],
            'ketogenic' => ['status' => 'uncertain', 'reason' => 'Net karbonhidrat ve makro dağılımı için doğrulanmış veri gerekir.'],
            'halal_suitable' => ['status' => $tag('halal_suitable'), 'reason' => 'Sertifikasyon veya kaynak doğrulaması gerekir.'],
        ];
    }
}
