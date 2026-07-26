<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class QueueHealthService
{
    /** @return array<string, int|string|null> */
    public function snapshot(): array
    {
        $threshold = now()->subSeconds((int) config('queue.health.stuck_after_seconds', 300))->getTimestamp();
        $connection = (string) config('queue.default');
        $pending = DB::table('jobs')->whereNull('reserved_at')->count();
        $reserved = DB::table('jobs')->whereNotNull('reserved_at')->count();
        $stuck = DB::table('jobs')->where('reserved_at', '>', 0)->where('reserved_at', '<', $threshold)->count();
        $heartbeat = Cache::get('queue.worker_heartbeat');
        $workerStatus = $heartbeat && now()->diffInSeconds(Carbon::parse($heartbeat)) <= (int) config('queue.health.heartbeat_stale_after_seconds', 120) ? 'healthy' : 'unavailable';

        return [
            'connection' => $connection,
            'pending_jobs' => $pending,
            'reserved_jobs' => $reserved,
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'stuck_jobs' => $stuck,
            'worker_status' => $workerStatus,
            'worker_heartbeat' => $heartbeat,
            'last_successful_job_at' => Cache::get('queue.last_successful_job_at'),
            'last_failed_job_at' => Cache::get('queue.last_failed_job_at'),
            'repeated_failures' => DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count(),
            'overall_status' => $stuck > 0 || ($workerStatus === 'unavailable' && $reserved > 0) ? 'degraded' : 'healthy',
        ];
    }

    public function heartbeat(): void
    {
        Cache::forever('queue.worker_heartbeat', now()->toIso8601String());
    }

    public function completed(): void
    {
        Cache::forever('queue.last_successful_job_at', now()->toIso8601String());
        $this->heartbeat();
    }
}
