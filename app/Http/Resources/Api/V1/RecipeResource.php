<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

final class RecipeResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['public_id' => $this->public_id, 'title' => $this->title, 'slug' => $this->slug, 'excerpt' => $this->excerpt, 'servings' => $this->servings, 'difficulty' => $this->difficulty, 'total_minutes' => $this->totalMinutes(), 'cuisine' => $this->cuisine?->only(['public_id', 'name', 'slug']), 'category' => $this->category?->only(['public_id', 'name', 'slug']), 'ingredients' => $this->whenLoaded('ingredients', fn () => $this->ingredients->map(fn ($i) => ['public_id' => $i->public_id, 'name' => $i->name, 'quantity' => $i->pivot->quantity, 'unit' => $i->pivot->unit]))];
    }
}
