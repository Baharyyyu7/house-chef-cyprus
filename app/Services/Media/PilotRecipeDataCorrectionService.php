<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Allergen;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\IngredientAllergenDetail;
use App\Models\IngredientUnitConversion;
use App\Models\Recipe;
use App\Models\RecipeIngredientGroup;
use App\Models\RecipeProvenance;
use App\Models\RecipeStep;
use App\Services\AllergenAnalysisService;
use App\Services\DietaryCompatibilityService;
use App\Services\NutritionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Curated, original editorial corrections for the AI-image pilot.
 *
 * This deliberately corrects only the records that were blocked by the recipe
 * fidelity gate.  It is repeatable, preserves the existing source provenance,
 * and records an explicit before/after editorial trail instead of silently
 * overwriting the legacy generic ingredient template.
 */
final class PilotRecipeDataCorrectionService
{
    /** @return array{corrected:int,needs_review:int,recipes:list<array<string,mixed>>} */
    public function apply(): array
    {
        $result = ['corrected' => 0, 'needs_review' => 0, 'recipes' => []];

        foreach ($this->definitions() as $slug => $definition) {
            $recipe = Recipe::query()->where('slug', $slug)->first();
            if ($recipe === null) {
                $result['needs_review']++;
                $result['recipes'][] = ['slug' => $slug, 'status' => 'needs_review', 'reason' => 'Tarif kaydı bulunamadı.'];

                continue;
            }

            DB::transaction(function () use ($recipe, $definition, &$result): void {
                $before = $this->snapshot($recipe);
                $this->replaceEditorialContent($recipe, $definition);
                $recipe->refresh();
                $after = $this->snapshot($recipe);

                RecipeProvenance::query()->updateOrCreate(
                    ['recipe_id' => $recipe->id, 'field_name' => 'ai_image_fidelity_correction'],
                    [
                        'public_id' => (string) Str::ulid(),
                        'source_type' => 'original_content',
                        'license' => 'House Chef Cyprus original editorial content',
                        'content_hash' => hash('sha256', json_encode($after, JSON_THROW_ON_ERROR)),
                        'raw_payload' => [
                            'before' => $before,
                            'after' => $after,
                            'reason' => 'AI görsel pilotu öncesinde tarif adı, malzemeler, ölçüler ve adımlar arasındaki genel şablon uyumsuzluğu editoryal olarak düzeltildi.',
                            'review_basis' => 'Başlık, kategori, mutfak, açıklama ve özgün yapım akışı birlikte değerlendirildi; haricî tarif metni kullanılmadı.',
                        ],
                        'retrieved_at' => now(),
                        'verification_status' => 'verified',
                        'confidence' => 90,
                        'reviewed_at' => now(),
                        'last_checked_at' => now(),
                        'is_active' => true,
                    ],
                );

                app(NutritionService::class)->invalidate($recipe);
                app(NutritionService::class)->snapshot($recipe->fresh()->load('recipeIngredients.ingredient.nutritionFacts.nutrient'));
                app(AllergenAnalysisService::class)->analyse($recipe->fresh()->load('recipeIngredients.ingredient.allergenDetails.allergen'));
                app(DietaryCompatibilityService::class)->assess($recipe->fresh()->load('recipeIngredients.ingredient.nutritionFacts.nutrient', 'recipeIngredients.ingredient.allergenDetails.allergen', 'dietaryTags'));

                $result['corrected']++;
                $result['recipes'][] = ['slug' => $recipe->slug, 'title' => $recipe->title, 'status' => 'corrected', 'before' => $before['ingredients'], 'after' => $after['ingredients']];
            });
        }

        return $result;
    }

    /** @param array<string,mixed> $definition */
    private function replaceEditorialContent(Recipe $recipe, array $definition): void
    {
        $recipe->update([
            'excerpt' => $definition['excerpt'],
            'description' => $definition['description'],
            'equipment' => $definition['equipment'],
            'tips' => $definition['tips'],
            'common_mistakes' => $definition['common_mistakes'],
            'serving_suggestions' => $definition['serving_suggestions'],
            'storage_notes' => $definition['storage_notes'],
            // The records are a reviewed editorial correction, not a new import.
            'verification_status' => 'verified',
            'editorial_status' => 'published',
            'editorial_reviewed_at' => now(),
        ]);

        $recipe->ingredients()->detach();
        $recipe->ingredientGroups()->delete();
        $recipe->steps()->delete();

        foreach ($definition['ingredients'] as $position => $line) {
            $ingredient = $this->ingredient($line['ingredient']);
            $this->ensureKnownAllergens($ingredient, $line['allergens'] ?? []);
            $groupName = $line['group'] ?? 'Ana malzemeler';
            $group = RecipeIngredientGroup::query()->firstOrCreate(
                ['recipe_id' => $recipe->id, 'name' => $groupName],
                ['public_id' => (string) Str::ulid(), 'sort_order' => $groupName === 'Ana malzemeler' ? 1 : 2],
            );
            $canonicalUnit = $line['canonical_unit'];
            $canonicalQuantity = (string) $line['canonical_quantity'];
            $conversion = $this->conversion($ingredient, $line['display_measure_code'], $canonicalUnit, $canonicalQuantity, (string) $line['display_quantity']);
            $recipe->ingredients()->attach($ingredient->id, [
                'recipe_ingredient_group_id' => $group->id,
                'quantity' => (string) $line['display_quantity'],
                'display_quantity' => (string) $line['display_quantity'],
                'unit' => $line['display_measure_code'],
                'display_measure_code' => $line['display_measure_code'],
                'display_note' => $line['display_note'] ?? null,
                'grams' => $canonicalUnit === 'g' ? $canonicalQuantity : null,
                'canonical_quantity' => $canonicalQuantity,
                'canonical_unit' => $canonicalUnit,
                'ingredient_unit_conversion_id' => $conversion?->id,
                'conversion_confidence' => 'editorial_reviewed',
                'conversion_method' => 'editorial_canonical_measurement',
                'preparation_note' => $line['preparation_note'] ?? null,
                'group_name' => $groupName,
                'scaling_behavior' => 'scalable',
                'is_required' => true,
                'is_optional' => false,
                'sort_order' => $position + 1,
            ]);
        }

        foreach ($definition['steps'] as $position => $step) {
            RecipeStep::query()->create([
                'public_id' => (string) Str::ulid(),
                'recipe_id' => $recipe->id,
                'position' => $position + 1,
                'title' => $step[0],
                'instruction' => $step[1],
                'duration_seconds' => $step[2],
                'equipment' => $step[3],
                'timer_required' => $position === 1,
                'chef_tip' => $step[4] ?? null,
            ]);
        }

        if (isset($definition['dietary_tags'])) {
            $tagIds = DietaryTag::query()->whereIn('code', $definition['dietary_tags'])->pluck('id')->all();
            $recipe->dietaryTags()->sync($tagIds);
        }
    }

    private function ingredient(string $name): Ingredient
    {
        $slug = Str::slug($name);

        return Ingredient::query()->firstOrCreate(
            ['slug' => $slug],
            ['public_id' => (string) Str::ulid(), 'name' => Str::headline($name), 'canonical_name' => Str::headline($name), 'default_unit' => 'g', 'is_active' => true],
        );
    }

    private function conversion(Ingredient $ingredient, string $measure, string $unit, string $canonicalQuantity, string $displayQuantity): ?IngredientUnitConversion
    {
        if (! in_array($measure, ['g', 'ml'], true)) {
            return null;
        }

        return IngredientUnitConversion::query()->updateOrCreate(
            ['ingredient_id' => $ingredient->id, 'household_measure_code' => $measure, 'household_quantity' => $displayQuantity, 'canonical_unit' => $unit, 'is_active' => true],
            ['public_id' => (string) Str::ulid(), 'canonical_quantity' => $canonicalQuantity, 'source_type' => 'editorial_correction', 'source_name' => 'House Chef Cyprus pilot editoryal ölçü düzeltmesi', 'verification_status' => 'medium', 'confidence_score' => 82, 'conversion_method' => 'editorial_canonical_measurement', 'notes' => 'Pilot görsel doğruluğu için tarif bağlamında editoryal olarak gözden geçirildi.'],
        );
    }

    /** @param list<string> $allergens */
    private function ensureKnownAllergens(Ingredient $ingredient, array $allergens): void
    {
        foreach ($allergens as $code) {
            $allergen = Allergen::query()->where('code', $code)->first();
            if ($allergen === null) {
                continue;
            }
            IngredientAllergenDetail::query()->updateOrCreate(
                ['ingredient_id' => $ingredient->id, 'allergen_id' => $allergen->id],
                ['relation_type' => 'contains', 'confidence_score' => 90, 'source_type' => 'editorial_correction', 'source_name' => 'House Chef Cyprus pilot editoryal incelemesi', 'is_verified' => true, 'is_active' => true, 'notes' => 'Tarif bağlamı için doğrulanmış temel alerjen ilişkisi.'],
            );
        }
    }

    /** @return array{ingredients:list<string>,steps:list<string>,description:string} */
    private function snapshot(Recipe $recipe): array
    {
        $recipe->loadMissing(['ingredients', 'steps']);

        return [
            'ingredients' => $recipe->ingredients->map(fn (Ingredient $ingredient): string => $ingredient->canonical_name ?: $ingredient->name)->values()->all(),
            'steps' => $recipe->steps->map(fn (RecipeStep $step): string => $step->instruction)->values()->all(),
            'description' => (string) $recipe->description,
        ];
    }

    /** @return array<string,array<string,mixed>> */
    private function definitions(): array
    {
        $base = fn (string $excerpt, string $description, array $ingredients, array $steps, array $dietaryTags = []): array => [
            'excerpt' => $excerpt,
            'description' => $description,
            'ingredients' => $ingredients,
            'steps' => $steps,
            'dietary_tags' => $dietaryTags,
            'equipment' => ['Kesme tahtası', 'Bıçak', 'Ölçü kabı', 'Tencere veya tava'],
            'tips' => ['Malzemeleri ölçüp sırayla hazırlayın.', 'Kıvamı servis öncesinde kontrol edin.'],
            'common_mistakes' => ['Malzemeleri aynı anda ekleyip pişirme sırasını kaybetmek.', 'Tuz ve ekşiliği son kontrolden önce kesinleştirmek.'],
            'serving_suggestions' => 'Tarifi taze garnitürle, sıcaklığı ve kıvamı yerindeyken servis edin.',
            'storage_notes' => ['buzdolabi_gun' => 2, 'uyari' => 'Oda sıcaklığına geldikten sonra kapalı kapta saklayın.'],
        ];
        $line = fn (string $ingredient, string|int|float $quantity, string $unit = 'g', ?string $group = null, array $allergens = []): array => [
            'ingredient' => $ingredient, 'display_quantity' => $quantity, 'display_measure_code' => $unit, 'canonical_quantity' => $quantity, 'canonical_unit' => $unit, 'group' => $group ?? 'Ana malzemeler', 'allergens' => $allergens,
        ];

        return [
            'kibris-usulu-firin-tavuk' => $base('Limon, domates ve patatesle aynı tepside pişen, ev usulü Kıbrıs esintili tavuk yemeği.', 'Kemikli tavuk parçaları, patates ve domatesin limonlu-zeytinyağlı bir sosla fırında ağır ağır piştiği özgün ev mutfağı yorumu.', [
                $line('tavuk', 800), $line('patates', 500), $line('domates', 250), $line('soğan', 120), $line('sarımsak', 12, 'g', 'Sos ve lezzet'), $line('limon', 90, 'g', 'Sos ve lezzet'), $line('zeytinyağı', 30, 'ml', 'Sos ve lezzet'),
            ], [['Hazırlık', 'Tavuğu, iri doğranmış patatesi, domatesi ve soğanı fırın kabına yerleştirin.', 900, 'Fırın kabı'], ['Soslama ve pişirme', 'Sarımsak, limon ve zeytinyağını karıştırıp tepsiye gezdirin; 190 derecede tavuk kızarana kadar pişirin.', 2700, 'Fırın'], ['Servis', 'Tepsiyi kısa süre dinlendirip tavuk ve sebzeleri sıcak servis edin.', 300, 'Servis tabağı']]),
            'limonlu-akdeniz-makarnasi' => $base('Limon kabuğu, domates ve zeytinyağıyla ferahlatılmış hızlı Akdeniz makarnası.', 'Makarnanın nişastalı pişirme suyuyla bağlanan limonlu-zeytinyağlı sos; domates ve maydanozla tamamlanır.', [
                $line('makarna', 320), $line('domates', 240), $line('limon', 90), $line('sarımsak', 9, 'g', 'Sos ve lezzet'), $line('zeytinyağı', 30, 'ml', 'Sos ve lezzet'), $line('maydanoz', 15, 'g', 'Sos ve lezzet'),
            ], [['Makarnayı haşlama', 'Makarnayı tuzlu suda diri kalacak şekilde haşlayın ve bir miktar pişirme suyunu ayırın.', 720, 'Tencere'], ['Sosu hazırlama', 'Zeytinyağında sarımsağı kısa süre çevirin; domatesi, limon kabuğunu ve suyunu ekleyin.', 420, 'Tava'], ['Birleştirme', 'Makarnayı sosla ve ayırdığınız suyla karıştırın; maydanozla tamamlayın.', 240, 'Tava']]),
            'sebzeli-teriyaki-tavuk' => $base('Tavuk, biber ve havucun parlak teriyaki sosla buluştuğu hızlı tava yemeği.', 'İnce doğranmış tavuk ve sebzeler yüksek ısıda kısa süre pişirilir; sos en son eklenerek parlak kalması sağlanır.', [
                $line('tavuk', 600), $line('biber', 240), $line('havuç', 180), $line('soya sosu', 45, 'ml', 'Sos ve lezzet', ['soy']), $line('sarımsak', 9, 'g', 'Sos ve lezzet'), $line('zeytinyağı', 15, 'ml', 'Sos ve lezzet'),
            ], [['Hazırlık', 'Tavuğu ince şeritler halinde, biberi ve havucu eşit boyda doğrayın.', 600, 'Bıçak ve kesme tahtası'], ['Yüksek ısıda pişirme', 'Tavuğu tavada renk alana kadar pişirin; sebzeleri ekleyip diri kalacak şekilde çevirin.', 600, 'Geniş tava'], ['Soslama', 'Sarımsak ve soya sosunu ekleyip bir iki dakika karıştırın; sos parlaklaşınca servis edin.', 180, 'Geniş tava']]),
            'firin-sebzeli-taco-kasesi' => $base('Fırınlanmış sebzeler, fasulye ve pirinçle hazırlanan renkli taco esintili kase.', 'Baharatlı fırın sebzeleri; fasulye, pirinç, domates ve limonla aynı kasede dengeli bir öğüne dönüşür.', [
                $line('fasulye', 400), $line('pirinç', 240), $line('biber', 240), $line('domates', 200), $line('limon', 90, 'g', 'Sos ve lezzet'), $line('zeytinyağı', 30, 'ml', 'Sos ve lezzet'),
            ], [['Sebzeleri hazırlama', 'Biberleri doğrayıp zeytinyağıyla fırın tepsisine yayın.', 420, 'Fırın tepsisi'], ['Fırınlama', 'Sebzeleri 210 derecede kenarları renk alana kadar fırınlayın.', 1200, 'Fırın'], ['Kaseyi kurma', 'Pirinç, fasulye, fırın sebzeleri ve domatesi kaselere paylaştırın; limonla bitirin.', 300, 'Servis kaseleri']], ['vegetarian']),
            'nohutlu-akdeniz-kasesi' => $base('Nohut, salatalık, domates ve limonlu zeytinyağıyla hazırlanan serin Akdeniz kasesi.', 'Haşlanmış nohut; taze sebzeler, maydanoz ve limonlu zeytinyağı sosuyla bir araya gelir.', [
                $line('nohut', 400), $line('salatalık', 250), $line('domates', 250), $line('maydanoz', 20, 'g', 'Sos ve lezzet'), $line('limon', 90, 'g', 'Sos ve lezzet'), $line('zeytinyağı', 30, 'ml', 'Sos ve lezzet'),
            ], [['Doğrama', 'Salatalık ve domatesi eşit küçük küpler halinde doğrayın.', 480, 'Bıçak ve kesme tahtası'], ['Sos', 'Limon suyu, zeytinyağı ve ince kıyılmış maydanozu karıştırın.', 120, 'Karıştırma kabı'], ['Birleştirme', 'Nohut, sebzeler ve sosu nazikçe karıştırıp kısa süre dinlendirin.', 180, 'Karıştırma kabı']], ['vegan', 'vegetarian']),
            'baklava-dilimi' => $base('İnce yufka katları, ceviz ve hafif şerbetle hazırlanan küçük baklava dilimleri.', 'İnce yufkalar yağlanıp cevizle katlanır; fırınlandıktan sonra ılık şerbetle dengelenir. Bu özgün ev mutfağı sürümü kontrollü porsiyonlar içindir.', [
                $line('baklavalık yufka', 300, 'g', 'Ana malzemeler', ['gluten']), $line('ceviz', 180, 'g', 'Ana malzemeler', ['tree_nuts']), $line('tereyağı', 160, 'g', 'Ana malzemeler', ['milk']), $line('şeker', 300, 'g', 'Şerbet'), $line('limon', 30, 'g', 'Şerbet'),
            ], [['Şerbet', 'Şeker ve suyu kaynatın; limonla kısa süre kaynatıp soğumaya bırakın.', 900, 'Tencere'], ['Katlama', 'Yufkaları yağlayıp ceviz serperek katlayın ve dilimleyin.', 1200, 'Fırın kabı'], ['Pişirme ve şerbetleme', '170 derecede kızarana kadar pişirin; ilk sıcaklığı çıktıktan sonra ılık şerbeti ekleyin.', 2100, 'Fırın'], ['Dinlendirme', 'Şerbeti çekmesi için oda sıcaklığında dinlendirin.', 1800, 'Servis kabı']], ['vegetarian']),
            'zeytinyagli-nohutlu-pilav' => $base('Nohut, pirinç ve zeytinyağıyla hazırlanan sade, taneli pilav.', 'Pirincin kısa süre zeytinyağında çevrildiği, haşlanmış nohutla tamamlanan ev usulü pilav.', [
                $line('pirinç', 320), $line('nohut', 260), $line('soğan', 100), $line('zeytinyağı', 30, 'ml', 'Sos ve lezzet'), $line('limon', 30, 'g', 'Sos ve lezzet'),
            ], [['Ön hazırlık', 'Pirinci yıkayıp süzün; soğanı küçük küpler halinde doğrayın.', 480, 'Süzgeç'], ['Pilavı pişirme', 'Soğanı zeytinyağında yumuşatın, pirinci ekleyin; sıcak suyu ve nohudu katıp kısık ateşte çektirin.', 1800, 'Kapaklı tencere'], ['Dinlendirme', 'Ocağı kapatıp pilavı kapalı tencerede dinlendirin; limonla servis edin.', 600, 'Kapaklı tencere']], ['vegan', 'vegetarian']),
            'cacik' => $base('Yoğurt, salatalık, sarımsak ve nane ile hazırlanan serinletici cacık.', 'Yoğurt suyla kıvamlandırılır; salatalık, sarımsak ve nane eklenir. Serviste zeytinyağı isteğe bağlı ince bir çizgi olarak kullanılır.', [
                $line('yoğurt', 600, 'g', 'Ana malzemeler', ['milk']), $line('salatalık', 300), $line('sarımsak', 6, 'g', 'Sos ve lezzet'), $line('nane', 8, 'g', 'Sos ve lezzet'), $line('zeytinyağı', 10, 'ml', 'Sos ve lezzet'),
            ], [['Hazırlık', 'Salatalığı küçük küpler halinde doğrayın; sarımsağı ezin.', 420, 'Bıçak ve kesme tahtası'], ['Karıştırma', 'Yoğurdu soğuk suyla açın, salatalık, sarımsak ve naneyi ekleyin.', 180, 'Karıştırma kabı'], ['Servis', 'Cacığı soğuk servis edin; isterseniz zeytinyağını yüzeyine gezdirin.', 120, 'Servis kasesi']], ['vegetarian']),
        ];
    }
}
