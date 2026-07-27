<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\DietaryCompatibilityService;
use App\Services\NutritionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

final class DatabaseRecipeSearch implements RecipeSearchInterface
{
    public function __construct(private readonly NutritionService $nutrition, private readonly DietaryCompatibilityService $dietary, private readonly AllergenAnalysisService $allergens) {}

    public function search(array $filters): LengthAwarePaginator
    {
        $query = Recipe::query()
            ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'is_featured', 'created_at'])
            ->with([
                'cuisine:id,public_id,name,slug',
                'category:id,public_id,name,slug',
                'dietaryTags:id,code',
                'allergenOverrideRecords.allergen:id,public_id,name',
                'recipeIngredients.ingredient.nutritionFacts.nutrient',
                'recipeIngredients.ingredient.allergenDetails.allergen:id,public_id,name',
            ])
            ->where('status', 'published')
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(fn ($nested) => $nested->where('title', 'like', "%{$term}%")->orWhereHas('ingredients', fn ($ingredients) => $ingredients->where('name', 'like', "%{$term}%"))))
            ->when($filters['cuisine'] ?? null, fn ($query, string $slug) => $query->whereHas('cuisine', fn ($cuisines) => $cuisines->where('slug', $slug)))
            ->when($filters['category'] ?? null, fn ($query, string $slug) => $query->whereHas('category', fn ($categories) => $categories->where('slug', $slug)))
            ->when($filters['ingredient'] ?? null, fn ($query, string $publicId) => $query->whereHas('ingredients', fn ($ingredients) => $ingredients->where('public_id', $publicId)))
            ->when($filters['difficulty'] ?? null, fn ($query, string $difficulty) => $query->where('difficulty', $difficulty))
            ->when($filters['max_minutes'] ?? null, fn ($query, int $minutes) => $query->whereRaw('(prep_minutes + cook_minutes + rest_minutes) <= ?', [$minutes]));

        if (($filters['sort'] ?? null) === 'time') {
            $query->orderByRaw('(prep_minutes + cook_minutes + rest_minutes)');
        } else {
            $query->orderByDesc('is_featured')->latest();
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
        $perPage = min((int) config('performance.pagination.api_max'), max(1, (int) ($filters['per_page'] ?? config('performance.pagination.public'))));
        $page = max(1, (int) ($filters['page'] ?? 1));

        return new Paginator($recipes->forPage($page, $perPage)->values(), $recipes->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
    }
}
