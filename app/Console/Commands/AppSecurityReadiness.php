<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionOperationsService;
use Illuminate\Console\Command;

final class AppSecurityReadiness extends Command
{
    protected $signature = 'app:security-readiness {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductionOperationsService $operations): int
    {
        $report = $operations->securityReadiness();
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $report['safe_message']);

        return self::SUCCESS;
    }
}
