<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\DietaryCompatibilityService;
use App\Services\NutritionService;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

final class DatabaseRecipeSearch implements RecipeSearchInterface
{
    public function __construct(
        private readonly NutritionService $nutrition,
        private readonly DietaryCompatibilityService $dietary,
        private readonly AllergenAnalysisService $allergens,
        private readonly TurkishRecipeQueryParser $queryParser,
    ) {}

    public function search(array $filters): LengthAwarePaginator
    {
        $searchPlan = $this->queryParser->parse((string) ($filters['q'] ?? ''));
        $perPage = min((int) config('performance.pagination.api_max'), max(1, (int) ($filters['per_page'] ?? config('performance.pagination.public'))));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $hasNutritionFilter = isset($filters['min_protein']) || isset($filters['max_calories']) || ($filters['verified_only'] ?? false);
        $hasDietaryFilter = filled($filters['dietary_tag'] ?? $filters['diet'] ?? null);
        $excludedAllergens = array_values(array_unique(array_filter($filters['exclude_allergens'] ?? [$filters['exclude_allergen'] ?? null])));
        $hasAllergenFilter = $excludedAllergens !== [];
        $requiresInMemoryAnalysis = $hasNutritionFilter || $hasDietaryFilter || $hasAllergenFilter;

        $relations = [
            'cuisine:id,public_id,name,slug',
            'category:id,public_id,name,slug',
            // Card rendering intentionally runs the full public-media gate. Do
            // not trim the approval fields from this eager load: an incomplete
            // projection makes approved media look like a fallback.
            'media' => fn ($query) => $query->select([
                'id', 'mediable_type', 'mediable_id', 'kind', 'disk', 'path', 'source_name', 'source_url', 'provider_metadata',
                'alt_text', 'is_placeholder', 'visibility', 'media_status', 'verification_status',
                'license_review_status', 'attribution_review_status', 'generated_by_ai', 'responsive_variants', 'sort_order',
                'fidelity_status', 'fidelity_confidence', 'checksum_sha256', 'perceptual_hash',
                'media_reviewer_user_id', 'media_reviewed_at', 'approved_by_user_id', 'approved_at',
            ])->where('visibility', 'public'),
            'dietaryTags:id,code,name',
        ];

        if ($requiresInMemoryAnalysis) {
            $relations = array_merge($relations, [
                'allergenOverrideRecords.allergen:id,public_id,name',
                'recipeIngredients.ingredient.nutritionFacts.nutrient',
                'recipeIngredients.ingredient.allergenDetails.allergen:id,public_id,name',
            ]);
        }

        $query = Recipe::query()
            ->translatedInto()
            ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status', 'completeness_score', 'completeness_status', 'editorial_reviewed_at', 'is_featured', 'created_at'])
            ->with($relations)
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            // Kalite kapısının kapattığı tarif site içi aramada da çıkmaz.
            // 20 Eylül 2026'da ölçüldü: "vegan" aramasının ilk sayfasındaki
            // 20 sonucun tamamı, malzemesi "sebze karışımı" olan şablon
            // kayıtlardı. Bu sayfaları aramaya değmez sayıp ziyaretçiye
            // sunmak kendi ölçümümüzle çelişiyordu.
            ->whereDoesntHave('seoMetadata', fn ($metadata) => $metadata->where('robots', 'like', '%noindex%'))
            ->when($filters['q'] ?? null, function ($query) use ($searchPlan): void {
                if (app()->getLocale() !== config('locales.default', 'tr')) {
                    $query->whereHas('translations', fn ($translations) => $translations
                        ->where('locale', app()->getLocale())->whereIn('field', ['title', 'excerpt'])
                        ->where('value', 'like', '%'.$searchPlan['original_query'].'%'));

                    return;
                }
                // Sorgu yapısal filtrelere çözülüyorsa ham metni başlıkta
                // aramak sonucu öldürüyordu: "kıbrıs mutfağı" doğru biçimde
                // cuisine=kıbrıs'a çözülüyor ama hiçbir tarifin başlığında o
                // ifade geçmediği için sıfır sonuç dönüyordu - oysa sitede
                // "Kıbrıs Mutfağı" adlı, 22 tarifli bir mutfak var. Aynı şey
                // "kolay tarifler" ve "20 dakikada" için de oluyordu; üçü
                // birlikte 64 aramaydı ve hepsi boşa gitti.
                //
                // Yapısal sinyal varsa o filtreler zaten daraltıyor; metin
                // şartı yalnız malzeme geçtiğinde ya da hiç sinyal yokken
                // uygulanıyor.
                $yapisal = ($searchPlan['cuisine'] ?? null) !== null
                    || ($searchPlan['difficulty'] ?? null) !== null
                    || ($searchPlan['max_minutes'] ?? null) !== null
                    || ($searchPlan['method'] ?? null) !== null
                    || ($searchPlan['meal_type'] ?? null) !== null;

                if ($yapisal && $searchPlan['included_ingredients'] === []) {
                    return;
                }

                $query->where(function ($nested) use ($searchPlan): void {
                    $original = (string) $searchPlan['original_query'];
                    if ($original !== '') {
                        $nested->where('title', 'like', "%{$original}%");
                    }
                    foreach ($searchPlan['included_ingredients'] as $term) {
                        $nested->orWhereHas('ingredients', fn ($ingredients) => $ingredients->where('name', 'like', "%{$term}%"));
                    }
                });
            })
            ->when($filters['cuisine'] ?? $searchPlan['cuisine'], fn ($query, string $slug) => $query->whereHas('cuisine', fn ($cuisines) => $cuisines->where('slug', $slug)->orWhere('name', 'like', "%{$slug}%")))
            ->when($filters['category'] ?? null, fn ($query, string $slug) => $query->whereHas('category', fn ($categories) => $categories->where('slug', $slug)))
            ->when($filters['ingredient'] ?? null, fn ($query, string $publicId) => $query->whereHas('ingredients', fn ($ingredients) => $ingredients->where('public_id', $publicId)))
            ->when(Recipe::zorlukKanonik($filters['difficulty'] ?? $searchPlan['difficulty'] ?? null), fn ($query, string $difficulty) => $query->where('difficulty', $difficulty))
            ->when($filters['max_minutes'] ?? $searchPlan['max_minutes'], fn ($query, int $minutes) => $query->whereRaw('(prep_minutes + cook_minutes + rest_minutes) <= ?', [$minutes]));

        foreach ($searchPlan['included_ingredients'] as $term) {
            $query->whereHas('ingredients', fn ($ingredients) => $ingredients->where('name', 'like', "%{$term}%"));
        }
        foreach ($searchPlan['excluded_ingredients'] as $term) {
            $query->whereDoesntHave('ingredients', fn ($ingredients) => $ingredients->where('name', 'like', "%{$term}%"));
        }

        // Gerçek (YZ olmayan) onaylı fotoğrafı olan, eksiksiz tarifler her
        // sıralamada önce gelir; sonra YZ görselli olanlar, en son görselsizler.
        $approvedMedia = "select 1 from media where media.mediable_type = ? and media.mediable_id = recipes.id and media.visibility = 'public' and coalesce(media.is_placeholder, 0) = 0 and media.verification_status = 'verified' and media.media_status = 'approved_real_media' and media.license_review_status = 'approved' and media.attribution_review_status = 'approved'";
        $query->orderByRaw("case when exists ({$approvedMedia} and coalesce(media.generated_by_ai, 0) = 0) then 0 when exists ({$approvedMedia}) then 1 else 2 end", [Recipe::class, Recipe::class])
            ->orderByRaw("case when completeness_status = 'publishable' then 0 else 1 end")
            ->orderByDesc('completeness_score');

        if (($filters['sort'] ?? null) === 'time') {
            $query->orderByRaw('(prep_minutes + cook_minutes + rest_minutes)');
        } else {
            $query->orderByDesc('is_featured')->latest();
        }

        if (! $requiresInMemoryAnalysis) {
            return $query->paginate($perPage, ['*'], 'page', $page)
                ->withPath(app(LocaleUrlService::class)->urlFor(app()->getLocale(), request()->url()))->withQueryString();
        }

        $recipes = $query->get()->filter(function (Recipe $recipe) use ($filters): bool {
            $nutrition = $this->nutrition->calculate($recipe);
            $protein = (string) data_get($nutrition, 'nutrients.protein.per_serving', '0');
            $calories = (string) data_get($nutrition, 'nutrients.energy_kcal.per_serving', '0');
            if (isset($filters['min_protein']) && bccomp($protein, (string) $filters['min_protein'], 4) < 0) {
                return false;
            }
            if (isset($filters['max_calories']) && (bccomp($calories, '0', 4) === 0 || bccomp($calories, (string) $filters['max_calories'], 4) > 0)) {
                return false;
            }
            if (($filters['verified_only'] ?? false) && ($nutrition['confidence_score'] < 80 || $nutrition['completeness_percentage'] < 90 || $nutrition['contains_estimated_values'])) {
                return false;
            }
            if ($tag = ($filters['dietary_tag'] ?? $filters['diet'] ?? null)) {
                $status = $this->dietary->assess($recipe)[$tag]['status'] ?? 'uncertain';
                if (! in_array($status, ['compatible', 'manually_verified'], true)) {
                    return false;
                }
            }
            // Contract: only confirmed `contains` excludes a recipe. `may_contain`
            // and `cross_contamination_risk` remain visible as safety information.
            $excludedAllergens = array_values(array_unique(array_filter($filters['exclude_allergens'] ?? [$filters['exclude_allergen'] ?? null])));
            if ($excludedAllergens !== []) {
                foreach ($this->allergens->analyse($recipe)['groups']['contains'] as $item) {
                    if (in_array($item['public_id'], $excludedAllergens, true)) {
                        return false;
                    }
                }
            }

            return true;
        })->values();

        return new Paginator($recipes->forPage($page, $perPage)->values(), $recipes->count(), $perPage, $page, ['path' => app(LocaleUrlService::class)->urlFor(app()->getLocale(), request()->url()), 'query' => request()->query()]);
    }
}
