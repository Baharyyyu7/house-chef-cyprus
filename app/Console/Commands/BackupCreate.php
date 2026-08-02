<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupCreate extends Command
{
    protected $signature = 'backup:create {--type=manual : manual, scheduled, pre-deploy veya pre-migration} {--json}';

    public function handle(BackupService $backup): int
    {
        try {
            $result = $backup->create((string) $this->option('type'));
            $this->line($this->option('json') ? json_encode($result, JSON_UNESCAPED_SLASHES) : 'Backup oluşturuldu: '.$result['backup_id']);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $code = $this->safeCode($exception);
            $result = ['status' => 'failed', 'error' => ['code' => $code, 'message' => 'Backup güvenli biçimde oluşturulamadı.']];
            $this->line($this->option('json') ? (string) json_encode($result, JSON_UNESCAPED_SLASHES) : 'Backup güvenli biçimde oluşturulamadı. ['.$code.']');

            return self::FAILURE;
        }
    }

    private function safeCode(\Throwable $exception): string
    {
        $code = $exception instanceof \RuntimeException ? $exception->getMessage() : 'BACKUP_UNEXPECTED_FAILURE';

        return preg_match('/^BACKUP_[A-Z_]+$/', $code) === 1 ? $code : 'BACKUP_UNEXPECTED_FAILURE';
    }
}
