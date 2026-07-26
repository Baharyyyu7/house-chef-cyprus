<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class QueueHealthService
{
    /** @return array<string, int|string|null> */
    public function snapshot(): array
    {
        $threshold = now()->subSeconds((int) config('queue.health.stuck_after_seconds', 300))->getTimestamp();
        $connection = (string) config('queue.default');
        $pending = DB::table('jobs')->count();
        $stuck = DB::table('jobs')->where('reserved_at', '>', 0)->where('reserved_at', '<', $threshold)->count();

        return [
            'connection' => $connection,
            'pending_jobs' => $pending,
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'stuck_jobs' => $stuck,
            'worker_heartbeat' => Cache::get('queue.worker_heartbeat'),
            'last_successful_job_at' => Cache::get('queue.last_successful_job_at'),
            'last_failed_job_at' => Cache::get('queue.last_failed_job_at'),
        ];
    }

    public function heartbeat(): void
    {
        Cache::forever('queue.worker_heartbeat', now()->toIso8601String());
    }
}
