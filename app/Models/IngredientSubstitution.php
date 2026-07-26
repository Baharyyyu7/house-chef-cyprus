<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientSubstitution extends Model
{
    protected $fillable = ['ingredient_id', 'substitute_ingredient_id', 'substitution_type', 'ratio', 'unit_compatibility', 'context', 'notes', 'confidence_score', 'is_verified', 'is_active'];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'substitute_ingredient_id');
    }
}
