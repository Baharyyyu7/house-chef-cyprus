<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionOperationsService;
use Illuminate\Console\Command;

final class AppMailHealth extends Command
{
    protected $signature = 'app:mail-health {--no-send : Canlı e-posta göndermez} {--send-test= : Açık onayla verilen adrese test gönderir} {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductionOperationsService $operations): int
    {
        if (filled($this->option('send-test'))) {
            $this->error('Canlı mail testi bu otomasyonda kapalıdır; onaylı operasyon runbook’unu kullanın.');

            return self::FAILURE;
        }
        $report = $operations->mailHealth();
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $report['safe_message']);

        return self::SUCCESS;
    }
}
