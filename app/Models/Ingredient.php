<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** @property string $public_id */
class Ingredient extends Model
{
    use HasPublicId;

    protected $fillable = ['name', 'canonical_name', 'slug', 'category', 'aliases', 'allergens', 'default_unit', 'default_unit_id', 'is_active', 'is_allergen', 'is_perishable', 'typical_shelf_life_days', 'storage_type', 'notes'];

    protected function casts(): array
    {
        return ['aliases' => 'array', 'allergens' => 'array', 'is_active' => 'boolean', 'is_allergen' => 'boolean', 'is_perishable' => 'boolean'];
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /** @return HasMany<IngredientAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(IngredientAlias::class);
    }

    /** @return HasMany<IngredientSubstitution, $this> */
    public function substitutions(): HasMany
    {
        return $this->hasMany(IngredientSubstitution::class);
    }

    /** @return BelongsTo<Unit, $this> */
    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }

    /** @return MorphMany<NutritionFact, $this> */
    public function nutritionFacts(): MorphMany
    {
        return $this->morphMany(NutritionFact::class, 'nutritionable');
    }

    /** @return HasMany<IngredientAllergenDetail, $this> */
    public function allergenDetails(): HasMany
    {
        return $this->hasMany(IngredientAllergenDetail::class);
    }

    /** @return MorphMany<Media, $this> */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }
}
