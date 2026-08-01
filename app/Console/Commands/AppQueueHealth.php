<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionOperationsService;
use Illuminate\Console\Command;

final class AppQueueHealth extends Command
{
    protected $signature = 'app:queue-health {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductionOperationsService $operations): int
    {
        $report = $operations->queueHealth();
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Queue: '.$report['overall_status']);

        return self::SUCCESS;
    }
}
