<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupHealth extends Command
{
    protected $signature = 'backup:health {--json : Makine okunabilir çıktı verir}';

    public function handle(BackupService $backup): int
    {
        $latest = $backup->list()[0] ?? null;
        $report = [
            'status' => $latest === null ? 'not_configured' : (($latest['status'] ?? '') === 'verified' ? 'healthy' : 'degraded'),
            'checked_at' => now()->toIso8601String(),
            'latest_backup' => $latest === null ? null : array_intersect_key($latest, array_flip(['backup_id', 'type', 'created_at', 'verified_at', 'restore_test_status', 'status'])),
            'safe_message' => $latest === null ? 'Henüz doğrulanabilir yedek kaydı yok.' : 'Yedek manifesti güvenli biçimde kontrol edildi.',
        ];
        $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $report['safe_message']);

        return self::SUCCESS;
    }
}
