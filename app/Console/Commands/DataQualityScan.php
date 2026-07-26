<?php

namespace App\Console\Commands;

use App\Models\DataQualityIssue;
use App\Services\DataQualityService;
use Illuminate\Console\Command;

final class DataQualityScan extends Command
{
    protected $signature = 'data:quality-scan';

    protected $description = 'Veri kalite sorunlarını kaydeder';

    public function handle(DataQualityService $quality): int
    {
        $issues = $quality->report();
        foreach ($issues as $issue) {
            DataQualityIssue::updateOrCreate(['issue_type' => $issue['issue_type'], 'entity_public_id' => $issue['entity_public_id'], 'resolution_status' => 'open'], $issue + ['detected_at' => now(), 'resolution_status' => 'open']);
        } $this->info(count($issues).' sorun kaydedildi.');

        return self::SUCCESS;
    }
}
