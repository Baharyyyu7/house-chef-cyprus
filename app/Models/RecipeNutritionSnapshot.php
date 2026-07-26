<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeNutritionSnapshot extends Model
{
    protected $fillable = ['recipe_id', 'servings', 'payload', 'completeness_percentage', 'confidence_score', 'source_version', 'calculated_at', 'invalidated_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'calculated_at' => 'datetime', 'invalidated_at' => 'datetime'];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
