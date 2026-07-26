<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;

final class DataQualityService
{
    /** @return list<array<string,mixed>> */
    public function report(): array
    {
        $issues = [];
        foreach (Recipe::withCount(['recipeIngredients', 'steps'])->get() as $recipe) {
            if (! $recipe->title) {
                $issues[] = $this->issue('missing_recipe_title', 'critical', $recipe, 'Tarif adı eksik.');
            } if (! $recipe->description) {
                $issues[] = $this->issue('missing_description', 'warning', $recipe, 'Tarif açıklaması eksik.');
            } if (! $recipe->cuisine_id || ! $recipe->category_id) {
                $issues[] = $this->issue('missing_taxonomy', 'warning', $recipe, 'Mutfak veya kategori eksik.');
            } if (! $recipe->recipe_ingredients_count) {
                $issues[] = $this->issue('missing_ingredients', 'error', $recipe, 'Tarif malzemesiz.');
            } if (! $recipe->steps_count) {
                $issues[] = $this->issue('missing_steps', 'error', $recipe, 'Tarif adımsız.');
            } if (! $recipe->public_id) {
                $issues[] = $this->issue('missing_public_id', 'critical', $recipe, 'Public ID eksik.');
            }
        }

return $issues;
    }

    private function issue(string $type, string $severity, Recipe $recipe, string $message): array
    {
        return compact('type', 'severity', 'message') + ['issue_type' => $type, 'entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id];
    }
}
