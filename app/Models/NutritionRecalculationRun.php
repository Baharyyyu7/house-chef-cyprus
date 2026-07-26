<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NutritionRecalculationRun extends Model
{
    protected $fillable = ['recipe_id', 'triggered_by', 'status', 'attempt', 'started_at', 'finished_at', 'error_code', 'error_message', 'previous_confidence', 'new_confidence', 'previous_completeness', 'new_completeness'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
