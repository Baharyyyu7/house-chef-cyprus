<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasTranslations;
use App\Services\Seo\RecipeTranslationGate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $public_id
 * @property Carbon|null $published_at
 * @property array<string, float|int|string>|null $nutrition
 */
class Recipe extends Model
{
    use HasPublicId, SoftDeletes;
    use \App\Models\Concerns\RedirectsOldSlugs;

    public function showRouteName(): string
    {
        return 'recipes.show';
    }

    use HasTranslations {
        hasTranslation as private kendiAlanlariCevrildi;
        scopeTranslatedInto as private kendiAlanlariIcinSuz;
    }

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

    /**
     * Bir tarif yalnız kendi metin alanları çevrildiğinde çevrilmiş sayılmaz.
     *
     * Tarifin gövdesi kendi sütunlarında değil, adımlarında ve malzemelerinde
     * durur. Yalnız başlık/özet/açıklama çevrilirse ortaya İngilizce başlıklı
     * ama Türkçe yönergeli bir sayfa çıkar - kapının tam olarak engellemek
     * için var olduğu şey. 680 tarif için bu, /en/ altında 680 yarı Türkçe
     * sayfa demekti.
     */
    public function hasTranslation(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        if (! $this->kendiAlanlariCevrildi($locale)) {
            return false;
        }

        if ($locale === (string) config('locales.default', 'tr')) {
            return true;
        }

        foreach ($this->steps as $adim) {
            if (! $adim->hasTranslation($locale)) {
                return false;
            }
        }

        foreach ($this->ingredients as $malzeme) {
            if (! $malzeme->hasTranslation($locale)) {
                return false;
            }
        }

        // Her adımı "In step 1, carefully proceed with the culinary
        // preparation..." diye başlayan çeviri, çeviri değil. 27 Eylül
        // 2026'da AdSense siteyi "düşük değerli içerik" diye reddettiğinde
        // altı dilde ~530 tarif sayfası böyleydi; noindex'tiler ama açıktılar.
        return app(RecipeTranslationGate::class)->engel($this, $locale) === null;
    }

    /**
     * Listeleme, sitemap ve hreflang aynı tanımı paylaşmak zorunda.
     *
     * hasTranslation() adımlara ve malzemelere bakıyorsa bu kapsam da
     * bakmalı; ayrışırlarsa hreflang listede olmayan bir sayfayı gösterir ve
     * Google kümenin tamamını atar.
     */
    public function scopeTranslatedInto(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        $query = $this->kendiAlanlariIcinSuz($query, $locale);

        if ($locale === (string) config('locales.default', 'tr')) {
            return $query;
        }

        // "Çevrilmemiş çocuğu olmayan tarif": çocuk başına tek tek bakmak
        // yerine eksikliği aramak, N+1 sorgu doğurmadan aynı sonucu verir.
        foreach ([
            ['steps', (new RecipeStep)->translatableFields()],
            ['ingredients', (new Ingredient)->translatableFields()],
        ] as [$iliski, $alanlar]) {
            foreach ($alanlar as $alan) {
                $query->whereDoesntHave($iliski, function (Builder $cocuk) use ($locale, $alan): void {
                    $cocuk->whereNotNull($alan)
                        ->whereRaw('TRIM('.$alan.') <> ?', [''])
                        ->whereDoesntHave('translations', function (Builder $ceviri) use ($locale, $alan): void {
                            $ceviri->where('locale', $locale)
                                ->where('field', $alan)
                                ->whereNotNull('value')
                                ->whereRaw('TRIM(value) <> ?', ['']);
                        });
                });
            }
        }

        $kalip = app(RecipeTranslationGate::class)->kalipTarifleri($locale);

        return $kalip === [] ? $query : $query->whereNotIn($this->qualifyColumn('id'), $kalip);
    }

    protected $fillable = ['cuisine_id', 'category_id', 'content_factory_batch_id', 'data_source_id', 'external_id', 'content_origin', 'original_hash', 'first_imported_at', 'last_verified_at', 'title', 'slug', 'excerpt', 'description', 'country', 'region', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'dietary_tags', 'allergens', 'nutrition', 'storage_notes', 'source_name', 'source_url', 'reliability_score', 'status', 'is_featured', 'verification_status', 'editorial_status', 'content_factory_state', 'fact_check_required', 'completeness_score', 'completeness_status', 'content_factory_readiness_score', 'content_factory_rule_version', 'content_factory_checked_at', 'content_factory_metadata', 'editorial_reviewed_at', 'editorial_reviewed_by_user_id', 'human_approved_by_user_id', 'human_approved_at', 'published_at', 'equipment', 'tips', 'common_mistakes', 'serving_suggestions', 'reheating_notes', 'freezing_notes', 'cultural_notes', 'seasonality', 'image_status', 'image_license', 'image_attribution', 'image_reviewed_at'];

    /**
     * Zorluk değerinin kanonik biçimi ve Türkçe karşılıkları.
     *
     * Alan iki dilde birden saklanıyordu: 176 yayındaki tarifin 112'si
     * "Kolay/Orta/Zor", 64'ü "easy/medium/hard". Sonuç, iki özelliğin
     * kataloğun ayrık yarılarını görmesiydi - herkese açık zorluk filtresi
     * Türkçe değerleri sorguluyor, kişiselleştirme profili İngilizce
     * değerleri. Kesişimleri boştu.
     *
     * Kanonik biçim İngilizce: kişiselleştirme ve API zaten onu kullanıyor,
     * dile bağlı değil. Türkçe yalnız gösterimde ve filtre girdisinde.
     */
    public const ZORLUKLAR = ['easy' => 'Kolay', 'medium' => 'Orta', 'hard' => 'Zor'];

    /** Herhangi bir yazımı kanonik değere çevirir; tanımadığını null yapar. */
    public static function zorlukKanonik(?string $deger): ?string
    {
        $deger = trim((string) $deger);

        if ($deger === '') {
            return null;
        }

        $kucuk = mb_strtolower($deger, 'UTF-8');

        foreach (self::ZORLUKLAR as $kanonik => $etiket) {
            if ($kucuk === $kanonik || $kucuk === mb_strtolower($etiket, 'UTF-8')) {
                return $kanonik;
            }
        }

        return null;
    }

    /** Gösterim etiketi. */
    public function zorlukEtiketi(): ?string
    {
        $kanonik = self::zorlukKanonik($this->difficulty);
        if ($kanonik === null) {
            return null;
        }

        $transKey = 'recipe.difficulty_'.$kanonik;
        $translated = __($transKey);
        if ($translated !== $transKey) {
            return $translated;
        }

        return self::ZORLUKLAR[$kanonik] ?? null;
    }

    /** Yazarken normalleştir ki karışım bir daha oluşmasın. */
    protected function difficulty(): Attribute
    {
        return Attribute::make(
            set: fn (?string $deger): ?string => self::zorlukKanonik($deger) ?? $deger,
        );
    }

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

    /** @return HasMany<RecipeRating, $this> */
    public function ratings(): HasMany
    {
        return $this->hasMany(RecipeRating::class);
    }

    /**
     * @return array{average: float|null, count: int}
     */
    public function ratingSummary(): array
    {
        if (! $this->exists) {
            return ['average' => null, 'count' => 0];
        }

        if ($this->relationLoaded('ratings')) {
            $toplam = $this->ratings->count();
            if ($toplam === 0) {
                return ['average' => null, 'count' => 0];
            }

            return [
                'average' => round((float) $this->ratings->avg('rating'), 1),
                'count' => $toplam,
            ];
        }

        try {
            $toplam = (int) $this->ratings()->count();
            if ($toplam === 0) {
                return ['average' => null, 'count' => 0];
            }

            return [
                'average' => round((float) $this->ratings()->avg('rating'), 1),
                'count' => $toplam,
            ];
        } catch (\Throwable) {
            return ['average' => null, 'count' => 0];
        }
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

    /** @return BelongsToMany<BlogPost, $this> */
    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class);
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
