<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductExperienceAuditService;
use Illuminate\Console\Command;

final class UxAudit extends Command
{
    protected $signature = 'ux:audit {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Ürün deneyimi sözleşmelerini statik olarak denetler';

    public function handle(ProductExperienceAuditService $audit): int
    {
        return $this->render($audit->audit());
    }

    /** @param array<string, mixed> $report */
    private function render(array $report): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Yüzey', 'Statik ön kontrol'], collect($report['scores'])->map(fn (int $score, string $surface) => [$surface, $score.'/100'])->values()->all());
            $this->line('Durum: '.$report['status'].' · Kural sürümü: '.$report['rule_version']);
        }

        return $report['critical_blockers'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
