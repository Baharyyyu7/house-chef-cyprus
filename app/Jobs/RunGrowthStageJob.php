<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\IntegrityReport;
use App\Services\Seo\GrowthSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

final class RunGrowthStageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [60, 300];

    public function __construct(public readonly int $reportId)
    {
        $this->onConnection('database')->onQueue('default')->afterCommit();
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('growth-stage:'.$this->reportId))->releaseAfter(65)->expireAfter(75)];
    }

    public function handle(GrowthSystem $system): void
    {
        $report = IntegrityReport::query()->findOrFail($this->reportId);
        if (in_array($report->status, ['success', 'attention'], true)) {
            return;
        }
        $payload = $report->payload;
        $report->update(['status' => 'running', 'payload' => [...$payload, 'started_at' => now()->toIso8601String()]]);
        try {
            $result = $system->execute($payload['stage']);
            $status = in_array($result['status'] ?? 'success', ['success', 'unchanged'], true) ? 'success' : 'attention';
            $report->update(['status' => $status, 'payload' => [...$payload, 'finished_at' => now()->toIso8601String(), 'result' => $result]]);
        } catch (Throwable $exception) {
            $report->update(['status' => 'retrying', 'payload' => [...$payload, 'error_code' => 'GROWTH_STAGE_FAILED']]);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        IntegrityReport::query()->whereKey($this->reportId)->update(['status' => 'failed']);
    }
}
