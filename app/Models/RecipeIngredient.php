<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasPublicId;

    protected $fillable = ['recipe_id', 'recipe_ingredient_group_id', 'ingredient_id', 'quantity', 'display_quantity', 'unit', 'display_measure_code', 'display_note', 'unit_id', 'grams', 'canonical_quantity', 'canonical_unit', 'ingredient_unit_conversion_id', 'conversion_confidence', 'conversion_method', 'preparation', 'preparation_note', 'substitution', 'is_required', 'is_optional', 'group_name', 'can_be_substituted', 'scaling_behavior', 'scaling_note', 'manual_override_reason', 'sort_order'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:4', 'display_quantity' => 'decimal:4', 'grams' => 'decimal:4', 'canonical_quantity' => 'decimal:4', 'is_optional' => 'boolean', 'is_required' => 'boolean', 'can_be_substituted' => 'boolean'];
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

    /** @return BelongsTo<IngredientUnitConversion, $this> */
    public function conversion(): BelongsTo
    {
        return $this->belongsTo(IngredientUnitConversion::class, 'ingredient_unit_conversion_id');
    }

    /** @return BelongsTo<RecipeIngredientGroup,$this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(RecipeIngredientGroup::class, 'recipe_ingredient_group_id');
    }
}
