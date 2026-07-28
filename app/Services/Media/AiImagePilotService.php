<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AiImageGeneration;
use App\Models\Recipe;
use Illuminate\Support\Collection;

final class AiImagePilotService
{
    /** @var list<string> */
    private const PILOT_SLUGS = [
        'kibris-usulu-firin-tavuk', 'domates-corbasi', 'limonlu-akdeniz-makarnasi', 'sebzeli-teriyaki-tavuk',
        'firin-sebzeli-taco-kasesi', 'nohutlu-akdeniz-kasesi', 'peynirli-borek', 'baklava-dilimi',
        'zeytinyagli-nohutlu-pilav', 'cacik',
    ];

    public function __construct(private readonly RecipeImagePromptBuilder $prompts) {}

    /** @return Collection<int, Recipe> */
    public function recipes()
    {
        return Recipe::query()->with(['cuisine', 'category', 'ingredients', 'media', 'aiImageGenerations'])
            ->whereIn('slug', self::PILOT_SLUGS)->get()
            ->sortBy(fn (Recipe $recipe): int => array_search($recipe->slug, self::PILOT_SLUGS, true))->values();
    }

    /** @return list<AiImageGeneration> */
    public function synchronize(string $provider = 'gemini'): array
    {
        $recipes = $this->recipes();
        AiImageGeneration::query()->where('provider', $provider)->whereIn('status', ['planned', 'blocked_recipe_fidelity'])->whereNotIn('recipe_id', $recipes->pluck('id'))->delete();

        $variations = min((int) config('ai_images.max_variations_per_recipe'), 2);

        return $recipes->flatMap(function (Recipe $recipe) use ($provider, $variations): array {
            return collect(range(1, $variations))->map(function (int $number) use ($recipe, $provider): AiImageGeneration {
                $prompt = $this->prompts->build($recipe, $this->variationFor($recipe, $number));

                $attributes = [
                    'provider' => $provider,
                    'model' => config("ai_images.providers.{$provider}.model"),
                    'prompt' => $prompt['prompt'],
                    'negative_prompt' => $prompt['negative_prompt'],
                    'prompt_version' => $prompt['prompt_version'],
                    'aspect_ratio' => '4:3',
                    'status' => $prompt['ready'] ? 'planned' : 'blocked_recipe_fidelity',
                    'estimated_cost_cents' => (int) config('ai_images.pilot_cost_estimate_cents_per_image'),
                    'error_code' => $prompt['ready'] ? null : 'RECIPE_FIDELITY_REQUIRED',
                    'error_message' => $prompt['ready'] ? null : implode(' ', $prompt['blocking_reasons']),
                ];
                $generation = AiImageGeneration::query()->firstOrNew(['recipe_id' => $recipe->id, 'variation_number' => $number]);
                if (! $generation->exists || in_array($generation->status, ['planned', 'blocked_recipe_fidelity'], true)) {
                    $generation->fill($attributes)->save();
                }

                return $generation;
            })->all();
        })->all();
    }

    /** @return array{recipe_count:int,variation_count:int,estimated_cost_cents:?int,budget_cents:int,within_budget:bool,cost_estimate_configured:bool} */
    public function costPreview(): array
    {
        $recipeCount = $this->recipes()->count();
        $variationCount = min($recipeCount * (int) config('ai_images.max_variations_per_recipe'), (int) config('ai_images.max_total_variations'));
        $perImage = (int) config('ai_images.pilot_cost_estimate_cents_per_image');
        $cost = $perImage > 0 ? $variationCount * $perImage : null;
        $budget = (int) config('ai_images.daily_budget_cents');

        return ['recipe_count' => $recipeCount, 'variation_count' => $variationCount, 'estimated_cost_cents' => $cost, 'budget_cents' => $budget, 'within_budget' => $cost !== null && $budget > 0 && $cost <= $budget, 'cost_estimate_configured' => $cost !== null];
    }

    public function generationAllowed(): bool
    {
        $provider = (array) config('ai_images.providers.gemini', []);
        $cost = $this->costPreview();

        return (bool) ($provider['enabled'] ?? false)
            && filled($provider['key'] ?? null)
            && $cost['within_budget']
            && $this->consumedVariationCount() < (int) config('ai_images.max_total_variations')
            && $this->recipes()->every(fn (Recipe $recipe): bool => $this->prompts->build($recipe)['ready']);
    }

    public function consumedVariationCount(): int
    {
        return AiImageGeneration::query()
            ->where('provider', 'gemini')
            ->whereIn('status', ['requesting', 'provider_failed', 'generated', 'approved', 'rejected'])
            ->count();
    }

    private function variationFor(Recipe $recipe, int $number): string
    {
        if ($number === 2) {
            return $recipe->category?->slug === 'tatli' ? 'close_up_texture' : 'modern_overhead';
        }

        return match ($recipe->category?->slug) {
            'corba' => 'close_up_texture',
            'tatli' => 'dark_premium_studio',
            'hamur-isi' => 'rustic_mediterranean',
            default => $recipe->cuisine?->slug === 'kibris-mutfagi' ? 'traditional_cyprus' : 'bright_ivory_editorial',
        };
    }
}
