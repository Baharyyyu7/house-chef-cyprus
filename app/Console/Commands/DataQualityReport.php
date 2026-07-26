<?php

namespace App\Console\Commands;

use App\Services\DataQualityService;
use Illuminate\Console\Command;

final class DataQualityReport extends Command
{
    protected $signature = 'data:quality-report {--json : Makine okunabilir çıktı}';

    protected $description = 'Veri kalite sorunlarını değiştirmeden raporlar';

    public function handle(DataQualityService $quality): int
    {
        $issues = $quality->report();
        if ($this->option('json')) {
            $this->line(json_encode(['score' => $quality->score(), 'counts' => collect($issues)->countBy('severity'), 'issues' => $issues], JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }
        $this->info('Kalite skoru: '.$quality->score().'/100');
        foreach ($issues as $issue) {
            $this->line("[{$issue['severity']}] {$issue['message']} ({$issue['entity_public_id']})");
        } $this->info(count($issues).' sorun bulundu.');

        return self::SUCCESS;
    }
}
