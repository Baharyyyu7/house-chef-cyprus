<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allergen extends Model
{
    use HasPublicId;

    protected $fillable = ['public_id', 'code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<IngredientAllergenDetail, $this> */
    public function ingredientDetails(): HasMany
    {
        return $this->hasMany(IngredientAllergenDetail::class);
    }
}
