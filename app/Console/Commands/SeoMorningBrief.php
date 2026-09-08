<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentOpportunity;
use App\Models\SeoIssue;
use App\Services\Integrity\IntegrityReportLog;
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
            'organic_metrics' => [
                'status' => 'not_connected',
                'google_clicks' => null,
                'ranking_queries' => null,
                'external_referring_domains' => null,
                'note' => 'Search Console performans ve bağlantı verisi henüz içe alınmıyor. Bilinmeyen değer sıfır değildir.',
            ],
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
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info("SEO özeti: {$payload['open_seo_issues']} açık sorun, {$payload['content_opportunities']} fırsat.");

        return self::SUCCESS;
    }
}
