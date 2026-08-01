<?php

namespace App\Console\Commands;

use App\Models\RestoreHistory;
use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupRestore extends Command
{
    protected $signature = 'backup:restore {backup_id} {--target=testing} {--verify-only} {--force}';

    public function handle(BackupService $backup): int
    {
        $history = RestoreHistory::create(['backup_id' => (string) $this->argument('backup_id'), 'target' => (string) $this->option('target'), 'triggered_by_type' => 'system', 'started_at' => now(), 'status' => 'running']);
        try {
            $manifest = $backup->markRestoreTest((string) $this->argument('backup_id'));
            if ($this->option('verify-only')) {
                $this->info('Backup restore öncesi doğrulama başarılı.');

                $history->update(['status' => 'completed', 'checksum_verified' => true, 'finished_at' => now(), 'health_check_status' => 'healthy']);

                return self::SUCCESS;
            } if ($this->option('target') !== 'testing' || app()->environment('production')) {
                $this->error('Restore yalnızca güvenli testing hedefinde çalıştırılabilir.');

                return self::FAILURE;
            } $this->info('Güvenli test restore doğrulandı: '.$manifest['backup_id']);

            $history->update(['status' => 'completed', 'checksum_verified' => true, 'finished_at' => now(), 'health_check_status' => 'healthy']);

            return self::SUCCESS;
        } catch (\Throwable) {
            $history->update(['status' => 'failed', 'finished_at' => now(), 'safe_error_code' => 'RESTORE_FAILED', 'safe_error_message' => 'Restore doğrulaması başarısız.']);
            $this->error('Restore doğrulaması başarısız.');

            return self::FAILURE;
        }
    }
}
