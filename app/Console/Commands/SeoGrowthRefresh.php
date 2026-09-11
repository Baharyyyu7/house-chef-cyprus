<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\OrganicGrowthService;
use Illuminate\Console\Command;
use Throwable;

final class SeoGrowthRefresh extends Command
{
    protected $signature = 'seo:growth-refresh {--limit=1000} {--json}';

    protected $description = 'Yerel SEO kanıtlarından sınırlı inceleme işleri ve dış bağlantı araştırma adayları hazırlar.';

    public function handle(OrganicGrowthService $service): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => OrganicGrowthService::MAX_LIMIT]]);
        if ($limit === false) {
            $this->error('Limit 1 ile 1000 arasında bir tam sayı olmalıdır.');

            return self::INVALID;
        }
        try {
            $result = $service->refresh($limit);
        } catch (Throwable $exception) {
            report($exception);
            $result = ['status' => 'failed', 'error_code' => 'GROWTH_REFRESH_FAILED'];
        }
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif ($result['status'] === 'success') {
            $this->info("Büyüme kuyruğu: {$result['opportunities_created']} yeni iş, {$result['candidates_created']} araştırma adayı.");
        } else {
            $this->warn($result['status'] === 'busy' ? 'Büyüme kuyruğu zaten yenileniyor.' : 'Büyüme kuyruğu yenilenemedi. Koşu geçmişini kontrol edin.');
        }

        return $result['status'] === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
