<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

final class SeoIndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow-submit
        {--dry-run : Sadece gönderilecek URL listesini görüntüler}
        {--force : Yapılandırma kapalı olsa bile gönderimi zorlar}
        {--json : Durum ve sonucu JSON olarak döner}';

    protected $description = 'Son güncellenen tarif ve blog yazılarını IndexNow (Bing/Yandex) arama motorlarına anında bildirir.';

    public function handle(IndexNowService $service): int
    {
        $enabled = (bool) config('seo.indexnow.enabled', false);
        $forced = (bool) $this->option('force');

        if (! $enabled && ! $forced) {
            $payload = [
                'provider' => 'indexnow',
                'enabled' => false,
                'submitted' => 0,
                'reason' => 'provider_disabled',
            ];

            if ($this->option('json')) {
                $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            } else {
                $this->warn('IndexNow gönderimi yapılandırmada kapalı; hiçbir dış çağrı yapılmadı. (--force ile zorlanabilir)');
            }

            return self::SUCCESS;
        }

        $urls = $service->collectRecentUrls();

        if ($this->option('dry-run')) {
            if ($this->option('json')) {
                $this->line((string) json_encode([
                    'provider' => 'indexnow',
                    'enabled' => true,
                    'dry_run' => true,
                    'count' => count($urls),
                    'urls' => $urls,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            } else {
                $this->line(sprintf('IndexNow kuyruğu: %d adres tespit edildi.', count($urls)));
                foreach (array_slice($urls, 0, 10) as $url) {
                    $this->line('  - ' . $url);
                }
                if (count($urls) > 10) {
                    $this->line(sprintf('  ... ve %d adres daha.', count($urls) - 10));
                }
                $this->info('[DRY-RUN] İstek gönderilmedi.');
            }

            return self::SUCCESS;
        }

        $result = $service->submit($urls);

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'provider' => 'indexnow',
                'enabled' => true,
                'status' => $result['status'],
                'count' => $result['count'],
                'submitted' => $result['submitted'],
                'retry_after' => $result['retry_after'],
                'success' => $result['success'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $result['success'] ? self::SUCCESS : self::FAILURE;
        }

        if ($result['success']) {
            $this->info(sprintf('✓ %d adres IndexNow protokolü ile arama motorlarına başarıyla bildirildi (HTTP %d).', $result['count'], $result['status']));

            return self::SUCCESS;
        }

        $this->warn(sprintf('IndexNow yanıtı: HTTP %d (Kabul edilen %d adres)', $result['status'], $result['count']));

        return self::FAILURE;
    }
}
