<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ReleaseCandidateAuditService;
use Illuminate\Console\Command;

final class ReleaseCandidateAudit extends Command
{
    protected $signature = 'release-candidate:rc1-audit {--json : Makine okunabilir çıktı verir}';

    protected $description = 'RC1 yayın öncesi kanıt özetini üretir; insan veya production doğrulamasını taklit etmez.';

    public function handle(ReleaseCandidateAuditService $audit): int
    {
        $report = $audit->audit();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Seviye', 'Açık bulgu'], collect($report['findings'])->map(fn (int $count, string $level): array => [str($level)->upper()->toString(), $count])->all());
            $this->line('RC1 durumu: '.$report['state'].' · Kural sürümü: '.$report['rule_version']);
        }

        return $report['findings']['blocker'] === 0 && $report['findings']['critical'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
