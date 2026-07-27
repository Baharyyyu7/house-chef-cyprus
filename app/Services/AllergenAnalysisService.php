<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;

final class AllergenAnalysisService
{
    /** @return array<string, mixed> */
    public function analyse(Recipe $recipe): array
    {
        $groups = ['contains' => [], 'may_contain' => [], 'cross_contamination_risk' => [], 'derived_from' => [], 'unverified' => []];
        $lines = $recipe->relationLoaded('recipeIngredients')
            ? $recipe->recipeIngredients
            : $recipe->recipeIngredients()->with('ingredient.allergenDetails.allergen')->get();
        foreach ($lines as $line) {
            $ingredient = $line->relationLoaded('ingredient') ? $line->ingredient : $line->ingredient()->firstOrFail();
            $details = $ingredient->relationLoaded('allergenDetails') ? $ingredient->allergenDetails : $ingredient->allergenDetails()->with('allergen')->get();
            foreach ($details as $detail) {
                $type = $detail->relation_type;
                $allergen = $detail->relationLoaded('allergen') ? $detail->allergen : $detail->allergen()->firstOrFail();
                $item = ['public_id' => $allergen->public_id, 'name' => $allergen->name, 'confidence_score' => $detail->confidence_score, 'ingredient' => $ingredient->name, 'optional' => (bool) $line->is_optional, 'verified' => (bool) $detail->is_verified];
                $groups[$type][] = $item;
                if (! $detail->is_verified) {
                    $groups['unverified'][] = $item;
                }
            }
        }
        $overrides = $recipe->relationLoaded('allergenOverrideRecords')
            ? $recipe->allergenOverrideRecords->where('is_active', true)->sortByDesc('is_verified')
            : $recipe->allergenOverrideRecords()->with('allergen')->where('is_active', true)->orderByDesc('is_verified')->get();
        foreach ($overrides as $override) {
            $allergen = $override->relationLoaded('allergen') ? $override->allergen : $override->allergen()->firstOrFail();
            $type = $override->risk_type;
            if ($type === 'does_not_contain') {
                $groups['contains'] = array_values(array_filter($groups['contains'], fn (array $item): bool => $item['public_id'] !== $allergen->public_id));

                continue;
            }
            $groups[$type][] = ['public_id' => $allergen->public_id, 'name' => $allergen->name, 'confidence_score' => 100, 'ingredient' => null, 'optional' => false, 'verified' => true, 'override' => true, 'reason' => $override->derived_from];
        }
        foreach ($groups as &$items) {
            $unique = [];
            foreach ($items as $item) {
                $unique[$item['public_id'].'-'.$item['ingredient'].'-'.($item['optional'] ? '1' : '0')] = $item;
            }
            $deduplicated = [];
            foreach ($unique as $item) {
                $deduplicated[] = $item;
            }
            $items = $deduplicated;
        }
        unset($items);

        return ['groups' => $groups, 'has_confirmed_allergens' => $groups['contains'] !== [], 'medical_disclaimer' => 'Alerjen bilgileri ürün etiketlerinin ve bireysel hassasiyetlerin yerini tutmaz.'];
    }
}
