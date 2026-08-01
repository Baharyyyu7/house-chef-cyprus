<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionOperationsService;
use Illuminate\Console\Command;

final class DatabaseReadiness extends Command
{
    protected $signature = 'app:database-readiness {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Production veritabanı hazırlığını güvenli biçimde raporlar.';

    public function handle(ProductionOperationsService $operations): int
    {
        $report = $operations->databaseReadiness();
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $report['safe_message']);

        return $report['status'] === 'unhealthy' ? self::FAILURE : self::SUCCESS;
    }
}
