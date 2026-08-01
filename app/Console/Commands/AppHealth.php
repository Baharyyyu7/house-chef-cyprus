<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionOperationsService;
use Illuminate\Console\Command;

final class AppHealth extends Command
{
    protected $signature = 'app:health {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductionOperationsService $operations): int
    {
        $report = $operations->health();
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Sağlık durumu: '.$report['status']);

        return self::SUCCESS;
    }
}
