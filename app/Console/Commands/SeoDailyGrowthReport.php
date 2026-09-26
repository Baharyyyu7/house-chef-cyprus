<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\DailyGrowthService;
use App\Services\Seo\SearchConsoleMeasurementService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Her sabah: dünden büyüdük mü, büyümeyi ne durduruyor.
 *
 * Komut iki şey yapar. Bir: bugünün Search Console toplamını bir önceki
 * koşuyla karşılaştırır, böylece "her gün bir öncekinden yüksek" iddiası
 * ölçülebilir hale gelir. İki: ilk yirmide görünüp hiç tıklanmayan
 * sayfaları ayıklar ve her biri için bir iş kaydı açar; bunlar başlık ve
 * açıklama işidir, sıra beklemeden bugün düzeltilebilir.
 *
 * İş kaydı açar, içerik yazmaz. Başlık yazmak insan ya da editör kararıdır;
 * otomatik başlık üretimi 13 Eylül'de şablon içerik üretmişti.
 */
final class SeoDailyGrowthReport extends Command
{
    protected $signature = 'seo:daily-growth-report {--gun=7 : Ölçüm penceresi (gün)} {--json}';

    protected $description = 'Günlük organik büyümeyi bir önceki ölçümle karşılaştırır ve tıklanmayan sayfalar için iş kaydı açar.';

    public function handle(SearchConsoleMeasurementService $gsc, DailyGrowthService $servis, IntegrityReportLog $log): int
    {
        try {
            $satirlar = $gsc->pageRows(max(1, (int) $this->option('gun')));
        } catch (Throwable) {
            $satirlar = [];
        }

        if ($satirlar === []) {
            $this->components->warn('Search Console verisi alınamadı; ölçüm yapılmadı.');

            return self::SUCCESS;
        }

        $bugun = $servis->toplam($satirlar);
        $onceki = $this->oncekiToplam();
        $fark = $servis->fark($bugun, $onceki);
        $firsatlar = $servis->tiklanmayanlar($satirlar);
        $acilan = $this->isKaydiAc($firsatlar);

        $log->record(
            'seo_daily_growth',
            $fark['direction'] === 'kucculdu' ? 'attention' : 'ok',
            $bugun['clicks'],
            [
                'window_days' => (int) $this->option('gun'),
                'today' => $bugun,
                'previous' => $onceki,
                'delta' => $fark,
                'zero_click_pages' => count($firsatlar),
                'opportunities_opened' => $acilan,
                'top_zero_click' => array_slice($firsatlar, 0, 50),
            ]
        );

        if ($this->option('json')) {
            $this->line((string) json_encode(['today' => $bugun, 'delta' => $fark, 'zero_click' => $firsatlar], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            '%d gün: %d tıklama, %d gösterim, TO %%%.2f, sıra %.2f (%s)',
            (int) $this->option('gun'), $bugun['clicks'], $bugun['impressions'], $bugun['ctr'], $bugun['position'], $fark['direction']
        ));

        if ($onceki !== null) {
            $this->line(sprintf('  önceki ölçüme göre: tıklama %+d, gösterim %+d, sıra %+.2f', $fark['clicks'], $fark['impressions'], $fark['position']));
        }

        $this->line(sprintf('  görünüp tıklanmayan %d sayfa, %d yeni iş kaydı', count($firsatlar), $acilan));

        foreach (array_slice($firsatlar, 0, 15) as $satir) {
            $this->line(sprintf('  · %5d gösterim  sıra %4.1f  %s', $satir['impressions'], $satir['position'], $satir['page']));
        }

        return self::SUCCESS;
    }

    /**
     * Önceki GÜNÜN son ölçümü.
     *
     * Eskiden en son rapor alınıyordu. 24 Eylül 2026'da ölçüldü: görev
     * 07:05'te iki kez koşuyor ve ikinci koşu 37 saniye önceki birinciyle
     * karşılaştırılıyordu; "dünden büyüdük mü" sorusunun cevabı her sabah
     * "aynı" çıkıyordu. Gün içindeki elle koşular da aynı hatayı yapıyordu.
     *
     * @return array{clicks: int, impressions: int, pages: int, ctr: float, position: float}|null
     */
    private function oncekiToplam(): ?array
    {
        $rapor = IntegrityReport::query()->where('guard', 'seo_daily_growth')
            ->where('created_at', '<', now()->startOfDay())
            ->latest('id')->first();
        $bugun = $rapor?->payload['today'] ?? null;

        return is_array($bugun) ? $bugun : null;
    }

    /** @param list<array{page: string, impressions: int, position: float}> $firsatlar */
    private function isKaydiAc(array $firsatlar): int
    {
        $acilan = 0;

        foreach (array_slice($firsatlar, 0, 25) as $satir) {
            $kayit = ContentOpportunity::query()->firstOrCreate(
                ['normalized_key' => 'ctr-'.hash('sha256', $satir['page'])],
                [
                    'kind' => 'ctr_rewrite',
                    'title' => 'Başlık ve açıklama incelemesi: '.$satir['page'],
                    'search_intent' => 'informational',
                    'topic_cluster' => 'Tıklanma oranı',
                    'priority' => min(100, 50 + $satir['impressions']),
                    'risk_level' => 'low',
                    'status' => 'open',
                    'evidence' => [
                        'source' => 'daily_growth_report',
                        'page' => $satir['page'],
                        'impressions' => $satir['impressions'],
                        'clicks' => 0,
                        'position' => $satir['position'],
                        'observed_at' => now()->toIso8601String(),
                        'publication_requires_human_review' => true,
                    ],
                ]
            );

            $acilan += (int) $kayit->wasRecentlyCreated;
        }

        return $acilan;
    }
}
