<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxResponsiveAudit extends Command
{
    protected $signature = 'ux:responsive-audit {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductExperienceAuditService $audit): int
    {
        $report = $audit->audit();
        $payload = ['rule_version' => $report['rule_version'], 'generated_at' => $report['generated_at'], 'status' => $report['status'], 'scores' => $report['scores'], 'manual_qa_required' => ['320', '375', '430', '768', '1024', '1440', '1920']];
        $this->line($this->option('json') ? (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Responsive ön kontrol: '.$payload['status']);

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
