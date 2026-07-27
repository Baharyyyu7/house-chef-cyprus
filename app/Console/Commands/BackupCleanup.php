<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupCleanup extends Command
{
    protected $signature = 'backup:cleanup {--delete} {--json}';

    public function handle(BackupService $backup): int
    {
        $result = $backup->cleanup((bool) $this->option('delete'));
        $this->line($this->option('json') ? json_encode($result, JSON_UNESCAPED_SLASHES) : ($result['dry_run'] ? 'Dry-run: ' : 'Silindi: ').$result['candidates']);

        return self::SUCCESS;
    }
}
