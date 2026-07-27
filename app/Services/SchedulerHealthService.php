<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

final class SchedulerHealthService
{
    /** @param callable():void $task */
    public function run(string $name, callable $task): void
    {
        $started = now();
        Cache::forever('scheduler.health', ['last_started_at' => $started->toIso8601String(), 'last_task_name' => $name, 'status' => 'healthy']);
        try {
            $task();
            Cache::forever('scheduler.health', ['last_started_at' => $started->toIso8601String(), 'last_completed_at' => now()->toIso8601String(), 'last_task_name' => $name, 'last_duration_ms' => $started->diffInMilliseconds(now()), 'status' => 'healthy']);
        } catch (\Throwable $exception) {
            report($exception);
            Cache::forever('scheduler.health', ['last_started_at' => $started->toIso8601String(), 'last_failed_at' => now()->toIso8601String(), 'last_task_name' => $name, 'last_error_code' => 'SCHEDULER_TASK_FAILED', 'status' => 'failed']);

            throw $exception;
        }
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return Cache::get('scheduler.health', ['status' => 'never_run', 'task_count' => 2]) + ['task_count' => 2];
    }
}
