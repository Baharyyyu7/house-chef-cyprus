<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipeIngredientGroup extends Model
{
    use HasPublicId;

    protected $fillable = ['recipe_id', 'name', 'sort_order'];

    /** @return BelongsTo<Recipe,$this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return HasMany<RecipeIngredient,$this> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
