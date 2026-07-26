<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasPublicId;
    use SoftDeletes;

    protected $fillable = ['cuisine_id', 'category_id', 'data_source_id', 'external_id', 'content_origin', 'original_hash', 'first_imported_at', 'last_verified_at', 'title', 'slug', 'excerpt', 'description', 'country', 'region', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'dietary_tags', 'allergens', 'nutrition', 'storage_notes', 'source_name', 'source_url', 'reliability_score', 'status', 'is_featured'];

    protected function casts(): array
    {
        return ['dietary_tags' => 'array', 'allergens' => 'array', 'nutrition' => 'array', 'storage_notes' => 'array', 'is_featured' => 'boolean'];
    }

    /** @return BelongsTo<Cuisine, $this> */
    public function cuisine(): BelongsTo
    {
        return $this->belongsTo(Cuisine::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /** @return HasMany<RecipeStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    /** @return BelongsToMany<Ingredient, $this> */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')->withPivot(['quantity', 'unit', 'grams', 'preparation', 'substitution', 'is_optional', 'sort_order'])->withTimestamps();
    }

    /** @return MorphMany<Media, $this> */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'recipe_allergens')->withPivot(['risk_type', 'derived_from']);
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class, 'recipe_dietary_tag');
    }

    public function totalMinutes(): int
    {
        return $this->prep_minutes + $this->cook_minutes + $this->rest_minutes;
    }
}
