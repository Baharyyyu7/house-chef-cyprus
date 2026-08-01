<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxPerformanceAudit extends Command
{
    protected $signature = 'ux:performance-audit {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductExperienceAuditService $audit): int
    {
        $report = $audit->audit();
        $payload = ['rule_version' => $report['rule_version'], 'generated_at' => $report['generated_at'], 'status' => $report['status'], 'measurement_note' => 'LCP, CLS, INP ve TTFB gerçek tarayıcı/RUM ölçümü gerektirir; bu komut bunları uydurmaz.', 'manual_qa_required' => ['lighthouse_or_rum', 'real_network', 'production_ttfb']];
        $this->line($this->option('json') ? (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Performans ön kontrolü: '.$payload['status']);

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
