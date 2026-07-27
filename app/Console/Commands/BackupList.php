<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

final class BackupList extends Command
{
    protected $signature = 'backup:list {--json}';

    public function handle(BackupService $backup): int
    {
        $rows = $backup->list();
        $this->line($this->option('json') ? json_encode(['data' => $rows], JSON_UNESCAPED_SLASHES) : count($rows).' backup bulundu.');

        return self::SUCCESS;
    }
}
