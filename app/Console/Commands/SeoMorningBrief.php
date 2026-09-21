<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentOpportunity;
use App\Models\SeoIssue;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\SearchConsoleMeasurementService;
use App\Services\Seo\SeoProviderHealthService;
use Illuminate\Console\Command;

final class SeoMorningBrief extends Command
{
    protected $signature = 'seo:morning-brief {--json}';

    protected $description = 'İçerik ekibine yalnız yerel veriden güvenli sabah özeti üretir.';

    public function handle(): int
    {
        $payload = [
            'open_seo_issues' => SeoIssue::query()->whereNull('resolved_at')->count(),
            'content_opportunities' => ContentOpportunity::query()->where('status', 'open')->count(),
            'generated_at' => now()->toIso8601String(),
            'external_delivery' => 'disabled',
            // Catalogue health is not Google traffic or third-party backlink coverage.
            'organic_metrics' => app(SearchConsoleMeasurementService::class)->snapshot(),
            'providers' => app(SeoProviderHealthService::class)->snapshot(),
            'priority_issues' => SeoIssue::query()->whereNull('resolved_at')
                ->orderByRaw("CASE severity WHEN 'critical' THEN 0 WHEN 'error' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                ->orderBy('first_detected_at')->limit(5)->get(['severity', 'category', 'url', 'message'])->toArray(),
            'automation' => [
                'publishes_content' => false,
                'paid_provider_calls' => false,
                'creates_backlinks' => false,
                'next_step' => 'Önce teknik engelleri ve gerçek performans veri bağlantısını tamamla; içerik ve dış bağlantı kararları editoryal kontrolden geçer.',
            ],
        ];
        app(IntegrityReportLog::class)->record('seo:morning-brief', 'measurement_incomplete', $payload['open_seo_issues'], $payload);
        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("SEO Özeti: {$payload['open_seo_issues']} açık sorun, {$payload['content_opportunities']} fırsat.");
            $m = $payload['organic_metrics'] ?? [];
            if (($m['status'] ?? '') === 'measured') {
                $this->line("📊 GSC Organik Ölçüm: {$m['google_impressions']} gösterim, {$m['google_clicks']} tıklama (Ort. Sıra: {$m['average_position']})");
                $v = $m['growth_velocity'] ?? [];
                if (($v['direction'] ?? '') === 'growing') {
                    $this->info("🚀 Büyüme İvmesi: +{$v['impression_change']} gösterim (+%{$v['growth_velocity_pct']})");
                }
                $sd = $m['striking_distance'] ?? [];
                if (! empty($sd)) {
                    $topSd = array_slice($sd, 0, 3);
                    $sdList = implode(', ', array_map(fn ($row) => "'{$row['query']}' (#".round($row['position'], 1).")", $topSd));
                    $this->line("🎯 Vurucu Mesafe (İlk 3'e Aday): {$sdList}");
                }
            }
        }

        return self::SUCCESS;
    }
}
