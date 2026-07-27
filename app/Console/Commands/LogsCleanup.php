<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class LogsCleanup extends Command
{
    protected $signature = 'logs:cleanup {--delete} {--json}';

    public function handle(): int
    {
        $files = collect(Storage::disk('local')->allFiles('logs'))->filter(fn (string $p): bool => str_starts_with($p, 'logs/') && ! str_contains($p, '..') && ! str_ends_with($p, date('Y-m-d').'.log'))->values()->all();
        $deleted = 0;
        if ($this->option('delete')) {
            foreach ($files as $f) {
                Storage::disk('local')->delete($f);
                $deleted++;
            }AuditLog::create(['action' => 'logs.cleanup', 'metadata' => ['deleted' => $deleted]]);
        } $result = ['dry_run' => ! $this->option('delete'), 'candidates' => count($files), 'deleted' => $deleted];
        $this->line($this->option('json') ? json_encode($result) : 'Log cleanup adayları: '.count($files));

        return self::SUCCESS;
    }
}
