<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

final class SeoIndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow-submit {--dry-run : Sadece gönderilecek URL listesini görüntüler}';

    protected $description = 'Son güncellenen tarif ve blog yazılarını IndexNow (Bing/Yandex) arama motorlarına anında bildirir.';

    public function handle(IndexNowService $service): int
    {
        $urls = $service->collectRecentUrls();
        $this->line(sprintf('IndexNow kuyruğu: %d adres tespit edildi.', count($urls)));

        if ($this->option('dry-run')) {
            foreach (array_slice($urls, 0, 10) as $url) {
                $this->line('  - ' . $url);
            }
            if (count($urls) > 10) {
                $this->line(sprintf('  ... ve %d adres daha.', count($urls) - 10));
            }
            $this->info('[DRY-RUN] İstek gönderilmedi.');

            return self::SUCCESS;
        }

        $result = $service->submit($urls);

        if ($result['success'] || in_array($result['status'], [200, 202], true)) {
            $this->info(sprintf('✓ %d adres IndexNow protokolü ile arama motorlarına başarıyla bildirildi (HTTP %d).', $result['count'], $result['status']));

            return self::SUCCESS;
        }

        $this->warn(sprintf('IndexNow yanıtı: HTTP %d (Kuyruktaki %d adres)', $result['status'], $result['count']));

        return self::SUCCESS;
    }
}
