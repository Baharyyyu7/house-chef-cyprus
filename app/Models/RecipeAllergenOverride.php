<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RecipeAllergenOverride extends Model
{
    protected $table = 'recipe_allergens';

    public $timestamps = false;

    /** @return BelongsTo<Allergen, $this> */
    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class);
    }
}
