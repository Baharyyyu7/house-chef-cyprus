<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NutritionFact extends Model
{
    protected $fillable = ['nutrient_id', 'value', 'basis_type', 'basis_amount', 'source_type', 'source_name', 'confidence_score', 'calculation_method', 'origin', 'verified_at', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Nutrient, $this> */
    public function nutrient(): BelongsTo
    {
        return $this->belongsTo(Nutrient::class);
    }

    public function nutritionable(): MorphTo
    {
        return $this->morphTo();
    }
}
