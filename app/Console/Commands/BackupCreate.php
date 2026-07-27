<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupCreate extends Command
{
    protected $signature = 'backup:create {--json}';

    public function handle(BackupService $backup): int
    {
        try {
            $result = $backup->create();
            $this->line($this->option('json') ? json_encode($result, JSON_UNESCAPED_SLASHES) : 'Backup oluşturuldu: '.$result['backup_id']);

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Backup güvenli biçimde oluşturulamadı.');

            return self::FAILURE;
        }
    }
}
