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

    public function __construct(
        private readonly NutritionService $nutrition,
        private readonly AllergenAnalysisService $allergens,
        private readonly AllergenInferenceService $inference,
        private readonly RecipeDataCompletenessService $completeness,
    ) {}

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
        // Kapsamı düşük hesap da bilinmiyor sayılır. Kuzu kolda tek ölçülü
        // satır tuzdu; hesap "6 kcal" çıkarıyor ve tarif "✓ Düşük kalorili"
        // etiketi alıyordu. Eşiğin altındaki toplam, yemeğin değil
        // ölçülebilen birkaç malzemenin toplamıdır.
        $satirSayisi = $recipe->relationLoaded('recipeIngredients') ? $recipe->recipeIngredients->count() : $recipe->recipeIngredients()->count();
        $besinYetersiz = ! $this->completeness->besinYeterli($nutrition, $satirSayisi);
        $proteinBilinmiyor = $besinYetersiz || $proteinHam === null || bccomp($protein, '0', 4) === 0;
        $kaloriBilinmiyor = $besinYetersiz || $kaloriHam === null || bccomp($calories, '0', 4) === 0;
        // Alerjen kodları doğrulanmış kayıtla malzeme adından çıkarımın
        // birleşimi: tabloda eşleme yoksa unlu tarif "Glutensiz" için
        // "belirsiz" kalıyordu, oysa unu adıyla taşıyor.
        $ozet = $this->inference->ozet($recipe, $allergens);
        $icerir = array_column($ozet['contains'], 'code');
        $hayvansal = array_intersect($icerir, ['milk', 'egg', 'fish', 'crustaceans', 'molluscs']) !== [];
        $deniz = array_intersect($icerir, ['fish', 'crustaceans', 'molluscs']) !== [];
        // Elle konmuş etiket, malzemeyle çelişiyorsa kazanamaz: "Vegan"
        // etiketli bir tarifte süt varsa doğru cevap "uygun değil".
        $tag = fn (string $code, string $fallback = 'uncertain'): string => in_array($code, $codes, true) ? 'manually_verified' : $fallback;
        $kesin = fn (bool $celisiyor, string $code, string $fallback = 'uncertain'): string => $celisiyor ? 'incompatible' : $tag($code, $fallback);

        // Sablon ham kodu baslik formatina ceviriyordu ve Turkce bir sitede
        // "Gluten Free", "High Protein" yaziyordu. Ad burada sabit: tabloya
        // baglamak, satir eksikse sessizce Ingilizceye dusmek demekti.
        // Tablodaki ad varsa onu kullaniyoruz, yoksa buradaki karsilik.
        $adlar = DietaryTag::query()->pluck('name', 'code');

        $satirlar = [
            'vegan' => ['status' => $kesin($hayvansal, 'vegan'), 'reason' => 'İçerik metadatası doğrulaması gerekir.'],
            'vegetarian' => ['status' => $kesin($deniz, 'vegetarian'), 'reason' => 'İçerik metadatası doğrulaması gerekir.'],
            'gluten_free' => ['status' => $kesin(in_array('gluten', $icerir, true), 'gluten_free'), 'reason' => 'Çapraz bulaşma ayrıca değerlendirilmelidir.'],
            'lactose_free' => ['status' => $kesin(in_array('milk', $icerir, true), 'lactose_free'), 'reason' => 'Ürün etiketi doğrulanmalıdır.'],
            'dairy_free' => ['status' => $kesin(in_array('milk', $icerir, true), 'dairy_free'), 'reason' => 'Ürün etiketi doğrulanmalıdır.'],
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
