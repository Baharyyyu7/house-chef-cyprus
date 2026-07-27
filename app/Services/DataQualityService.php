<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\IngredientAlias;
use App\Models\IngredientSubstitution;
use App\Models\IngredientUnitConversion;
use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Models\RecipeIngredient;

final class DataQualityService
{
    /** @return list<array<string,mixed>> */
    public function report(): array
    {
        $issues = [];
        foreach (Recipe::withCount(['recipeIngredients', 'steps'])->with('media')->lazyById((int) config('performance.quality_scan_chunk_size')) as $recipe) {
            if (! $recipe->title) {
                $issues[] = $this->issue('missing_recipe_title', 'critical', $recipe, 'Tarif adı eksik.');
            }
            if (! $recipe->description) {
                $issues[] = $this->issue('missing_description', 'warning', $recipe, 'Tarif açıklaması eksik.');
            }
            if (! $recipe->cuisine_id || ! $recipe->category_id) {
                $issues[] = $this->issue('missing_taxonomy', 'warning', $recipe, 'Mutfak veya kategori eksik.');
            }
            if (! $recipe->recipe_ingredients_count) {
                $issues[] = $this->issue('missing_ingredients', 'error', $recipe, 'Tarif malzemesiz.');
            }
            if (! $recipe->steps_count) {
                $issues[] = $this->issue('missing_steps', 'error', $recipe, 'Tarif adımsız.');
            }
            if (! $recipe->public_id) {
                $issues[] = $this->issue('missing_public_id', 'critical', $recipe, 'Public ID eksik.');
            }
            if (! preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', (string) $recipe->public_id)) {
                $issues[] = $this->issue('invalid_public_id', 'critical', $recipe, 'Public ID biçimi geçersiz.');
            }
            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $recipe->slug)) {
                $issues[] = $this->issue('invalid_slug', 'error', $recipe, 'Tarif slug biçimi geçersiz.');
            }
            if ($recipe->recipe_ingredients_count > 0 && $recipe->media->isEmpty()) {
                $issues[] = $this->issue('missing_media', 'info', $recipe, 'Tarif görseli yok.');
            }
            if ($recipe->content_origin === 'imported' && (! $recipe->source_name || ! $recipe->source_url)) {
                $issues[] = $this->issue('missing_provenance', 'warning', $recipe, 'İçe aktarılan tarifin kaynak bilgisi eksik.');
            }
        }

        foreach (Ingredient::query()->lazyById((int) config('performance.quality_scan_chunk_size')) as $ingredient) {
            if (! $ingredient->public_id) {
                $issues[] = $this->modelIssue('missing_ingredient_public_id', 'critical', $ingredient, 'Malzeme public ID eksik.');
            }
            if (! $ingredient->is_active && $ingredient->recipeIngredients()->exists()) {
                $issues[] = $this->modelIssue('inactive_ingredient_in_use', 'warning', $ingredient, 'Pasif malzeme aktif tarif ilişkilerinde kullanılıyor.');
            }
        }

        foreach (IngredientAlias::query()->select('normalized_alias')->groupBy('normalized_alias')->havingRaw('COUNT(*) > 1')->get() as $alias) {
            $issues[] = $this->genericIssue('duplicate_ingredient_alias', 'warning', IngredientAlias::class, (string) $alias->normalized_alias, 'Aynı normalize malzeme takma adı birden fazla kez kullanılmış.');
        }
        foreach (IngredientSubstitution::query()->whereColumn('ingredient_id', 'substitute_ingredient_id')->get() as $substitution) {
            $issues[] = $this->genericIssue('self_reference_substitution', 'error', IngredientSubstitution::class, (string) $substitution->id, 'Malzeme kendisine alternatif olarak tanımlanmış.');
        }
        foreach (RecipeIngredient::query()->where(fn ($query) => $query->where('quantity', '<', 0)->orWhere('grams', '<', 0))->get() as $item) {
            $issues[] = $this->genericIssue('negative_recipe_ingredient_quantity', 'error', RecipeIngredient::class, (string) $item->id, 'Tarif malzemesinde negatif miktar var.');
        }
        foreach (RecipeIngredient::query()->with('recipe')->get() as $item) {
            $entity = $item->recipe?->public_id;
            if ($item->display_quantity === null || ! $item->display_measure_code) {
                $issues[] = $this->genericIssue('missing_household_measurement', 'warning', RecipeIngredient::class, $entity, 'Tarif malzemesinde kullanıcıya gösterilecek ev ölçüsü eksik.');
            }
            if ($item->canonical_quantity === null || ! $item->canonical_unit) {
                $issues[] = $this->genericIssue('missing_canonical_measurement', 'warning', RecipeIngredient::class, $entity, 'Tarif malzemesinde canonical miktar eksik.');
            }
            if ($item->display_quantity !== null && (float) $item->display_quantity <= 0) {
                $issues[] = $this->genericIssue('invalid_household_quantity', 'error', RecipeIngredient::class, $entity, 'Tarif malzemesindeki ev ölçüsü sıfır veya negatif.');
            }
            if (in_array($item->conversion_confidence, ['low', 'unknown'], true)) {
                $issues[] = $this->genericIssue('low_confidence_conversion', 'info', RecipeIngredient::class, $entity, 'Tarif malzemesi düşük güvenli veya doğrulanmamış dönüşüm kullanıyor.');
            }
        }
        foreach (IngredientUnitConversion::query()->get() as $conversion) {
            if (! $conversion->source_name) {
                $issues[] = $this->genericIssue('missing_conversion_source', 'warning', IngredientUnitConversion::class, $conversion->public_id, 'Malzeme dönüşümünün kaynak bilgisi eksik.');
            }
            if ($conversion->canonical_quantity <= 0 || $conversion->household_quantity <= 0) {
                $issues[] = $this->genericIssue('invalid_conversion_quantity', 'error', IngredientUnitConversion::class, $conversion->public_id, 'Malzeme dönüşümünde geçersiz miktar var.');
            }
        }
        foreach (NutritionFact::query()->where('value', '<', 0)->get() as $fact) {
            $issues[] = $this->genericIssue('negative_nutrition_value', 'error', NutritionFact::class, (string) $fact->id, 'Besin değerinde negatif değer var.');
        }

        return $issues;
    }

    /** @return array{new:int,updated:int,resolved:int,score:int} */
    public function scan(): array
    {
        $found = $this->report();
        $keys = [];
        $new = 0;
        $updated = 0;
        foreach ($found as $item) {
            $key = $item['issue_type'].'|'.$item['entity_type'].'|'.$item['entity_public_id'];
            $keys[] = $key;
            $issue = DataQualityIssue::query()->where('issue_type', $item['issue_type'])->where('entity_type', $item['entity_type'])->where('entity_public_id', $item['entity_public_id'])->where('resolution_status', 'open')->first();
            if ($issue) {
                $issue->update($item + ['last_detected_at' => now()]);
                $updated++;
            } else {
                DataQualityIssue::create($item + ['detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open']);
                $new++;
            }
        }
        $resolved = 0;
        foreach (DataQualityIssue::query()->where('resolution_status', 'open')->get() as $issue) {
            if (! in_array($issue->issue_type.'|'.$issue->entity_type.'|'.$issue->entity_public_id, $keys, true)) {
                $issue->update(['resolution_status' => 'resolved', 'resolved_at' => now()]);
                $resolved++;
            }
        }

        return ['new' => $new, 'updated' => $updated, 'resolved' => $resolved, 'score' => $this->score()];
    }

    public function score(): int
    {
        $loss = DataQualityIssue::query()->where('resolution_status', 'open')->get()->sum(fn (DataQualityIssue $issue) => config('data_quality.penalties.'.$issue->severity, 0));

        return max(0, 100 - $loss);
    }

    /** @return array{overall:int,recipe:int,ingredient:int,nutrition:int,media:int,provenance:int} */
    public function categoryScores(): array
    {
        $issues = DataQualityIssue::query()->where('resolution_status', 'open')->get();
        $categories = [
            'recipe' => fn (DataQualityIssue $issue): bool => $issue->entity_type === Recipe::class,
            'ingredient' => fn (DataQualityIssue $issue): bool => in_array($issue->entity_type, [Ingredient::class, IngredientAlias::class, IngredientSubstitution::class], true),
            'nutrition' => fn (DataQualityIssue $issue): bool => $issue->entity_type === NutritionFact::class || str_contains($issue->issue_type, 'nutrition'),
            'media' => fn (DataQualityIssue $issue): bool => str_contains($issue->issue_type, 'media'),
            'provenance' => fn (DataQualityIssue $issue): bool => str_contains($issue->issue_type, 'provenance'),
        ];
        $scores = ['overall' => $this->score()];
        foreach ($categories as $category => $belongsToCategory) {
            $loss = $issues->filter($belongsToCategory)->sum(fn (DataQualityIssue $issue) => (int) config('data_quality.penalties.'.$issue->severity, 0));
            $scores[$category] = max(0, 100 - $loss);
        }

        return $scores;
    }

    private function issue(string $type, string $severity, Recipe $recipe, string $message): array
    {
        return $this->genericIssue($type, $severity, Recipe::class, $recipe->public_id, $message);
    }

    private function modelIssue(string $type, string $severity, Ingredient $ingredient, string $message): array
    {
        return $this->genericIssue($type, $severity, Ingredient::class, $ingredient->public_id, $message);
    }

    /** @return array<string, mixed> */
    private function genericIssue(string $type, string $severity, string $entityType, ?string $entityPublicId, string $message): array
    {
        return ['issue_type' => $type, 'severity' => $severity, 'message' => $message, 'entity_type' => $entityType, 'entity_public_id' => $entityPublicId];
    }
}
