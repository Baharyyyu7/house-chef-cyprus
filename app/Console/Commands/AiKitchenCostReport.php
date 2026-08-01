<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\AiKitchenService;
use Illuminate\Console\Command;

final class AiKitchenCostReport extends Command
{
    protected $signature = 'ai-kitchen:cost-report {--json : Makine okunabilir çıktı}';

    protected $description = 'AI Kitchen maliyet, cache ve gecikme özetini raporlar';

    public function handle(AiKitchenService $kitchen): int
    {
        $report = collect($kitchen->stats())->only(['requests', 'cost_cents', 'cache_hits', 'fallbacks', 'failures', 'average_latency_ms', 'safety_events'])->all();
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        foreach ($report as $key => $value) {
            $this->line("{$key}: {$value}");
        }

        return self::SUCCESS;
    }
}
