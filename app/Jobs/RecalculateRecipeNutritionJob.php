<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecalculateRecipeNutritionJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 300;

    public array $backoff = [30, 120];

    public function __construct(public readonly int $recipeId) {}

    public function uniqueId(): string
    {
        return (string) $this->recipeId;
    }

    public function handle(NutritionService $nutrition): void
    {
        $recipe = Recipe::query()->findOrFail($this->recipeId);
        $nutrition->invalidate($recipe);
        $nutrition->snapshot($recipe);
    }
}
