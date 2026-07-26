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
        foreach ($recipe->recipeIngredients()->with('ingredient.allergenDetails.allergen')->get() as $line) {
            $ingredient = $line->ingredient()->firstOrFail();
            foreach ($ingredient->allergenDetails()->with('allergen')->get() as $detail) {
                $type = $detail->relation_type;
                $allergen = $detail->allergen()->firstOrFail();
                $item = ['public_id' => $allergen->public_id, 'name' => $allergen->name, 'confidence_score' => $detail->confidence_score, 'ingredient' => $ingredient->name, 'optional' => (bool) $line->is_optional, 'verified' => (bool) $detail->is_verified];
                $groups[$type][] = $item;
                if (! $detail->is_verified) {
                    $groups['unverified'][] = $item;
                }
            }
        }
        foreach ($recipe->allergenOverrideRecords()->with('allergen')->get() as $override) {
            $allergen = $override->allergen()->firstOrFail();
            $type = $override->risk_type;
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
