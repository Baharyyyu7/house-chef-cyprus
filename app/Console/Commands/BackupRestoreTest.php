<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupRestoreTest extends Command
{
    protected $signature = 'backup:restore-test {backup_id?} {--target=testing} {--json : Makine okunabilir çıktı verir}';

    public function handle(BackupService $backup): int
    {
        $latest = $backup->list()[0]['backup_id'] ?? null;
        $id = (string) ($this->argument('backup_id') ?: $latest);
        if ($id === '' || $this->option('target') !== 'testing' || app()->environment('production')) {
            $this->error('Restore testi yalnız güvenli testing hedefinde yürütülebilir.');

            return self::FAILURE;
        }
        try {
            $manifest = $backup->markRestoreTest($id);
            $report = ['status' => 'healthy', 'backup_id' => $id, 'target' => 'testing', 'restore_test_status' => 'verified_only', 'checked_at' => now()->toIso8601String(), 'safe_message' => 'Backup checksum ve SQLite şeması testing hedefi için doğrulandı.'];
            $this->line($this->option('json') ? (string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Güvenli restore testi doğrulandı: '.$manifest['backup_id']);

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Güvenli restore testi başarısız.');

            return self::FAILURE;
        }
    }
}
