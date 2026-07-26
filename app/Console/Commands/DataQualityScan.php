<?php

namespace App\Console\Commands;

use App\Services\DataQualityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class DataQualityScan extends Command
{
    protected $signature = 'data:quality-scan';

    protected $description = 'Veri kalite sorunlarını kaydeder';

    public function handle(DataQualityService $quality): int
    {
        $startedAt = now();
        try {
            $result = $quality->scan();
            Cache::forever('data_quality.last_scan', ['status' => 'success', 'started_at' => $startedAt->toIso8601String(), 'finished_at' => now()->toIso8601String(), 'duration_ms' => $startedAt->diffInMilliseconds(now()), 'result' => $result]);
            $this->info("Skor {$result['score']} | yeni {$result['new']} | güncellenen {$result['updated']} | çözülen {$result['resolved']}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            Cache::forever('data_quality.last_scan', ['status' => 'failed', 'started_at' => $startedAt->toIso8601String(), 'finished_at' => now()->toIso8601String(), 'error_code' => 'DATA_QUALITY_SCAN_FAILED']);
            $this->error('Veri kalite taraması güvenli şekilde başarısız oldu.');

            return self::FAILURE;
        }
    }
}
