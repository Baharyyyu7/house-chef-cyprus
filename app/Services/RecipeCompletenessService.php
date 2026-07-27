<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;

final class RecipeCompletenessService
{
    /** @return array{score:int,status:string,checks:array<string,array{complete:bool,weight:int,message:string}>} */
    public function evaluate(Recipe $recipe): array
    {
        $recipe->loadMissing(['recipeIngredients', 'steps', 'provenances', 'nutritionSnapshots', 'dietaryTags', 'media']);
        $checks = [
            'basic_information' => $this->check((bool) ($recipe->title && $recipe->excerpt && $recipe->description && $recipe->cuisine_id && $recipe->category_id && $recipe->servings > 0 && $recipe->totalMinutes() > 0), 15, 'Başlık, açıklama, sınıflandırma, porsiyon ve süre'),
            'ingredients' => $this->check($recipe->recipeIngredients->isNotEmpty(), 8, 'En az bir malzeme'),
            'household_measurement' => $this->check($recipe->recipeIngredients->isNotEmpty() && $recipe->recipeIngredients->every(fn ($line): bool => $line->display_quantity !== null && $line->display_measure_code !== null), 8, 'Ev ölçüleri'),
            'canonical_measurement' => $this->check($recipe->recipeIngredients->isNotEmpty() && $recipe->recipeIngredients->every(fn ($line): bool => $line->canonical_quantity !== null && $line->canonical_unit !== null && $line->conversion_confidence !== 'unknown'), 8, 'Canonical ölçüler ve güven'),
            'ingredient_groups' => $this->check($recipe->recipeIngredients->isNotEmpty() && $recipe->recipeIngredients->every(fn ($line): bool => $line->recipe_ingredient_group_id !== null || $line->group_name !== null), 4, 'Malzeme grupları'),
            'steps' => $this->check($recipe->steps->count() >= 3, 10, 'En az üç hazırlama adımı'),
            'equipment' => $this->check(! empty($recipe->equipment) || $recipe->steps->contains(fn ($step): bool => filled($step->equipment)), 5, 'Ekipman bilgisi'),
            'tips' => $this->check(! empty($recipe->tips) || $recipe->steps->contains(fn ($step): bool => filled($step->chef_tip)), 5, 'Püf noktası'),
            'storage' => $this->check(! empty($recipe->storage_notes), 5, 'Saklama bilgisi'),
            'nutrition' => $this->check($recipe->nutritionSnapshots->contains(fn ($snapshot): bool => $snapshot->invalidated_at === null), 8, 'Geçerli nutrition snapshot'),
            'allergens' => $this->check($recipe->allergens !== null, 5, 'Alerjen analizi'),
            'dietary' => $this->check($recipe->dietaryTags->isNotEmpty() || $recipe->dietary_tags !== null, 4, 'Diyet uygunluğu'),
            'media' => $this->check($recipe->image_status !== 'missing' && filled($recipe->image_license), 5, 'Görsel ve lisans durumu'),
            'provenance' => $this->check($recipe->provenances->contains(fn ($source): bool => $source->is_active && in_array($source->verification_status, ['researched', 'partially_verified', 'verified'], true)), 5, 'Aktif ve incelenmiş kaynak'),
            'editorial_review' => $this->check(in_array($recipe->verification_status, ['partially_verified', 'verified'], true) && $recipe->editorial_reviewed_at !== null, 10, 'Editoryal kontrol'),
        ];
        $score = (int) collect($checks)->filter(fn (array $check): bool => $check['complete'])->sum('weight');
        $status = $score >= 95 && $checks['provenance']['complete'] && $checks['editorial_review']['complete'] ? 'publishable' : ($score >= 75 ? 'ready_for_review' : ($score >= 45 ? 'needs_review' : 'incomplete'));

        return ['score' => $score, 'status' => $status, 'checks' => $checks];
    }

    public function refresh(Recipe $recipe): array
    {
        $result = $this->evaluate($recipe);
        $recipe->update(['completeness_score' => $result['score'], 'completeness_status' => $result['status']]);

        return $result;
    }

    /** @return array{complete:bool,weight:int,message:string} */
    private function check(bool $complete, int $weight, string $message): array
    {
        return ['complete' => $complete, 'weight' => $weight, 'message' => $message];
    }
}
