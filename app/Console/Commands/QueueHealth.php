<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\QueueHealthService;
use Illuminate\Console\Command;

final class QueueHealth extends Command
{
    protected $signature = 'queue:health {--json : Makine okunabilir çıktı}';

    protected $description = 'Database queue sağlık özetini gösterir';

    public function handle(QueueHealthService $health): int
    {
        $health->heartbeat();
        $snapshot = $health->snapshot();
        if ($this->option('json')) {
            $this->line(json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->table(['Bağlantı', 'Bekleyen', 'Reserved', 'Başarısız', 'Takılmış', 'Durum'], [[...array_values(array_intersect_key($snapshot, array_flip(['connection', 'pending_jobs', 'reserved_jobs', 'failed_jobs', 'stuck_jobs', 'overall_status'])))]]);

        return self::SUCCESS;
    }
}
