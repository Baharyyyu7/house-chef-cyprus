<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\ContentGapService;
use Illuminate\Console\Command;

final class SeoContentOpportunities extends Command
{
    protected $signature = 'seo:content-opportunities {--json}';

    protected $description = 'Gerçek platform verisinden editoryal içerik fırsatları üretir.';

    public function handle(ContentGapService $service): int
    {
        $result = $service->refresh();
        if ($this->option('json')) {
            $this->line((string) json_encode(['created' => $result['created'], 'updated' => $result['updated'], 'count' => count($result['opportunities'])], JSON_UNESCAPED_UNICODE));
        } else {
            $this->info("İçerik fırsatları: yeni {$result['created']}, güncellenen {$result['updated']}");
        }

        return self::SUCCESS;
    }
}
