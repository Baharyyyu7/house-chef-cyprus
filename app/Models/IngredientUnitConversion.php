<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientUnitConversion extends Model
{
    use HasPublicId;

    protected $fillable = ['ingredient_id', 'household_measure_code', 'household_quantity', 'canonical_quantity', 'canonical_unit', 'density_g_per_ml', 'source_type', 'source_name', 'source_url', 'verification_status', 'confidence_score', 'conversion_method', 'reviewed_at', 'reviewed_by_user_id', 'notes', 'is_active', 'valid_from', 'valid_until'];

    protected function casts(): array
    {
        return ['household_quantity' => 'decimal:4', 'canonical_quantity' => 'decimal:4', 'density_g_per_ml' => 'decimal:6', 'confidence_score' => 'integer', 'is_active' => 'boolean', 'reviewed_at' => 'datetime', 'valid_from' => 'datetime', 'valid_until' => 'datetime'];
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
