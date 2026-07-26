<?php

namespace App\Console\Commands;

use App\Services\DataQualityService;
use Illuminate\Console\Command;

final class DataQualityReport extends Command
{
    protected $signature = 'data:quality-report';

    protected $description = 'Veri kalite sorunlarını değiştirmeden raporlar';

    public function handle(DataQualityService $quality): int
    {
        $issues = $quality->report();
        foreach ($issues as $issue) {
            $this->line("[{$issue['severity']}] {$issue['message']} ({$issue['entity_public_id']})");
        } $this->info(count($issues).' sorun bulundu.');

        return self::SUCCESS;
    }
}
