<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentFactoryBatch;
use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Services\ContentFactoryEnrichmentService;
use Illuminate\Console\Command;

final class ContentFactoryMorningBrief extends Command
{
    protected $signature = 'content:factory-morning-brief {--json}';

    protected $description = 'Dış teslimat yapmadan yalnız yerel içerik fabrikası sabah özetini üretir.';

    public function handle(ContentFactoryEnrichmentService $enrichment): int
    {
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'recipe_batches' => ContentFactoryBatch::query()->where('content_type', 'recipe')->count(),
            'blog_batches' => ContentFactoryBatch::query()->where('content_type', 'blog')->count(),
            'waiting_for_human_review' => ContentFactoryReview::query()->where('status', 'pending')->count(),
            'fact_check_issues' => ContentFactoryIssue::query()->where('status', 'pending')->count(),
            'readiness' => $enrichment->audit(),
            'auto_publish' => false,
            'external_delivery' => 'disabled',
        ];
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('İçerik Fabrikası özeti: '.$payload['waiting_for_human_review'].' insan incelemesi bekliyor.');

        return self::SUCCESS;
    }
}
