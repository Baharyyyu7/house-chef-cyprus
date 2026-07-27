<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NutritionRecalculationRun;
use App\Models\Recipe;
use App\Services\NutritionService;
use App\Services\QueueHealthService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecalculateRecipeNutritionJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 300;

    public array $backoff = [30, 120];

    public function __construct(public readonly string $recipePublicId, public readonly ?int $runId = null)
    {
        $this->onQueue('nutrition');
    }

    public function uniqueId(): string
    {
        return $this->recipePublicId;
    }

    public function handle(NutritionService $nutrition): void
    {
        $recipe = Recipe::query()->where('public_id', $this->recipePublicId)->firstOrFail();
        $run = $this->runId ? NutritionRecalculationRun::query()->find($this->runId) : null;
        $previous = $recipe->nutritionSnapshots()->whereNull('invalidated_at')->latest('calculated_at')->first();
        $run?->update(['status' => 'running', 'started_at' => now(), 'previous_confidence' => $previous?->confidence_score, 'previous_completeness' => $previous?->completeness_percentage]);
        try {
            $nutrition->invalidate($recipe);
            $result = $nutrition->snapshot($recipe);
            $run?->update(['status' => 'completed', 'finished_at' => now(), 'new_confidence' => $result['confidence_score'], 'new_completeness' => $result['completeness_percentage']]);
            app(QueueHealthService::class)->completed();
        } catch (\Throwable $exception) {
            $run?->update(['status' => 'failed', 'finished_at' => now(), 'error_code' => 'NUTRITION_RECALCULATION_FAILED', 'error_message' => 'Besin hesaplama işlemi tamamlanamadı.']);
            cache()->forever('queue.last_failed_job_at', now()->toIso8601String());
            throw $exception;
        }
    }
}
