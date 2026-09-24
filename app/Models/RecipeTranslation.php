<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<array{group?:string|null,text:string}>|null $ingredients
 * @property list<array{title:string,text:string,minutes?:int|null}>|null $steps
 * @property list<string>|null $equipment
 * @property list<string>|null $tips
 * @property list<string>|null $common_mistakes
 * @property list<string>|null $keywords
 */
final class RecipeTranslation extends Model
{
    protected $fillable = ['recipe_id', 'locale', 'slug', 'title', 'excerpt', 'description', 'cuisine_label', 'category_label', 'difficulty_label', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'ingredients', 'steps', 'equipment', 'tips', 'common_mistakes', 'keywords', 'serving_suggestions', 'storage_notes', 'freezing_notes', 'reheating_notes', 'image_alt', 'meta_title', 'meta_description', 'status', 'source_hash', 'translated_at'];

    protected function casts(): array
    {
        return ['ingredients' => 'array', 'steps' => 'array', 'equipment' => 'array', 'tips' => 'array', 'common_mistakes' => 'array', 'keywords' => 'array', 'translated_at' => 'datetime'];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Only translations whose Turkish source recipe is itself public may be
     * shown; a translation never makes a draft or unverified recipe public.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePubliclyVisible(Builder $query, string $locale): Builder
    {
        return $query->where($query->qualifyColumn('locale'), $locale)->where($query->qualifyColumn('status'), 'published')
            ->whereHas('recipe', fn (Builder $recipe) => $recipe->where($recipe->qualifyColumn('status'), 'published')->where($recipe->qualifyColumn('verification_status'), 'verified'));
    }

    public function prepMinutes(): int
    {
        return (int) ($this->prep_minutes ?? $this->recipe->prep_minutes ?? 0);
    }

    public function cookMinutes(): int
    {
        return (int) ($this->cook_minutes ?? $this->recipe->cook_minutes ?? 0);
    }

    public function totalMinutes(): int
    {
        return $this->prepMinutes() + $this->cookMinutes() + (int) ($this->rest_minutes ?? $this->recipe->rest_minutes ?? 0);
    }

    public function servingCount(): int
    {
        return max(1, (int) ($this->servings ?? $this->recipe->servings ?? 1));
    }
}
