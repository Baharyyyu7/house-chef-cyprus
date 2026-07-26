<?php

namespace App\Console\Commands;

use App\Models\DataQualityIssue;
use App\Services\DataQualityService;
use Illuminate\Console\Command;

final class DataQualityReport extends Command
{
    protected $signature = 'data:quality-report {--json : Makine okunabilir çıktı}';

    protected $description = 'Veri kalite sorunlarını değiştirmeden raporlar';

    public function handle(DataQualityService $quality): int
    {
        $issues = $quality->report();
        $scores = $quality->categoryScores();
        $counts = collect($issues)->countBy('severity')->sortKeys()->all();
        $lastScan = DataQualityIssue::max('last_detected_at');
        if ($this->option('json')) {
            $this->line(json_encode(['score' => $scores['overall'], 'category_scores' => $scores, 'counts' => $counts, 'last_scan_at' => $lastScan, 'issues' => $issues], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->info('Kalite skoru: '.$scores['overall'].'/100');
        $this->line('Kategori skorları: '.json_encode($scores, JSON_UNESCAPED_UNICODE));
        $this->line('Önem dağılımı: '.json_encode($counts, JSON_UNESCAPED_UNICODE));
        foreach ($issues as $issue) {
            $this->line("[{$issue['severity']}] {$issue['message']} ({$issue['entity_public_id']})");
        } $this->info(count($issues).' sorun bulundu.');

        return self::SUCCESS;
    }
}
