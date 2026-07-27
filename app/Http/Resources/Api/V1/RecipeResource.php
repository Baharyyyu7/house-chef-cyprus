<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Recipe;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Recipe $resource */
final class RecipeResource extends JsonResource
{
    public function toArray($request): array
    {
        $recipe = $this->resource;

        return ['public_id' => $recipe->public_id, 'title' => $recipe->title, 'slug' => $recipe->slug, 'excerpt' => $recipe->excerpt, 'servings' => $recipe->servings, 'difficulty' => $recipe->difficulty, 'total_minutes' => $recipe->totalMinutes(), 'cuisine' => $recipe->cuisine?->only(['public_id', 'name', 'slug']), 'category' => $recipe->category?->only(['public_id', 'name', 'slug']), 'ingredients' => $this->whenLoaded('ingredients', fn () => $recipe->ingredients->map(fn ($ingredient) => ['public_id' => $ingredient->public_id, 'name' => $ingredient->name, 'quantity' => data_get($ingredient, 'pivot.quantity'), 'unit' => data_get($ingredient, 'pivot.unit')])), 'ingredient_lines' => $this->whenLoaded('recipeIngredients', fn () => $recipe->recipeIngredients->map(fn ($line) => ['ingredient' => ['public_id' => $line->ingredient?->public_id, 'name' => $line->ingredient?->name], 'display' => ['quantity' => $line->display_quantity, 'measure_code' => $line->display_measure_code, 'measure_label' => $line->display_measure_code, 'formatted' => null], 'canonical' => ['quantity' => $line->canonical_quantity, 'unit' => $line->canonical_unit], 'conversion' => ['confidence' => $line->conversion_confidence, 'verified' => $line->conversion_confidence === 'verified'], 'preparation_note' => $line->preparation_note, 'optional' => $line->is_optional, 'group' => $line->group_name]))];
    }
}
