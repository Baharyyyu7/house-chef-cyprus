<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\QueueHealthService;
use Illuminate\Console\Command;

final class QueueHealth extends Command
{
    protected $signature = 'queue:health';

    protected $description = 'Database queue sağlık özetini gösterir';

    public function handle(QueueHealthService $health): int
    {
        $health->heartbeat();
        $this->table(['Bağlantı', 'Bekleyen', 'Başarısız', 'Takılmış'], [[...array_values(array_intersect_key($health->snapshot(), array_flip(['connection', 'pending_jobs', 'failed_jobs', 'stuck_jobs'])))]]);

        return self::SUCCESS;
    }
}
