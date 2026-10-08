<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;

/**
 * Bir tarifin besin ve alerjen verisi iddia taşıyacak kadar tam mı?
 *
 * "✓ Doğrulandı" rozeti yalnız kayıt ve görsel incelemesine bakıyordu; aynı
 * sayfada "%0 tamam" besin kartı ve alerjen satırı olmayan bir yumurtalı
 * tarif duruyordu. Kuzu kol "6 kcal, ✓ Düşük kalorili" diyordu: tek ölçülü
 * malzeme tuzdu ve hesap tuzun yüzde yüz kapsandığını söylüyordu. Bu sınıf
 * iki kararı tek yerde tutar: besin değerinin gösterilebilir olması ve
 * rozetin basılabilmesi.
 */
final class RecipeDataCompletenessService
{
    public function __construct(
        private readonly NutritionService $nutrition,
        private readonly AllergenAnalysisService $allergens,
        private readonly AllergenInferenceService $inference,
    ) {}

    public static function esik(): int
    {
        return (int) config('nutrition.min_completeness_for_claims', 80);
    }

    /**
     * Besin hesabının gerçekten kapsadığı pay.
     *
     * NutritionService tamamlığı yalnız metrik ölçüsü olan satırların
     * ağırlığına göre hesaplıyor; "2 su bardağı un" gibi satırlar paydaya hiç
     * girmiyor. Burada ikisinin küçüğü alınır: ağırlık kapsamı ve besin
     * verisi bulunan satırların oranı.
     *
     * @param  array<string, mixed>  $besin
     */
    public function besinYuzdesi(array $besin, int $satirSayisi): int
    {
        $agirlik = (int) ($besin['completeness_percentage'] ?? 0);
        if ($satirSayisi <= 0) {
            return 0;
        }
        $eksik = count((array) ($besin['missing_ingredients'] ?? []));
        $satir = intdiv(max(0, $satirSayisi - $eksik) * 100, $satirSayisi);

        return min($agirlik, $satir);
    }

    /** @param array<string, mixed> $besin */
    public function besinYeterli(array $besin, int $satirSayisi): bool
    {
        return $besin['nutrients'] !== [] && $this->besinYuzdesi($besin, $satirSayisi) >= self::esik();
    }

    /**
     * @param  array<string, mixed>  $besin
     * @param  array{verified: bool}  $alerjenOzeti
     */
    public function tamam(array $besin, int $satirSayisi, array $alerjenOzeti): bool
    {
        return $this->besinYeterli($besin, $satirSayisi) && $alerjenOzeti['verified'];
    }

    /**
     * Kartlar için önbellekli karar. Kart sorgusu malzemeleri yüklemiyor;
     * karar tarifin kendi kaydı üzerinden ayrı hesaplanır ve yarım saat tutulur.
     */
    public function tamamOnbellekli(Recipe $recipe): bool
    {
        return (bool) Cache::remember('recipe-data-complete:v1:'.$recipe->getKey(), now()->addMinutes(30), function () use ($recipe): bool {
            $taze = Recipe::query()->with(['recipeIngredients.ingredient.nutritionFacts.nutrient', 'recipeIngredients.ingredient.allergenDetails.allergen', 'allergenOverrideRecords.allergen'])->find($recipe->getKey());
            if ($taze === null) {
                return false;
            }
            $besin = $this->nutrition->calculate($taze, max(1, (int) $taze->servings));

            return $this->tamam($besin, $taze->recipeIngredients->count(), $this->inference->ozet($taze, $this->allergens->analyse($taze)));
        });
    }
}
