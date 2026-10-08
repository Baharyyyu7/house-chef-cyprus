<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class HealthService
{
    /** @return array{status:string,checked_at:string,components:array<int,array<string,mixed>>} */
    public function snapshot(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget('health.snapshot.v1');
        }

        return Cache::remember('health.snapshot.v1', now()->addSeconds((int) config('observability.health_cache_seconds')), function (): array {
            $components = [];
            $components[] = $this->check('application', fn () => true, 'Uygulama çalışıyor.');
            $components[] = $this->check('database', fn () => DB::select('select 1') !== [], 'Veritabanı erişilebilir.');
            $components[] = $this->check('cache', fn () => Cache::put('health.probe', 'ok', 10) && Cache::get('health.probe') === 'ok', 'Cache erişilebilir.');
            $components[] = $this->check('storage_public', fn () => is_writable(storage_path('app/public')), 'Public depolama yazılabilir.');
            $components[] = $this->check('storage_private', fn () => is_writable(storage_path('app/private')), 'Private depolama yazılabilir.');
            $components[] = $this->check('disk', fn () => disk_free_space(storage_path()) >= (int) config('observability.critical_disk_free_bytes'), 'Disk alanı yeterli.', fn (): array => ['free_bytes' => (int) disk_free_space(storage_path())]);
            $components[] = ['name' => 'queue', 'status' => app(QueueHealthService::class)->snapshot()['overall_status'] === 'healthy' ? 'healthy' : 'degraded', 'checked_at' => now()->toIso8601String(), 'duration_ms' => 0, 'safe_message' => 'Queue durumu kontrol edildi.', 'metadata' => []];
            $components[] = $this->scheduler();
            $components[] = ['name' => 'media_processing', 'status' => Media::query()->where('processing_status', 'failed')->exists() ? 'degraded' : 'healthy', 'checked_at' => now()->toIso8601String(), 'duration_ms' => 0, 'safe_message' => 'Medya işleme durumu kontrol edildi.', 'metadata' => ['failed_count' => Media::query()->where('processing_status', 'failed')->count()]];
            $overall = collect($components)->contains('status', 'unhealthy') ? 'unhealthy' : (collect($components)->contains('status', 'degraded') ? 'degraded' : 'healthy');

            return ['status' => $overall, 'checked_at' => now()->toIso8601String(), 'components' => $components];
        });
    }

    /**
     * Zamanlayıcı bileşeni yalnız "son dürtme ne zaman" sorusuna bakarsa
     * dakikalık görevler koşarken gece yedeği haftalarca düşebilir. Kritik
     * görevlerden biri beklenen sıklığının iki katından uzun süredir başarılı
     * koşmadıysa bileşen degraded olur.
     *
     * @return array<string, mixed>
     */
    private function scheduler(): array
    {
        $scheduler = app(SchedulerHealthService::class);
        $snapshot = $scheduler->snapshot();
        $bayat = $snapshot['stale_critical_tasks'];
        $saglikli = in_array($snapshot['status'], ['healthy', 'failed'], true) && $bayat === [];

        return [
            'name' => 'scheduler',
            'status' => $saglikli ? 'healthy' : 'degraded',
            'checked_at' => now()->toIso8601String(),
            'duration_ms' => 0,
            'safe_message' => $bayat === [] ? 'Scheduler durumu kontrol edildi.' : 'Kritik zamanlanmış görev bayat ya da başarısız.',
            'metadata' => ['stale_critical_tasks' => $bayat, 'failed_tasks' => $snapshot['failed_tasks'], 'task_count' => $snapshot['task_count']],
        ];
    }

    /** @param callable():bool $probe @param callable():array<string,mixed>|null $metadata */
    private function check(string $name, callable $probe, string $message, ?callable $metadata = null): array
    {
        $started = hrtime(true);
        try {
            $ok = $probe();

            return ['name' => $name, 'status' => $ok ? 'healthy' : 'unhealthy', 'checked_at' => now()->toIso8601String(), 'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000), 'safe_message' => $ok ? $message : 'Güvenli sağlık kontrolü başarısız.', 'metadata' => $metadata ? $metadata() : []];
        } catch (\Throwable) {
            return ['name' => $name, 'status' => 'unhealthy', 'checked_at' => now()->toIso8601String(), 'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000), 'safe_message' => 'Güvenli sağlık kontrolü başarısız.', 'metadata' => []];
        }
    }
}
