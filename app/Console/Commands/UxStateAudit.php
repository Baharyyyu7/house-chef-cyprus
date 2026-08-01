<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxStateAudit extends Command
{
    protected $signature = 'ux:state-audit {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductExperienceAuditService $audit): int
    {
        $report = $audit->audit();
        $payload = ['rule_version' => $report['rule_version'], 'generated_at' => $report['generated_at'], 'status' => $report['status'], 'empty_state_present' => ! $report['findings']['missing_empty_state'], 'error_state_present' => ! $report['findings']['missing_error_state'], 'manual_qa_required' => ['loading', 'network_error', 'provider_unavailable', '403_404_419_500']];
        $this->line($this->option('json') ? (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Durum ön kontrolü: '.$payload['status']);

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
