<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\IngredientAlias;
use App\Models\IngredientSubstitution;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class HouseChefSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['g', 'Gram', 'g', 'weight', true, 1], ['kg', 'Kilogram', 'kg', 'weight', true, 1000], ['ml', 'Mililitre', 'ml', 'volume', true, 1], ['l', 'Litre', 'l', 'volume', true, 1000], ['adet', 'Adet', 'adet', 'count', false, null], ['yk', 'Yemek kaşığı', 'yk', 'household', false, null], ['ck', 'Çay kaşığı', 'çk', 'household', false, null]] as [$code, $name, $symbol, $type, $metric, $factor]) {
            Unit::query()->firstOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'symbol' => $symbol, 'unit_type' => $type, 'is_metric' => $metric, 'conversion_factor' => $factor]);
        }
        if (app()->environment(['local', 'testing'])) {
            $admin = User::query()->firstOrCreate(['email' => 'admin@housechef.local'], ['name' => 'House Chef Yöneticisi', 'password' => 'ChangeMe123!', 'email_verified_at' => now(), 'role' => 'admin']);
            $admin->update(['role' => 'admin']);
        }
        $category = Category::query()->firstOrCreate(['slug' => 'ana-yemek'], ['name' => 'Ana Yemek', 'description' => 'Doyurucu tarifler']);
        $cuisines = collect(['Kıbrıs Mutfağı' => 'Kıbrıs', 'Türk Mutfağı' => 'Türkiye', 'İtalyan Mutfağı' => 'İtalya', 'Japon Mutfağı' => 'Japonya', 'Meksika Mutfağı' => 'Meksika', 'Lübnan Mutfağı' => 'Lübnan'])->mapWithKeys(fn (string $country, string $name) => [$name => Cuisine::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'country' => $country])]);
        $cuisines->each(fn (Cuisine $cuisine) => $cuisine->public_id ?? $cuisine->update(['public_id' => (string) Str::ulid()]));
        $ingredientNames = ['tavuk', 'soğan', 'sarımsak', 'domates', 'zeytinyağı', 'pirinç', 'limon', 'yoğurt', 'patates', 'maydanoz', 'nohut', 'makarna', 'mercimek', 'ıspanak', 'havuç', 'biber', 'mantar', 'fasulye', 'bulgur', 'salatalık'];
        $ingredients = collect($ingredientNames)->mapWithKeys(fn (string $name) => [$name => Ingredient::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => Str::headline($name), 'canonical_name' => Str::headline($name), 'default_unit' => 'adet'])]);
        $ingredients->each(fn (Ingredient $ingredient) => $ingredient->public_id ?? $ingredient->update(['public_id' => (string) Str::ulid()]));
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
                $ingredient->nutritionFacts()->updateOrCreate(['nutrient_id' => $nutrientModels[$code]->id, 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial'], ['value' => $value, 'source_name' => 'House Chef Cyprus demo veri seti', 'confidence_score' => $ingredient->slug === 'mantar' ? 65 : 90, 'calculation_method' => 'editorial_reference', 'origin' => $ingredient->slug === 'mantar' ? 'ai_estimated' : 'verified', 'verified_at' => $ingredient->slug === 'mantar' ? null : now()]);
            }
        }
        $allergens = collect([['gluten', 'Gluten içeren tahıllar'], ['milk', 'Süt'], ['egg', 'Yumurta'], ['peanut', 'Yer fıstığı'], ['tree_nuts', 'Sert kabuklu yemişler'], ['soy', 'Soya'], ['fish', 'Balık'], ['crustaceans', 'Kabuklu deniz ürünleri'], ['sesame', 'Susam'], ['celery', 'Kereviz'], ['mustard', 'Hardal'], ['lupin', 'Acı bakla'], ['molluscs', 'Yumuşakçalar'], ['sulphites', 'Sülfit']])->mapWithKeys(function (array $data) {
            [$code, $name] = $data;

            return [$code => Allergen::query()->updateOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'is_active' => true])];
        });
        $tags = collect([['vegan', 'Vegan'], ['vegetarian', 'Vejetaryen'], ['gluten_free', 'Glutensiz'], ['lactose_free', 'Laktozsuz'], ['dairy_free', 'Sütsüz'], ['high_protein', 'Yüksek protein'], ['low_calorie', 'Düşük kalorili'], ['ketogenic', 'Ketojenik'], ['halal_suitable', 'Helal uygun']])->mapWithKeys(function (array $data) {
            [$code, $name] = $data;

            return [$code => DietaryTag::query()->updateOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'description' => 'Demo diyet etiketi', 'is_active' => true])];
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
            IngredientSubstitution::query()->firstOrCreate(['ingredient_id' => $ingredients[$ingredientName]->id, 'substitute_ingredient_id' => $ingredients[$substituteName]->id, 'substitution_type' => 'emergency'], ['ratio' => 1, 'confidence_score' => 75, 'is_verified' => true, 'is_active' => true, 'notes' => 'Demo alternatifi']);
        }
        $recipes = [['Kıbrıs Usulü Fırın Tavuk', 'Kıbrıs Mutfağı', 20, 45], ['Zeytinyağlı Nohutlu Pilav', 'Türk Mutfağı', 15, 30], ['Limonlu Akdeniz Makarnası', 'İtalyan Mutfağı', 10, 20], ['Sebzeli Teriyaki Tavuk', 'Japon Mutfağı', 20, 18], ['Fırın Sebzeli Taco Kasesi', 'Meksika Mutfağı', 20, 25], ['Yoğurtlu Nohut Salatası', 'Lübnan Mutfağı', 15, 0], ['Domatesli Tavuk Sote', 'Türk Mutfağı', 15, 25], ['Limonlu Otlu Patates', 'Kıbrıs Mutfağı', 15, 35], ['Sarımsaklı Zeytinyağlı Makarna', 'İtalyan Mutfağı', 10, 15], ['Pirinçli Tavuk Çorbası', 'Türk Mutfağı', 15, 35], ['Nohutlu Akdeniz Kasesi', 'Lübnan Mutfağı', 20, 10], ['Fırında Baharatlı Tavuk', 'Meksika Mutfağı', 15, 40]];
        foreach ($recipes as $index => [$title, $cuisine, $prep, $cook]) {
            $recipe = Recipe::query()->firstOrCreate(['slug' => Str::slug($title)], ['title' => $title, 'cuisine_id' => $cuisines[$cuisine]->id, 'category_id' => $category->id, 'excerpt' => 'Mevsim malzemeleriyle hazırlanan, ev mutfağına uygun özgün demo tarifi.', 'description' => 'Bu tarif House Chef Cyprus için hazırlanmış örnek içeriktir. Damak tadınıza göre baharat oranını ayarlayabilirsiniz.', 'prep_minutes' => $prep, 'cook_minutes' => $cook, 'servings' => 4, 'difficulty' => $index % 3 === 0 ? 'Orta' : 'Kolay', 'dietary_tags' => $index % 2 === 0 ? ['Yüksek protein'] : ['Akdeniz tipi'], 'allergens' => $index % 2 === 0 ? [] : ['Gluten'], 'nutrition' => ['kalori' => 420, 'protein' => 28, 'karbonhidrat' => 44, 'yağ' => 14], 'storage_notes' => ['buzdolabi_gun' => 2, 'uyari' => 'Soğuduktan sonra kapalı kapta saklayın.'], 'source_name' => 'House Chef Cyprus özgün demo', 'reliability_score' => 100, 'status' => 'published', 'is_featured' => $index < 4]);
            if ($recipe->public_id === null) {
                $recipe->update(['public_id' => (string) Str::ulid()]);
            }
            foreach (array_values(array_slice($ingredients->all(), $index % 4, 4, true)) as $position => $ingredient) {
                $recipe->ingredients()->syncWithoutDetaching([$ingredient->id => ['quantity' => $position + 1, 'unit' => 'g', 'grams' => ($position + 1) * 100, 'is_optional' => false, 'sort_order' => $position + 1]]);
            }
            $recipe->dietaryTags()->syncWithoutDetaching([$tags[$index % 2 === 0 ? 'high_protein' : 'vegetarian']->id]);
            $steps = [
                ['position' => 1, 'title' => 'Hazırlık', 'instruction' => 'Malzemeleri yıkayın, doğrayın ve ölçüleri hazırlayın.', 'duration_seconds' => $prep * 60, 'equipment' => 'Kesme tahtası ve bıçak'],
                ['position' => 2, 'title' => 'Ön hazırlık', 'instruction' => 'Tencereyi veya tavayı orta ısıda hazırlayın, malzemeleri sıraya koyun.', 'duration_seconds' => 300, 'is_optional' => $index < 4, 'chef_tip' => 'Malzemeleri önceden hazırlamak pişirme sırasında kontrolü artırır.'],
                ['position' => 3, 'title' => 'Pişirme', 'instruction' => 'Malzemeleri tarifin gerektirdiği sırayla pişirin ve kıvamını kontrol edin.', 'duration_seconds' => max(60, $cook * 60), 'timer_required' => true, 'temperature_value' => $index < 4 ? 180 : null, 'temperature_unit' => $index < 4 ? 'celsius' : null, 'equipment' => $index < 4 ? 'Fırın veya tava' : 'Tava', 'safety_note' => $index < 4 ? 'Sıcak ekipmana çıplak elle dokunmayın; fırın eldiveni kullanın.' : null],
                ['position' => 4, 'title' => 'Servis', 'instruction' => 'Tarifi dinlendirin, son kontrolü yapın ve sıcak servis edin.', 'duration_seconds' => 300, 'chef_tip' => 'Taze yeşillikle servis edebilirsiniz.', 'completion_hint' => 'Sos parlak ve malzemeler eşit sıcaklıktaysa tarif hazırdır.'],
            ];
            foreach ($steps as $step) {
                $step['public_id'] = (string) Str::ulid();
                $recipe->steps()->updateOrCreate(['position' => $step['position']], $step);
            }
        }
    }
}
