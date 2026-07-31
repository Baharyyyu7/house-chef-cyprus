<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\ContentRefreshService;
use Illuminate\Console\Command;

final class SeoContentRefresh extends Command
{
    protected $signature = 'seo:content-refresh {--dry-run} {--json}';

    protected $description = 'Süresi gelen yayınlar için yalnız editoryal tazeleme taslakları oluşturur.';

    public function handle(ContentRefreshService $service): int
    {
        $result = $service->createReviewDrafts((bool) $this->option('dry-run'));
        $payload = $result + ['dry_run' => (bool) $this->option('dry-run'), 'auto_publish' => false];
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info("Tazeleme taslakları: {$result['created']} yeni, {$result['skipped']} atlandı.");

        return self::SUCCESS;
    }
}
