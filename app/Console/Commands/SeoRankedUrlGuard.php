<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\RankedUrlGuardService;
use App\Services\Seo\SearchConsoleMeasurementService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Google'ın gösterdiği ama kapanmış sayfaları her gün raporlar.
 *
 * 18 Eylül 2026'da sıralanan 10 yazı bir gün önce taslağa çekilmişti ve
 * gösterimlerin dörtte biri 404'e düşüyordu. Kimse fark etmemişti.
 */
final class SeoRankedUrlGuard extends Command
{
    protected $signature = 'seo:ranked-url-guard {--json : Makine okunabilir çıktı}';

    protected $description = 'Search Console gösterimi olan ama yayında ya da indekste olmayan sayfaları raporlar.';

    public function handle(SearchConsoleMeasurementService $gsc, RankedUrlGuardService $koruma, IntegrityReportLog $log): int
    {
        try {
            $satirlar = $gsc->pageRows();
        } catch (Throwable) {
            $this->components->warn('Search Console verisi alınamadı.');

            return self::SUCCESS;
        }

        $kapananlar = $koruma->kapananlar($satirlar);
        $kayip = array_sum(array_column($kapananlar, 'impressions'));

        $log->record('seo_ranked_url_guard', $kapananlar === [] ? 'ok' : 'attention', count($kapananlar), [
            'measured_pages' => count($satirlar),
            'lost_impressions' => $kayip,
            'closed' => array_slice($kapananlar, 0, 100),
        ]);

        if ($this->option('json')) {
            $this->line((string) json_encode(['closed' => count($kapananlar), 'lost_impressions' => $kayip, 'pages' => $kapananlar], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info(count($satirlar).' sayfa ölçüldü; gösterim alan '.count($kapananlar).' sayfa kapalı ('.$kayip.' gösterim).');

        foreach (array_slice($kapananlar, 0, 15) as $satir) {
            $this->line(sprintf('  · %5d  %-14s %s', $satir['impressions'], $satir['reason'], $satir['page']));
        }

        return self::SUCCESS;
    }
}
