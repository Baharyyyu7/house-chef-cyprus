<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $public_id
 * @property array<string, float|int|string>|null $nutrition
 */
class Recipe extends Model
{
    use HasPublicId, HasTranslations, SoftDeletes;

    /**
     * Bir tarif sayfasını taşıyan alanlar. Kısmi çeviri yeterli değil:
     * başlığı çevrilmiş ama açıklaması Türkçe kalmış bir sayfa, çevrilmemiş
     * sayfanın kendisidir.
     *
     * @return list<string>
     */
    public function translatableFields(): array
    {
        return ['title', 'excerpt', 'description'];
    }

    protected $fillable = ['cuisine_id', 'category_id', 'content_factory_batch_id', 'data_source_id', 'external_id', 'content_origin', 'original_hash', 'first_imported_at', 'last_verified_at', 'title', 'slug', 'excerpt', 'description', 'country', 'region', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'dietary_tags', 'allergens', 'nutrition', 'storage_notes', 'source_name', 'source_url', 'reliability_score', 'status', 'is_featured', 'verification_status', 'editorial_status', 'content_factory_state', 'fact_check_required', 'completeness_score', 'completeness_status', 'content_factory_readiness_score', 'content_factory_rule_version', 'content_factory_checked_at', 'content_factory_metadata', 'editorial_reviewed_at', 'editorial_reviewed_by_user_id', 'human_approved_by_user_id', 'human_approved_at', 'published_at', 'equipment', 'tips', 'common_mistakes', 'serving_suggestions', 'reheating_notes', 'freezing_notes', 'cultural_notes', 'seasonality', 'image_status', 'image_license', 'image_attribution', 'image_reviewed_at'];

    protected function casts(): array
    {
        return ['dietary_tags' => 'array', 'allergens' => 'array', 'nutrition' => 'array', 'storage_notes' => 'array', 'equipment' => 'array', 'tips' => 'array', 'common_mistakes' => 'array', 'seasonality' => 'array', 'content_factory_metadata' => 'array', 'is_featured' => 'boolean', 'fact_check_required' => 'boolean', 'content_factory_checked_at' => 'datetime', 'editorial_reviewed_at' => 'datetime', 'human_approved_at' => 'datetime', 'published_at' => 'datetime', 'image_reviewed_at' => 'datetime'];
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

    /** @return BelongsTo<ContentFactoryBatch, $this> */
    public function contentFactoryBatch(): BelongsTo
    {
        return $this->belongsTo(ContentFactoryBatch::class);
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /** @return HasMany<RecipeIngredientGroup,$this> */
    public function ingredientGroups(): HasMany
    {
        return $this->hasMany(RecipeIngredientGroup::class)->orderBy('sort_order');
    }

    /** @return HasMany<RecipeStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('position');
    }

    /** @return BelongsToMany<Ingredient, $this> */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')->withPivot(['recipe_ingredient_group_id', 'quantity', 'display_quantity', 'unit', 'display_measure_code', 'display_note', 'grams', 'canonical_quantity', 'canonical_unit', 'ingredient_unit_conversion_id', 'conversion_confidence', 'conversion_method', 'preparation', 'preparation_note', 'substitution', 'is_optional', 'group_name', 'scaling_behavior', 'scaling_note', 'sort_order'])->withTimestamps();
    }

    /** @return MorphMany<Media, $this> */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /** @return HasMany<RecipeMediaCandidate, $this> */
    public function mediaCandidates(): HasMany
    {
        return $this->hasMany(RecipeMediaCandidate::class);
    }

    /** @return MorphOne<SeoMetadata, $this> */
    public function seoMetadata(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    /** @return HasMany<AiImageGeneration, $this> */
    public function aiImageGenerations(): HasMany
    {
        return $this->hasMany(AiImageGeneration::class)->latest();
    }

    /** @return BelongsToMany<Allergen, $this> */
    public function allergenOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'recipe_allergens')->withPivot(['risk_type', 'derived_from']);
    }

    /** @return HasMany<RecipeAllergenOverride, $this> */
    public function allergenOverrideRecords(): HasMany
    {
        return $this->hasMany(RecipeAllergenOverride::class);
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class, 'recipe_dietary_tag');
    }

    /** @return HasMany<RecipeNutritionSnapshot, $this> */
    public function nutritionSnapshots(): HasMany
    {
        return $this->hasMany(RecipeNutritionSnapshot::class);
    }

    /** @return HasMany<RecipeProvenance, $this> */
    public function provenances(): HasMany
    {
        return $this->hasMany(RecipeProvenance::class);
    }

    /** @return BelongsToMany<RecipeCollection, $this> */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(RecipeCollection::class, 'recipe_collection_items')->withTimestamps();
    }

    /**
     * Yayındaki 678 tarifin 450'si "{Mutfak} Usulü Limonlu {Malzeme}" kalıbıyla
     * toplu üretilmiş, 17'sinin başlığı sıfatta kesilmiş ("Peynirli", "Fırında").
     * Bu kayıtların adı bir yemeği tarif etmez: onlar için görsel ürettirmek de
     * yayında tutmak da anlamsızdır. Kural hem panelde hem görsel brief'inde
     * kullanılır, bu yüzden tek yerde durur.
     */
    public static function titleDescribesADish(string $title): bool
    {
        $title = trim($title);

        if ($title === '' || mb_strpos($title, 'Usulü Limonlu') !== false) {
            return false;
        }

        if (count(preg_split('/\s+/u', $title) ?: []) > 1) {
            return true;
        }

        // Tek kelimelik başlık "Menemen" ya da "Humus" olabilir; bunlar geçerli
        // yemek adlarıdır. Geçersiz olan, ardındaki ismi kaybetmiş sıfatlardır.
        $parcalar = ['Fırın', 'Fırında', 'Et', 'Tavuk', 'Konya', 'Şiş', 'Dürüm'];

        return ! in_array($title, $parcalar, true)
            && preg_match('/(lı|li|lu|lü)$/u', $title) !== 1;
    }

    public function totalMinutes(): int
    {
        return $this->prep_minutes + $this->cook_minutes + $this->rest_minutes;
    }
}
