<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Models\RecipeNutritionSnapshot;
use App\Services\Cache\CacheInvalidationService;
use App\Services\Cache\CacheKeyFactory;
use Illuminate\Support\Facades\Cache;

/** Computes transparent nutrition totals from ingredient facts without float arithmetic. */
final class NutritionService
{
    private const SCALE = 6;

    public function __construct(private readonly CacheKeyFactory $keys) {}

    /**
     * @return array<string, mixed>
     */
    public function calculate(Recipe $recipe, ?int $servings = null, bool $includeOptional = true): array
    {
        $servings ??= max(1, (int) $recipe->servings);
        $key = $this->keys->nutrition($recipe, $servings, $includeOptional);

        return Cache::remember($key, now()->addHour(), fn (): array => $this->calculateFresh($recipe, $servings, $includeOptional));
    }

    /** @return array<string, mixed> */
    public function snapshot(Recipe $recipe, ?int $servings = null): array
    {
        $servings ??= max(1, (int) $recipe->servings);
        $payload = $this->calculate($recipe, $servings);

        RecipeNutritionSnapshot::query()->updateOrCreate(
            ['recipe_id' => $recipe->id, 'servings' => $servings, 'source_version' => 'v1'],
            ['payload' => $payload, 'completeness_percentage' => $payload['completeness_percentage'], 'confidence_score' => $payload['confidence_score'], 'calculated_at' => now(), 'invalidated_at' => null],
        );

        return $payload;
    }

    public function invalidate(Recipe $recipe): void
    {
        $recipe->unsetRelation('recipeIngredients');
        app(CacheInvalidationService::class)->recipe($recipe);
        foreach (RecipeNutritionSnapshot::query()->where('recipe_id', $recipe->id)->get() as $snapshot) {
            Cache::forget("nutrition:recipe:{$recipe->public_id}:servings:{$snapshot->servings}:optional:1");
            Cache::forget("nutrition:recipe:{$recipe->public_id}:servings:{$snapshot->servings}:optional:0");
            $snapshot->update(['invalidated_at' => now()]);
        }
    }

    /** Legacy presentation helper retained for existing recipe screens. @return array<string, string> */
    public function perServing(Recipe $recipe, int $servings): array
    {
        $result = $this->calculate($recipe, $servings);

        if ($result['nutrients'] !== []) {
            return collect($result['nutrients'])->mapWithKeys(fn (array $item, string $code) => [$code => $item['per_serving']])->all();
        }

        $factor = bcdiv((string) max(1, $servings), (string) max(1, (int) $recipe->servings), self::SCALE);

        return collect($recipe->nutrition ?? [])->mapWithKeys(fn ($value, string $name) => [$name => is_numeric($value) ? bcmul((string) $value, $factor, 1) : (string) $value])->all();
    }

    /** @return array<string, mixed> */
    private function calculateFresh(Recipe $recipe, int $servings, bool $includeOptional): array
    {
        if (! $recipe->relationLoaded('recipeIngredients')) {
            $recipe->load('recipeIngredients.ingredient.nutritionFacts.nutrient');
        }
        $totals = [];
        $missing = [];
        $coveredWeight = '0';
        $totalWeight = '0';
        $confidenceSum = 0;
        $factCount = 0;
        $containsEstimated = false;
        $containsAi = false;

        foreach ($recipe->recipeIngredients as $line) {
            if (! $includeOptional && $line->is_optional) {
                continue;
            }
            $amount = $this->metricAmount($line->quantity, $line->unit, $line->grams);
            if ($amount === null) {
                $missing[] = ['public_id' => $line->ingredient->public_id, 'name' => $line->ingredient->name, 'reason' => 'Kesin ölçü birimi yok'];

                continue;
            }
            [$amountValue, $basis] = $amount;
            $totalWeight = bcadd($totalWeight, $amountValue, self::SCALE);
            $facts = $line->ingredient->nutritionFacts->filter(fn (NutritionFact $fact): bool => $fact->basis_type === $basis && bccomp((string) $fact->basis_amount, '0', self::SCALE) === 1);
            if ($facts->isEmpty()) {
                $missing[] = ['public_id' => $line->ingredient->public_id, 'name' => $line->ingredient->name, 'reason' => 'Besin verisi yok'];

                continue;
            }
            $coveredWeight = bcadd($coveredWeight, $amountValue, self::SCALE);
            foreach ($facts as $fact) {
                $code = $fact->nutrient->code;
                $calculated = bcdiv(bcmul((string) $fact->value, $amountValue, self::SCALE), (string) $fact->basis_amount, self::SCALE);
                $totals[$code] ??= ['name' => $fact->nutrient->name, 'unit' => $fact->nutrient->unit, 'amount' => '0', 'origins' => []];
                $totals[$code]['amount'] = bcadd($totals[$code]['amount'], $calculated, self::SCALE);
                $totals[$code]['origins'][] = $fact->origin;
                $confidenceSum += $fact->confidence_score;
                $factCount++;
                $containsEstimated = $containsEstimated || $fact->origin !== 'verified';
                $containsAi = $containsAi || $fact->origin === 'ai_estimated';
            }
        }
        foreach ($totals as &$total) {
            $total['per_serving'] = bcdiv($total['amount'], (string) $servings, self::SCALE);
            $total['origins'] = array_values(array_unique($total['origins']));
        }
        unset($total);
        $completeness = bccomp($totalWeight, '0', self::SCALE) === 1 ? (int) bcdiv(bcmul($coveredWeight, '100', 0), $totalWeight, 0) : 0;
        $averageConfidence = $factCount > 0 ? intdiv($confidenceSum, $factCount) : 0;
        $confidence = intdiv($averageConfidence * $completeness, 100);

        return ['nutrients' => $totals, 'basis' => ['type' => 'recipe', 'servings' => $servings, 'include_optional' => $includeOptional], 'servings' => $servings, 'completeness_percentage' => $completeness, 'confidence_score' => $confidence, 'calculation_method' => 'ingredient_facts_exact_metric_v1', 'contains_estimated_values' => $containsEstimated, 'contains_ai_values' => $containsAi, 'missing_ingredients' => $missing, 'calculated_at' => now()->toIso8601String()];
    }

    /** @return array{0:string,1:string}|null */
    private function metricAmount(mixed $quantity, ?string $unit, mixed $grams): ?array
    {
        if ($grams !== null && bccomp((string) $grams, '0', self::SCALE) >= 0) {
            return [(string) $grams, 'per_100g'];
        }
        $quantity = (string) ($quantity ?? '0');

        return match ($unit) {
            'g' => [$quantity, 'per_100g'],
            'kg' => [bcmul($quantity, '1000', self::SCALE), 'per_100g'],
            'ml' => [$quantity, 'per_100ml'],
            'l' => [bcmul($quantity, '1000', self::SCALE), 'per_100ml'],
            default => null,
        };
    }
}
