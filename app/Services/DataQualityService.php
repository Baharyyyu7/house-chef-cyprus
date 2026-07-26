<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataQualityIssue;
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

    /** @return array{new:int,updated:int,resolved:int,score:int} */
    public function scan(): array
    {
        $found = $this->report();
        $keys = [];
        $new = 0;
        $updated = 0;
        foreach ($found as $item) {
            $keys[] = $item['issue_type'].'|'.$item['entity_public_id'];
            $issue = DataQualityIssue::query()->where('issue_type', $item['issue_type'])->where('entity_public_id', $item['entity_public_id'])->where('resolution_status', 'open')->first();
            if ($issue) {
                $issue->update($item + ['last_detected_at' => now()]);
                $updated++;
            } else {
                DataQualityIssue::create($item + ['detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open']);
                $new++;
            }
        } $resolved = 0;
        foreach (DataQualityIssue::query()->where('resolution_status', 'open')->get() as $issue) {
            if (! in_array($issue->issue_type.'|'.$issue->entity_public_id, $keys, true)) {
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

    private function issue(string $type, string $severity, Recipe $recipe, string $message): array
    {
        return compact('type', 'severity', 'message') + ['issue_type' => $type, 'entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id];
    }
}
