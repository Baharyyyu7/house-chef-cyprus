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
        $recipes = Recipe::query()->with(['cuisine', 'category', 'media', 'recipeIngredients.ingredient.nutritionFacts.nutrient'])
            ->where('status', 'published')
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(fn ($nested) => $nested->where('title', 'like', "%{$term}%")->orWhereHas('ingredients', fn ($ingredients) => $ingredients->where('name', 'like', "%{$term}%"))))
            ->when($filters['cuisine'] ?? null, fn ($query, string $slug) => $query->whereHas('cuisine', fn ($cuisines) => $cuisines->where('slug', $slug)))
            ->when($filters['max_minutes'] ?? null, fn ($query, int $minutes) => $query->whereRaw('(prep_minutes + cook_minutes + rest_minutes) <= ?', [$minutes]))
            ->orderByDesc('is_featured')->latest()->get()->filter(function (Recipe $recipe) use ($filters): bool {
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
                if ($allergen = ($filters['exclude_allergen'] ?? null)) {
                    foreach ($this->allergens->analyse($recipe)['groups']['contains'] as $item) {
                        if ($item['public_id'] === $allergen) {
                            return false;
                        }
                    }
                }

                return true;
            })->values();
        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 12)));
        $page = max(1, (int) ($filters['page'] ?? 1));

        return new Paginator($recipes->forPage($page, $perPage)->values(), $recipes->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
    }
}
