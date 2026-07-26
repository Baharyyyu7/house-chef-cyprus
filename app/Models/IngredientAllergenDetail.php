<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientAllergenDetail extends Model
{
    protected $fillable = ['ingredient_id', 'allergen_id', 'relation_type', 'confidence_score', 'source_type', 'is_verified', 'notes'];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean'];
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /** @return BelongsTo<Allergen, $this> */
    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class);
    }
}
