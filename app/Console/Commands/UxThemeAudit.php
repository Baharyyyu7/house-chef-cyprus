<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxThemeAudit extends Command
{
    protected $signature = 'ux:theme-audit {--json : Makine okunabilir çıktı verir}';

    public function handle(ProductExperienceAuditService $audit): int
    {
        $report = $audit->audit();
        $payload = ['rule_version' => $report['rule_version'], 'generated_at' => $report['generated_at'], 'status' => $report['status'], 'theme_tokens_present' => ! $report['findings']['missing_theme_tokens'], 'hard_coded_view_colors' => $report['findings']['hard_coded_view_colors'], 'manual_qa_required' => ['light_theme', 'dark_theme', 'contrast_measurement']];
        $this->line($this->option('json') ? (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Tema ön kontrolü: '.$payload['status']);

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
