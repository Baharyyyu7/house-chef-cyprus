<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RecipeAllergenOverride extends Model
{
    protected $table = 'recipe_allergens';

    public $timestamps = false;

    protected $fillable = ['recipe_id', 'allergen_id', 'risk_type', 'derived_from', 'source_type', 'source_name', 'is_verified', 'verified_at', 'notes', 'is_active', 'updated_by', 'updated_at'];

    /** @return BelongsTo<Allergen, $this> */
    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class);
    }
}
