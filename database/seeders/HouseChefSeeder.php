<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Allergen;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\IngredientAlias;
use App\Models\IngredientSubstitution;
use App\Models\IngredientUnitConversion;
use App\Models\Nutrient;
use App\Models\Unit;
use App\Models\User;
use App\Services\EditorialRecipeImporter;
use App\Services\Media\BrandedRecipeFallbackService;
use App\Services\Media\PilotRecipeDataCorrectionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class HouseChefSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['gram', 'gram', 'gram', 'g', 'weight', true, 1, false, true], ['kilogram', 'kilogram', 'kilogram', 'kg', 'weight', true, 1000, false, true], ['mililitre', 'mililitre', 'mililitre', 'ml', 'volume', true, 1, false, true], ['litre', 'litre', 'litre', 'l', 'volume', true, 1000, false, true],
            ['adet', 'adet', 'adet', 'adet', 'count', false, null, true, true], ['yarim_adet', 'yarım adet', 'yarım adet', '½ adet', 'count', false, null, true, false], ['su_bardagi', 'su bardağı', 'su bardağı', 'sb', 'household', false, null, true, false], ['cay_bardagi', 'çay bardağı', 'çay bardağı', 'çb', 'household', false, null, true, false], ['kahve_fincani', 'kahve fincanı', 'kahve fincanı', 'fincan', 'household', false, null, true, false], ['kase', 'kase', 'kase', 'kase', 'household', false, null, true, false], ['yemek_kasigi', 'yemek kaşığı', 'yemek kaşığı', 'yk', 'household', false, null, true, false], ['tatli_kasigi', 'tatlı kaşığı', 'tatlı kaşığı', 'tk', 'household', false, null, true, false], ['cay_kasigi', 'çay kaşığı', 'çay kaşığı', 'çk', 'household', false, null, true, false], ['tutam', 'tutam', 'tutam', 'tutam', 'household', false, null, true, false], ['fiske', 'fiske', 'fiske', 'fiske', 'household', false, null, true, false], ['avuc', 'avuç', 'avuç', 'avuç', 'household', false, null, true, false], ['demet', 'demet', 'demet', 'demet', 'count', false, null, true, false], ['bag', 'bağ', 'bağ', 'bağ', 'count', false, null, true, false], ['dis', 'diş', 'diş', 'diş', 'count', false, null, true, false], ['dal', 'dal', 'dal', 'dal', 'count', false, null, true, false], ['yaprak', 'yaprak', 'yaprak', 'yaprak', 'count', false, null, true, false], ['dilim', 'dilim', 'dilim', 'dilim', 'count', false, null, true, false], ['paket', 'paket', 'paket', 'paket', 'count', false, null, true, false], ['kutu', 'kutu', 'kutu', 'kutu', 'count', false, null, true, false], ['konserve', 'konserve', 'konserve', 'konserve', 'count', false, null, true, false], ['kepce', 'kepçe', 'kepçe', 'kepçe', 'household', false, null, true, false], ['servis_kasigi', 'servis kaşığı', 'servis kaşığı', 'sk', 'household', false, null, true, false],
            // Legacy programmatic units remain non-visible for backward compatibility.
            ['g', 'gram', 'gram', 'g', 'weight', true, 1, false, true], ['kg', 'kilogram', 'kilogram', 'kg', 'weight', true, 1000, false, true], ['ml', 'mililitre', 'mililitre', 'ml', 'volume', true, 1, false, true], ['l', 'litre', 'litre', 'l', 'volume', true, 1000, false, true], ['yk', 'yemek kaşığı', 'yemek kaşığı', 'yk', 'household', false, null, true, false], ['ck', 'çay kaşığı', 'çay kaşığı', 'çk', 'household', false, null, true, false],
        ] as $sort => [$code, $name, $singular, $symbol, $type, $metric, $factor, $fractions, $canonical]) {
            Unit::query()->updateOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'singular_name' => $singular, 'plural_name' => $name, 'symbol' => $symbol, 'short_label' => $symbol, 'unit_type' => $type, 'is_metric' => $metric, 'conversion_factor' => $factor, 'supports_fractions' => $fractions, 'is_user_visible' => ! in_array($code, ['g', 'kg', 'ml', 'l', 'yk', 'ck'], true), 'is_canonical' => $canonical, 'precision' => 2, 'sort_order' => $sort + 1]);
        }
        if (app()->environment(['local', 'testing'])) {
            $admin = User::query()->firstOrCreate(['email' => 'admin@housechef.local'], ['public_id' => (string) Str::ulid(), 'name' => 'House Chef Yöneticisi', 'password' => 'ChangeMe123!', 'email_verified_at' => now(), 'role' => 'admin']);
            $admin->update(['role' => 'admin', 'public_id' => $admin->public_id ?: (string) Str::ulid()]);
        }
        $cuisines = collect(['Kıbrıs Mutfağı' => 'Kıbrıs', 'Türk Mutfağı' => 'Türkiye', 'İtalyan Mutfağı' => 'İtalya', 'Japon Mutfağı' => 'Japonya', 'Meksika Mutfağı' => 'Meksika', 'Lübnan Mutfağı' => 'Lübnan'])->mapWithKeys(fn (string $country, string $name) => [$name => Cuisine::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'country' => $country])]);
        $cuisines->each(fn (Cuisine $cuisine) => $cuisine->public_id ?? $cuisine->update(['public_id' => (string) Str::ulid()]));
        $ingredientNames = ['tavuk', 'soğan', 'sarımsak', 'domates', 'zeytinyağı', 'pirinç', 'limon', 'yoğurt', 'patates', 'maydanoz', 'nohut', 'makarna', 'mercimek', 'ıspanak', 'havuç', 'biber', 'mantar', 'fasulye', 'bulgur', 'salatalık'];
        $ingredients = collect($ingredientNames)->mapWithKeys(fn (string $name) => [$name => Ingredient::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => Str::headline($name), 'canonical_name' => Str::headline($name), 'default_unit' => 'adet'])]);
        $ingredients->each(fn (Ingredient $ingredient) => $ingredient->public_id ?? $ingredient->update(['public_id' => (string) Str::ulid()]));
        $initialConversions = ['tavuk' => ['adet', 1, 180, 'g'], 'soğan' => ['adet', 1, 120, 'g'], 'sarımsak' => ['dis', 1, 3, 'g'], 'domates' => ['adet', 1, 120, 'g'], 'zeytinyağı' => ['yemek_kasigi', 1, 15, 'ml'], 'pirinç' => ['su_bardagi', 1, 180, 'g'], 'yoğurt' => ['yemek_kasigi', 1, 15, 'g'], 'maydanoz' => ['tutam', 1, 5, 'g'], 'nohut' => ['su_bardagi', 1, 160, 'g'], 'makarna' => ['su_bardagi', 1, 100, 'g'], 'mercimek' => ['su_bardagi', 1, 170, 'g'], 'fasulye' => ['su_bardagi', 1, 160, 'g'], 'bulgur' => ['su_bardagi', 1, 170, 'g'], 'limon' => ['adet', 1, 90, 'g']];
        foreach ($initialConversions as $name => [$measure, $household, $canonical, $unit]) {
            IngredientUnitConversion::query()->updateOrCreate(['ingredient_id' => $ingredients[$name]->id, 'household_measure_code' => $measure, 'household_quantity' => $household, 'canonical_unit' => $unit, 'is_active' => true], ['public_id' => (string) Str::ulid(), 'canonical_quantity' => $canonical, 'source_type' => 'editorial_initial', 'source_name' => 'House Chef Cyprus başlangıç ölçü kaydı', 'verification_status' => 'low', 'confidence_score' => 45, 'conversion_method' => 'editorial_estimate', 'notes' => 'İlk editoryal ölçü kaydı; bağımsız kaynak doğrulaması gerektirir.']);
        }
        $nutrients = [
            ['energy_kcal', 'Enerji', 'energy', 'kcal', true, false, 1], ['protein', 'Protein', 'macro', 'g', true, false, 2], ['carbohydrate', 'Karbonhidrat', 'macro', 'g', true, false, 3], ['fat', 'Yağ', 'macro', 'g', true, false, 4], ['saturated_fat', 'Doymuş yağ', 'macro', 'g', true, false, 5], ['trans_fat', 'Trans yağ', 'macro', 'g', true, false, 6], ['fiber', 'Lif', 'macro', 'g', true, false, 7], ['sugar', 'Şeker', 'macro', 'g', true, false, 8], ['sodium', 'Sodyum', 'mineral', 'mg', false, true, 9], ['salt', 'Tuz', 'mineral', 'g', false, true, 10], ['cholesterol', 'Kolesterol', 'other', 'mg', false, true, 11], ['vitamin_a', 'A vitamini', 'vitamin', 'µg', false, true, 12], ['vitamin_c', 'C vitamini', 'vitamin', 'mg', false, true, 13], ['vitamin_d', 'D vitamini', 'vitamin', 'µg', false, true, 14], ['vitamin_b12', 'B12 vitamini', 'vitamin', 'µg', false, true, 15], ['calcium', 'Kalsiyum', 'mineral', 'mg', false, true, 16], ['iron', 'Demir', 'mineral', 'mg', false, true, 17], ['potassium', 'Potasyum', 'mineral', 'mg', false, true, 18], ['magnesium', 'Magnezyum', 'mineral', 'mg', false, true, 19],
        ];
        foreach ($nutrients as [$code, $name, $categoryName, $unit, $macro, $micro, $sort]) {
            Nutrient::query()->updateOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'category' => $categoryName, 'unit' => $unit, 'decimal_precision' => 2, 'is_macro' => $macro, 'is_micronutrient' => $micro, 'is_active' => true, 'sort_order' => $sort]);
        }
        $nutrientModels = Nutrient::query()->get()->keyBy('code');
        foreach ($ingredients->values() as $position => $ingredient) {
            $values = ['energy_kcal' => 80 + $position * 5, 'protein' => 2 + ($position % 8), 'carbohydrate' => 4 + ($position % 10), 'fat' => $ingredient->slug === 'zeytinyagi' ? 100 : 1 + ($position % 4), 'fiber' => 1 + ($position % 5), 'sugar' => $position % 6, 'sodium' => 5 + $position, 'potassium' => 120 + $position * 10];
            foreach ($values as $code => $value) {
                $ingredient->nutritionFacts()->updateOrCreate(['nutrient_id' => $nutrientModels[$code]->id, 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial'], ['value' => $value, 'source_name' => 'House Chef Cyprus başlangıç besin kaydı', 'confidence_score' => $ingredient->slug === 'mantar' ? 65 : 90, 'calculation_method' => 'editorial_reference', 'origin' => $ingredient->slug === 'mantar' ? 'ai_estimated' : 'verified', 'verified_at' => $ingredient->slug === 'mantar' ? null : now()]);
            }
        }
        $allergens = collect([['gluten', 'Gluten içeren tahıllar'], ['milk', 'Süt'], ['egg', 'Yumurta'], ['peanut', 'Yer fıstığı'], ['tree_nuts', 'Sert kabuklu yemişler'], ['soy', 'Soya'], ['fish', 'Balık'], ['crustaceans', 'Kabuklu deniz ürünleri'], ['sesame', 'Susam'], ['celery', 'Kereviz'], ['mustard', 'Hardal'], ['lupin', 'Acı bakla'], ['molluscs', 'Yumuşakçalar'], ['sulphites', 'Sülfit']])->mapWithKeys(function (array $data) {
            [$code, $name] = $data;

            return [$code => Allergen::query()->updateOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'is_active' => true])];
        });
        $tags = collect([['vegan', 'Vegan'], ['vegetarian', 'Vejetaryen'], ['gluten_free', 'Glutensiz'], ['lactose_free', 'Laktozsuz'], ['dairy_free', 'Sütsüz'], ['high_protein', 'Yüksek protein'], ['low_calorie', 'Düşük kalorili'], ['ketogenic', 'Ketojenik'], ['halal_suitable', 'Helal uygun']])->mapWithKeys(function (array $data) {
            [$code, $name] = $data;

            return [$code => DietaryTag::query()->updateOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'description' => 'Editoryal diyet etiketi', 'is_active' => true])];
        });
        foreach ([['yoğurt', 'milk', 'contains'], ['makarna', 'gluten', 'contains'], ['bulgur', 'gluten', 'contains'], ['nohut', 'sesame', 'may_contain']] as [$ingredientName, $allergenCode, $relation]) {
            $ingredients[$ingredientName]->allergenDetails()->updateOrCreate(['allergen_id' => $allergens[$allergenCode]->id], ['relation_type' => $relation, 'confidence_score' => 90, 'source_type' => 'editorial', 'is_verified' => true]);
        }
        foreach (['soğan' => ['taze soğan', 'yeşil soğan'], 'yoğurt' => ['yoghurt'], 'kabartma tozu' => ['hamur kabartma tozu'], 'tavuk' => ['tavuk eti'], 'domates' => ['domatesler']] as $ingredientName => $aliases) {
            $ingredient = $ingredients->get($ingredientName);
            if ($ingredient === null) {
                continue;
            }
            foreach ($aliases as $alias) {
                IngredientAlias::query()->firstOrCreate(['ingredient_id' => $ingredient->id, 'normalized_alias' => Str::of($alias)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->value(), 'locale' => 'tr'], ['alias' => $alias, 'source_type' => 'editorial', 'confidence_score' => 100]);
            }
        }
        $substitutions = [['yoğurt', 'limon'], ['limon', 'yoğurt'], ['tavuk', 'nohut'], ['nohut', 'tavuk'], ['makarna', 'pirinç'], ['pirinç', 'makarna'], ['patates', 'nohut'], ['nohut', 'patates'], ['domates', 'yoğurt'], ['sarımsak', 'soğan']];
        foreach ($substitutions as [$ingredientName, $substituteName]) {
            IngredientSubstitution::query()->firstOrCreate(['ingredient_id' => $ingredients[$ingredientName]->id, 'substitute_ingredient_id' => $ingredients[$substituteName]->id, 'substitution_type' => 'emergency'], ['ratio' => 1, 'confidence_score' => 75, 'is_verified' => true, 'is_active' => true, 'notes' => 'Editoryal alternatif notu']);
        }
        app(EditorialRecipeImporter::class)->import();
        app(PilotRecipeDataCorrectionService::class)->apply();
        app(BrandedRecipeFallbackService::class)->synchronize();
    }
}
