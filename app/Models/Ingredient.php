<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property string $public_id */
class Ingredient extends Model
{
    use HasPublicId;

    protected $fillable = ['name', 'slug', 'aliases', 'allergens', 'default_unit'];

    protected function casts(): array
    {
        return ['aliases' => 'array', 'allergens' => 'array'];
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
