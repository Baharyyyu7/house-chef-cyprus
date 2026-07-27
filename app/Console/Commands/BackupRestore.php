<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupRestore extends Command
{
    protected $signature = 'backup:restore {backup_id} {--target=testing} {--verify-only} {--force}';

    public function handle(BackupService $backup): int
    {
        try {
            $manifest = $backup->verify((string) $this->argument('backup_id'));
            if ($this->option('verify-only')) {
                $this->info('Backup restore öncesi doğrulama başarılı.');

                return self::SUCCESS;
            } if ($this->option('target') !== 'testing' || app()->environment('production')) {
                $this->error('Restore yalnızca güvenli testing hedefinde çalıştırılabilir.');

                return self::FAILURE;
            } $this->info('Güvenli test restore doğrulandı: '.$manifest['backup_id']);

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Restore doğrulaması başarısız.');

            return self::FAILURE;
        }
    }
}
