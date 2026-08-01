<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxAccessibilityAudit extends Command
{
    protected $signature = 'ux:accessibility-audit {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductExperienceAuditService $audit): int
    {
        $report = $audit->audit();
        $payload = ['rule_version' => $report['rule_version'], 'generated_at' => $report['generated_at'], 'status' => $report['status'], 'findings' => $report['findings'], 'manual_qa_required' => $report['manual_qa_required']];
        $this->line($this->option('json') ? (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Erişilebilirlik ön kontrolü: '.$payload['status']);

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
