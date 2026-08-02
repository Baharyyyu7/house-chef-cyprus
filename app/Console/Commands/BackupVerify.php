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
        } catch (\Throwable $exception) {
            $code = $this->safeCode($exception);
            $result = ['status' => 'failed', 'error' => ['code' => $code, 'message' => 'Backup doğrulanamadı.']];
            $this->line($this->option('json') ? (string) json_encode($result, JSON_UNESCAPED_SLASHES) : 'Backup doğrulanamadı. ['.$code.']');

            return self::FAILURE;
        }
    }

    private function safeCode(\Throwable $exception): string
    {
        $code = $exception instanceof \RuntimeException ? $exception->getMessage() : 'BACKUP_UNEXPECTED_FAILURE';

        return preg_match('/^BACKUP_[A-Z_]+$/', $code) === 1 ? $code : 'BACKUP_UNEXPECTED_FAILURE';
    }
}
