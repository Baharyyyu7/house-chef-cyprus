<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentOpportunity;
use App\Models\SeoIssue;
use Illuminate\Console\Command;

final class SeoMorningBrief extends Command
{
    protected $signature = 'seo:morning-brief {--json}';

    protected $description = 'İçerik ekibine yalnız yerel veriden güvenli sabah özeti üretir.';

    public function handle(): int
    {
        $payload = ['open_seo_issues' => SeoIssue::query()->whereNull('resolved_at')->count(), 'content_opportunities' => ContentOpportunity::query()->where('status', 'open')->count(), 'generated_at' => now()->toIso8601String(), 'external_delivery' => 'disabled'];
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info("SEO özeti: {$payload['open_seo_issues']} açık sorun, {$payload['content_opportunities']} fırsat.");

        return self::SUCCESS;
    }
}
