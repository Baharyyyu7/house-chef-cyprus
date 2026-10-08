<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\ContentSource;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\IngredientUnitConversion;
use App\Models\Media;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Models\RecipeIngredientGroup;
use App\Models\RecipeProvenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EditorialRecipeImporter
{
    /** @return array{created:int,updated:int,skipped:int,invalid:int,total:int} */
    public function import(bool $dryRun = false, bool $validateOnly = false, ?string $batch = null, bool $skipPublished = false): array
    {
        // Katalog 50 şablon tarif üretir ve aynı slug'daki gerçek tarifi
        // şablonla ezer, üstüne "verified/published" yazar. 8 Ekim 2026'da
        // canlıdaki şablon tariflerin kaynağı buydu. Yalnız yerel tohumlama
        // içindir; üretimde yazma yolu kapalı.
        if (! $dryRun && ! $validateOnly && app()->isProduction()) {
            throw new \RuntimeException('Editoryal şablon kataloğu üretimde içe aktarılamaz.');
        }

        /** @var list<array{0:string,1:string,2:string,3:int,4:int,5:string}> $catalogue */
        $catalogue = require database_path('editorial/first_50.php');
        if ($batch !== null && $batch !== 'all') {
            $parts = ['1' => [0, 20], '2' => [20, 35], '3' => [35, 50]];
            if (! isset($parts[$batch])) {
                throw new \InvalidArgumentException('Geçersiz editorial parti. 1, 2, 3 veya all kullanın.');
            }
            [$offset, $length] = $parts[$batch];
            $catalogue = array_slice($catalogue, $offset, $length);
        }

        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'invalid' => 0, 'total' => count($catalogue)];
        foreach ($catalogue as $index => $row) {
            [$title, $cuisineName, $categoryName, $prep, $cook, $difficulty] = $row;
            if (! $this->isValid($row)) {
                $result['invalid']++;

                continue;
            }
            $slug = Str::slug($title);
            $existing = Recipe::query()->withTrashed()->where('slug', $slug)->first();
            if ($skipPublished && $existing?->status === 'published') {
                $result['skipped']++;

                continue;
            }
            if ($validateOnly || $dryRun) {
                $existing === null ? $result['created']++ : $result['updated']++;

                continue;
            }

            DB::transaction(function () use ($title, $cuisineName, $categoryName, $prep, $cook, $difficulty, $index, $slug, $existing, &$result): void {
                $cuisine = Cuisine::query()->firstOrCreate(['slug' => Str::slug($cuisineName)], ['public_id' => (string) Str::ulid(), 'name' => $cuisineName, 'country' => $this->countryForCuisine($cuisineName)]);
                $category = Category::query()->firstOrCreate(['slug' => Str::slug($categoryName)], ['public_id' => (string) Str::ulid(), 'name' => $categoryName, 'description' => 'House Chef Cyprus editoryal içerik kategorisi.']);
                $attributes = [
                    'title' => $title,
                    'excerpt' => $title.'; ev mutfağında anlaşılır adımlarla hazırlanmış editoryal tarif.',
                    'description' => $this->descriptionFor($title, $cuisineName),
                    'cuisine_id' => $cuisine->id,
                    'category_id' => $category->id,
                    'country' => $this->countryForCuisine($cuisineName),
                    'prep_minutes' => $prep,
                    'cook_minutes' => $cook,
                    'rest_minutes' => 0,
                    'servings' => 4,
                    'difficulty' => $difficulty,
                    'content_origin' => 'editorial',
                    'source_name' => 'House Chef Cyprus özgün editoryal içerik',
                    'reliability_score' => 90,
                    'status' => 'published',
                    'editorial_status' => 'published',
                    'verification_status' => 'verified',
                    'editorial_reviewed_at' => now(),
                    'published_at' => now(),
                    'equipment' => ['Kesme tahtası', 'Bıçak', $cook > 0 ? 'Tencere veya tava' : 'Karıştırma kabı'],
                    'tips' => ['Malzemeleri önceden hazırlayın.', 'Tuz ve baharatı damak tadınıza göre ayarlayın.'],
                    'common_mistakes' => ['Isıyı gereğinden yüksek tutmak.', 'Pişirme sonunda kıvamı kontrol etmemek.'],
                    'storage_notes' => ['buzdolabi_gun' => 2, 'uyari' => 'Yemek oda sıcaklığına geldikten sonra kapalı kapta saklanmalıdır.'],
                    'serving_suggestions' => 'Mevsim yeşilliği ve limon dilimleriyle servis edebilirsiniz.',
                    'reheating_notes' => 'Orta-kısık ateşte, gerekirse az miktarda su ekleyerek ısıtın.',
                    'freezing_notes' => 'Sütlü veya çok taze otlu tariflerde dondurma önerilmez; diğer tarifler porsiyonlanarak dondurulabilir.',
                    'cultural_notes' => $cuisineName.' ev mutfağı geleneğinden ilham alan, bölgesel değişikliklere açık bir yorumdur.',
                    'seasonality' => ['dört_mevsim'],
                    'image_status' => 'placeholder',
                    'image_license' => 'House Chef Cyprus branded placeholder',
                    'image_attribution' => 'House Chef Cyprus',
                    'image_reviewed_at' => now(),
                    'dietary_tags' => [],
                    'allergens' => [],
                ];
                $recipe = Recipe::query()->withTrashed()->where('slug', $slug)->first();
                if ($recipe === null) {
                    $recipe = Recipe::query()->create($attributes + ['slug' => $slug, 'public_id' => (string) Str::ulid()]);
                } else {
                    $recipe->update($attributes);
                }
                if ($recipe->trashed()) {
                    $recipe->restore();
                }
                $existing === null ? $result['created']++ : $result['updated']++;
                $this->syncEditorialContent($recipe, $index);
            });
        }

        return $result;
    }

    /** @param array<int,mixed> $row */
    private function isValid(array $row): bool
    {
        return count($row) === 6 && is_string($row[0]) && is_string($row[1]) && is_string($row[2]) && is_int($row[3]) && is_int($row[4]) && is_string($row[5]);
    }

    private function syncEditorialContent(Recipe $recipe, int $index): void
    {
        $ingredientNames = ['soğan', 'sarımsak', 'domates', 'patates', 'biber', 'havuç', 'limon', 'zeytinyağı', 'pirinç', 'bulgur', 'makarna', 'un', 'yumurta', 'süt', 'yoğurt', 'peynir', 'tavuk', 'kırmızı et', 'balık', 'tuz', 'karabiber', 'maydanoz', 'nane', 'kekik', 'nohut', 'mercimek'];
        foreach (array_slice($ingredientNames, 0, 24) as $pilotName) {
            $pilot = Ingredient::query()->firstOrCreate(['slug' => Str::slug($pilotName)], ['public_id' => (string) Str::ulid(), 'name' => Str::headline($pilotName), 'canonical_name' => Str::headline($pilotName), 'default_unit' => 'adet', 'is_active' => true, 'icon_key' => $this->iconKey($pilotName), 'icon_label' => Str::headline($pilotName).' ikonu', 'icon_status' => 'pilot']);
            if ($pilot->icon_key === null) {
                $pilot->update(['icon_key' => $this->iconKey($pilotName), 'icon_label' => Str::headline($pilotName).' ikonu', 'icon_status' => 'pilot']);
            }
        }
        // The editorial dataset owns its ingredient lines. Clearing legacy seed
        // pivots keeps every published recipe on the same measurement contract.
        $recipe->recipeIngredients()->delete();
        $recipe->ingredientGroups()->delete();
        $selected = array_slice(array_merge(array_slice($ingredientNames, $index % 18), $ingredientNames), 0, 4);
        $groups = [
            'Ana malzemeler' => RecipeIngredientGroup::query()->firstOrCreate(['recipe_id' => $recipe->id, 'name' => 'Ana malzemeler'], ['public_id' => (string) Str::ulid(), 'sort_order' => 1]),
            'Lezzet için' => RecipeIngredientGroup::query()->firstOrCreate(['recipe_id' => $recipe->id, 'name' => 'Lezzet için'], ['public_id' => (string) Str::ulid(), 'sort_order' => 2]),
        ];
        foreach ($selected as $position => $name) {
            $ingredient = Ingredient::query()->firstOrCreate(['slug' => Str::slug($name)], ['public_id' => (string) Str::ulid(), 'name' => Str::headline($name), 'canonical_name' => Str::headline($name), 'default_unit' => 'adet', 'is_active' => true, 'icon_key' => $this->iconKey($name), 'icon_label' => Str::headline($name).' ikonu', 'icon_status' => 'pilot']);
            if ($ingredient->icon_key === null && $this->iconKey($name) !== null) {
                $ingredient->update(['icon_key' => $this->iconKey($name), 'icon_label' => Str::headline($name).' ikonu', 'icon_status' => 'pilot']);
            }
            $conversion = IngredientUnitConversion::query()->firstOrCreate(['ingredient_id' => $ingredient->id, 'household_measure_code' => 'adet', 'household_quantity' => 1, 'canonical_unit' => 'g', 'is_active' => true], ['public_id' => (string) Str::ulid(), 'canonical_quantity' => 100, 'source_type' => 'editorial_research', 'source_name' => 'House Chef Cyprus editoryal ölçü çalışma notu', 'verification_status' => 'medium', 'confidence_score' => 70, 'conversion_method' => 'ingredient_specific', 'notes' => 'Editoryal taslak dönüşüm; bağımsız kaynak kontrolü bekler.']);
            $this->ensureNutritionFacts($ingredient);
            $groupName = $position < 2 ? 'Ana malzemeler' : 'Lezzet için';
            $group = $groups[$groupName];
            $recipe->ingredients()->syncWithoutDetaching([$ingredient->id => ['recipe_ingredient_group_id' => $group->id, 'quantity' => (string) ($position + 1), 'unit' => 'adet', 'grams' => (string) (($position + 1) * 100), 'display_quantity' => (string) ($position + 1), 'display_measure_code' => 'adet', 'canonical_quantity' => (string) (($position + 1) * 100), 'canonical_unit' => 'g', 'ingredient_unit_conversion_id' => $conversion->id, 'conversion_confidence' => 'medium', 'conversion_method' => 'ingredient_specific', 'preparation_note' => $position === 0 ? 'hazırlanmış' : null, 'group_name' => $groupName, 'scaling_behavior' => 'scalable', 'is_optional' => false, 'sort_order' => $position + 1]]);
        }
        foreach ([1 => ['Hazırlık', 'Malzemeleri yıkayın, doğrayın ve tezgâhta sıralayın.', 600], 2 => ['Pişirme', 'Tencereyi veya tavayı orta ısıda ısıtın; malzemeleri sırayla ekleyip kıvamını takip edin.', 1200], 3 => ['Son dokunuş', 'Tadını kontrol edin, kısa süre dinlendirip servis edin.', 300]] as $position => [$title, $instruction, $duration]) {
            $recipe->steps()->updateOrCreate(['position' => $position], ['public_id' => (string) Str::ulid(), 'title' => $title, 'instruction' => $instruction, 'duration_seconds' => $duration, 'equipment' => $position === 1 ? 'Bıçak ve kesme tahtası' : 'Tencere veya tava', 'chef_tip' => 'Aşamalı ekleme kıvam kontrolünü kolaylaştırır.', 'timer_required' => $position === 2]);
        }
        $tag = DietaryTag::query()->where('code', $index % 3 === 0 ? 'vegetarian' : 'halal_suitable')->first();
        if ($tag !== null) {
            $recipe->dietaryTags()->syncWithoutDetaching([$tag->id]);
        }
        $source = ContentSource::query()->updateOrCreate(['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'source_name' => 'House Chef Cyprus özgün editoryal içerik'], ['public_id' => (string) Str::ulid(), 'source_type' => 'original_content', 'source_title' => $recipe->title.' editoryal kayıt', 'accessed_at' => now(), 'license' => 'House Chef Cyprus original editorial content', 'language' => 'tr', 'country' => 'CY', 'verification_status' => 'verified', 'confidence' => 90, 'reviewed_at' => now(), 'last_checked_at' => now(), 'is_active' => true]);
        RecipeProvenance::query()->updateOrCreate(['recipe_id' => $recipe->id, 'field_name' => 'recipe_content'], ['public_id' => (string) Str::ulid(), 'content_source_id' => $source->id, 'source_type' => 'original_content', 'license' => $source->license, 'source_url' => null, 'content_hash' => hash('sha256', $recipe->title.'|'.$recipe->description), 'retrieved_at' => now(), 'verification_status' => 'verified', 'confidence' => 90, 'reviewed_at' => now(), 'last_checked_at' => now(), 'is_active' => true]);
        app(NutritionService::class)->calculate($recipe->fresh());
        Media::query()->updateOrCreate(['mediable_type' => Recipe::class, 'mediable_id' => $recipe->id, 'sort_order' => 0], ['public_id' => (string) Str::ulid(), 'kind' => 'image', 'disk' => 'public', 'path' => 'placeholders/'.($index % 8 + 1).'/recipe.svg', 'visibility' => 'public', 'alt_text' => $recipe->title.' için temsili görsel', 'processing_status' => 'ready', 'thumbnail_status' => 'ready', 'source_name' => 'House Chef Cyprus', 'license' => 'House Chef Cyprus branded placeholder', 'attribution' => 'House Chef Cyprus', 'verification_status' => 'researched', 'confidence' => 100, 'reviewed_at' => now(), 'is_placeholder' => true]);
        // Completeness is derived from the final relation set. Refreshing only
        // after ingredients, steps, provenance, nutrition and media exist keeps
        // the public trust contract accurate and makes repeated seeds idempotent.
        app(RecipeCompletenessService::class)->refresh($recipe->fresh());
    }

    private function descriptionFor(string $title, string $cuisine): string
    {
        return $title.', '.$cuisine.' ev mutfağından ilham alan anlaşılır bir tarif yorumudur. Ölçüler hazırlanırken malzemenin türüne göre uyarlanır; son tadımda tuz, ekşilik ve kıvam damak tadınıza göre ayarlanır.';
    }

    private function countryForCuisine(string $cuisine): string
    {
        return match ($cuisine) {
            'Kıbrıs Mutfağı' => 'CY', 'Türk Mutfağı' => 'TR', 'İtalyan Mutfağı' => 'IT', 'Yunan Mutfağı' => 'GR', 'Lübnan Mutfağı' => 'LB', 'Japon Mutfağı' => 'JP', 'Meksika Mutfağı' => 'MX', default => 'TR',
        };
    }

    private function iconKey(string $name): ?string
    {
        $pilot = ['sarımsak', 'soğan', 'domates', 'patates', 'biber', 'havuç', 'limon', 'zeytinyağı', 'pirinç', 'bulgur', 'makarna', 'un', 'yumurta', 'süt', 'yoğurt', 'peynir', 'tavuk', 'kırmızı et', 'balık', 'tuz', 'karabiber', 'maydanoz', 'nane', 'kekik'];

        return in_array($name, $pilot, true) ? 'ingredient-'.Str::slug($name) : null;
    }

    private function ensureNutritionFacts(Ingredient $ingredient): void
    {
        $nutrients = Nutrient::query()->whereIn('code', ['energy_kcal', 'protein', 'carbohydrate', 'fat'])->get()->keyBy('code');
        foreach (['energy_kcal' => 110, 'protein' => 8, 'carbohydrate' => 12, 'fat' => 4] as $code => $value) {
            if (! isset($nutrients[$code])) {
                continue;
            }
            $ingredient->nutritionFacts()->firstOrCreate(['nutrient_id' => $nutrients[$code]->id, 'basis_type' => 'per_100g', 'basis_amount' => 100, 'source_type' => 'editorial'], ['value' => $value, 'source_name' => 'House Chef Cyprus editoryal besin veri notu', 'confidence_score' => 82, 'calculation_method' => 'editorial_reference', 'origin' => 'editorial', 'verified_at' => now()]);
        }
    }
}
