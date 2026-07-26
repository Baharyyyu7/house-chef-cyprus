<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = ['recipe_id', 'ingredient_id', 'quantity', 'unit', 'unit_id', 'grams', 'preparation', 'preparation_note', 'substitution', 'is_required', 'is_optional', 'group_name', 'can_be_substituted', 'sort_order'];

    protected function casts(): array
    {
        return ['is_optional' => 'boolean', 'is_required' => 'boolean', 'can_be_substituted' => 'boolean'];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
