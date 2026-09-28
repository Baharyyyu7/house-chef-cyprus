<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** @property string $public_id */
class Ingredient extends Model
{
    use HasPublicId, HasTranslations;

    /**
     * Malzeme adı tarif sayfasının yarısıdır ve malzemeler tarifler arasında
     * paylaşılır: bir kez çevrilen "hellim" bütün tariflerde çevrilmiş olur.
     *
     * @return list<string>
     */
    public function translatableFields(): array
    {
        return ['name'];
    }

    protected $fillable = ['name', 'canonical_name', 'slug', 'category', 'aliases', 'allergens', 'default_unit', 'default_unit_id', 'is_active', 'is_allergen', 'is_perishable', 'typical_shelf_life_days', 'storage_type', 'notes', 'icon_key', 'icon_label', 'icon_status'];

    protected function casts(): array
    {
        return ['aliases' => 'array', 'allergens' => 'array', 'is_active' => 'boolean', 'is_allergen' => 'boolean', 'is_perishable' => 'boolean'];
    }

    /** @return HasMany<RecipeIngredient, $this> */
    /**
     * Bu dilde yayındaki bir tarifte geçen malzemeler.
     *
     * Seçiciler bütün aktif malzemeleri listeliyordu: taslaktaki şablon
     * tariflerin "Ana Malzeme" ve "Sebze Karışımı" yer tutucuları dahil,
     * yabancı sayfada ham Türkçe adla. Seçilince hiçbir tarif getirmeyen
     * malzeme seçenek değil.
     */
    public function scopeYayindaKullanilan(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereHas('recipeIngredients.recipe', static fn (Builder $tarif): Builder => $tarif
            ->where('status', 'published')->where('verification_status', 'verified')->translatedInto());
    }

    /**
     * Seçici için sıralı liste; ad o dilde, sıralama o dilin alfabesiyle.
     *
     * @param  \Illuminate\Support\Collection<int, self>  $malzemeler
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function dileGoreSirala($malzemeler)
    {
        $harmanlayici = class_exists(\Collator::class) ? new \Collator(app()->getLocale()) : null;

        return $malzemeler->sort(static fn (self $a, self $b): int => $harmanlayici?->compare((string) $a->name, (string) $b->name)
            ?? strcmp((string) $a->name, (string) $b->name))->values();
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /** @return HasMany<IngredientUnitConversion, $this> */
    public function unitConversions(): HasMany
    {
        return $this->hasMany(IngredientUnitConversion::class);
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
