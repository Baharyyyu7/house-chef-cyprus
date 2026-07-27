<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupVerify extends Command
{
    protected $signature = 'backup:verify {backup_id?} {--json}';

    public function handle(BackupService $backup): int
    {
        try {
            $rows = $backup->list();
            $id = $this->argument('backup_id') ?: ($rows[0]['backup_id'] ?? null);
            if (! is_string($id)) {
                throw new \RuntimeException;
            } $result = $backup->verify($id);
            $this->line($this->option('json') ? json_encode($result, JSON_UNESCAPED_SLASHES) : 'Backup doğrulandı: '.$result['backup_id']);

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Backup doğrulanamadı.');

            return self::FAILURE;
        }
    }
}
