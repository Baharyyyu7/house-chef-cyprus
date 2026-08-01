<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxHealth extends Command
{
    protected $signature = 'ux:health {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductExperienceAuditService $audit): int
    {
        $report = $audit->latest();
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'UX sağlık durumu: '.$report['status']);

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
