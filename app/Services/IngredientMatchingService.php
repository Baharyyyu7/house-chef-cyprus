<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientSubstitution;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Collection;

final class IngredientMatchingService
{
    /** @return array<string, mixed> */
    public function match(Recipe $recipe, User $user, array $excludedIngredientIds = []): array
    {
        $available = $user->pantryItems()->where('is_available', true)->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))->with('ingredient')->get();

        return $this->evaluate($recipe, $available->pluck('ingredient_id')->all(), $excludedIngredientIds, $available);
    }

    /** @param list<string> $ingredientPublicIds @return Collection<int, array<string, mixed>> */
    public function findRecipes(array $ingredientPublicIds, array $excludedPublicIds = [], bool $onlyExact = false, bool $includeSubstitutions = true, int $maxMissing = 99): Collection
    {
        $selected = Ingredient::query()->whereIn('public_id', array_values(array_unique($ingredientPublicIds)))->pluck('id')->all();
        $excluded = Ingredient::query()->whereIn('public_id', array_values(array_unique($excludedPublicIds)))->pluck('id')->all();

        return Recipe::query()->where('status', 'published')->with(['ingredients', 'recipeIngredients.ingredient'])->get()
            ->reject(fn (Recipe $recipe) => $recipe->recipeIngredients->contains(fn ($row) => in_array($row->ingredient_id, $excluded, true)))
            ->map(fn (Recipe $recipe) => $this->evaluate($recipe, $selected, [], collect()))
            ->filter(fn (array $match) => (! $onlyExact || $match['can_cook_exactly']) && ($includeSubstitutions || ! $match['can_cook_with_substitutions']) && $match['missing_required_count'] <= $maxMissing)
            ->sortBy([['rank', 'desc'], ['missing_required_count', 'asc']])->values();
    }

    /** @param list<int> $availableIds @param list<int> $excludedIds @param Collection<int, mixed> $pantryItems @return array<string, mixed> */
    private function evaluate(Recipe $recipe, array $availableIds, array $excludedIds, Collection $pantryItems): array
    {
        $rows = $recipe->relationLoaded('recipeIngredients')
            ? $recipe->recipeIngredients->sortBy('sort_order')->values()
            : $recipe->recipeIngredients()->with('ingredient')->orderBy('sort_order')->get();
        $required = $rows->filter(fn ($row) => ! $row->is_optional && $row->is_required);
        $optional = $rows->filter(fn ($row) => $row->is_optional || ! $row->is_required);
        $missing = collect();
        $substitutions = collect();
        $quantityResults = collect();
        $matched = 0;

        foreach ($required as $row) {
            if (in_array($row->ingredient_id, $availableIds, true) && ! in_array($row->ingredient_id, $excludedIds, true)) {
                $pantry = $pantryItems->firstWhere('ingredient_id', $row->ingredient_id);
                $quantity = $this->quantityStatus($row, $pantry);
                $quantityResults->push(['ingredient_public_id' => $row->ingredient->public_id, 'name' => $row->ingredient->name, ...$quantity]);
                if ($quantity['status'] !== 'insufficient') {
                    $matched++;

                    continue;
                }
            }
            $candidate = $row->can_be_substituted ? IngredientSubstitution::query()->with('substitute')->where('ingredient_id', $row->ingredient_id)->whereIn('substitute_ingredient_id', $availableIds)->where('is_active', true)->orderByDesc('confidence_score')->first() : null;
            if ($candidate !== null) {
                $substitutions->push(['ingredient' => $row->ingredient->name, 'substitute' => $candidate->substitute->name, 'verified' => $candidate->is_verified, 'confidence_score' => (float) $candidate->confidence_score, 'quantity_status' => $candidate->ratio ? 'probably_sufficient' : 'quantity_unknown']);
            } else {
                $missing->push(['public_id' => $row->ingredient->public_id, 'name' => $row->ingredient->name]);
            }
        }

        $requiredCount = $required->count();
        $exact = $requiredCount === 0 ? 0 : (int) round(($matched / $requiredCount) * 100);
        $effective = $requiredCount === 0 ? 0 : (int) round((($matched + $substitutions->count() * 0.7) / $requiredCount) * 100);
        $missingCount = $missing->count();
        $exactCook = $missingCount === 0 && $substitutions->isEmpty();
        $withSubs = $missingCount === 0 && $substitutions->isNotEmpty();
        $status = $exactCook ? 'Tam yapılabilir' : ($withSubs ? 'Alternatifle yapılabilir' : ($missingCount <= 2 ? "{$missingCount} malzeme eksik" : 'Daha fazla eksik'));

        return ['recipe' => $recipe, 'recipe_public_id' => $recipe->public_id, 'title' => $recipe->title, 'required_count' => $requiredCount, 'matched_required_count' => $matched, 'missing_required_count' => $missingCount, 'matched_optional_count' => $optional->whereIn('ingredient_id', $availableIds)->count(), 'missing_required_ingredients' => $missing->values()->all(), 'available_substitutions' => $substitutions->values()->all(), 'quantity_results' => $quantityResults->values()->all(), 'exact_match_percentage' => $exact, 'effective_match_percentage' => $effective, 'can_cook_exactly' => $exactCook, 'can_cook_with_substitutions' => $withSubs, 'cannot_cook' => ! $exactCook && ! $withSubs, 'status' => $status, 'required' => $requiredCount, 'available' => $matched, 'missing' => $missingCount, 'rank' => ($exactCook ? 10000 : 0) + ($withSubs ? 5000 : 0) + $effective * 10 - $missingCount * 100 - $recipe->totalMinutes()];
    }

    /** @return array{status:string,required_quantity:?string,available_quantity:?string,unit:?string} */
    private function quantityStatus($recipeLine, $pantry): array
    {
        if ($pantry === null) {
            return ['status' => 'insufficient', 'required_quantity' => null, 'available_quantity' => null, 'unit' => null];
        }
        $required = $recipeLine->canonical_quantity ?? $recipeLine->grams;
        $unit = $recipeLine->canonical_unit ?? ($recipeLine->grams !== null ? 'g' : null);
        $available = $pantry->canonical_quantity;
        $availableUnit = $pantry->canonical_unit;
        if ($available === null && in_array($pantry->unit, ['g', 'kg', 'ml', 'l'], true) && $pantry->quantity !== null) {
            $available = match ($pantry->unit) {
                'kg' => bcmul((string) $pantry->quantity, '1000', 4), 'l' => bcmul((string) $pantry->quantity, '1000', 4), default => (string) $pantry->quantity
            };
            $availableUnit = in_array($pantry->unit, ['g', 'kg'], true) ? 'g' : 'ml';
        }
        if ($required === null || $available === null || $unit === null || $availableUnit !== $unit) {
            return ['status' => 'quantity_unknown', 'required_quantity' => $required, 'available_quantity' => $available, 'unit' => $unit];
        }
        $status = bccomp((string) $available, (string) $required, 4) >= 0 ? ($pantry->conversion_confidence === 'medium' ? 'probably_sufficient' : 'sufficient') : 'insufficient';

        return ['status' => $status, 'required_quantity' => (string) $required, 'available_quantity' => (string) $available, 'unit' => $unit];
    }
}
