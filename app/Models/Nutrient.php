<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nutrient extends Model
{
    use HasPublicId;

    protected $fillable = ['code', 'name', 'category', 'unit', 'decimal_precision', 'daily_reference_value', 'is_macro', 'is_micronutrient', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_macro' => 'boolean', 'is_micronutrient' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return HasMany<NutritionFact, $this> */
    public function facts(): HasMany
    {
        return $this->hasMany(NutritionFact::class);
    }
}
