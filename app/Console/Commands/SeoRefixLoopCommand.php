<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\RefixSeoLoopService;
use Illuminate\Console\Command;

final class SeoRefixLoopCommand extends Command
{
    protected $signature = 'seo:refix-loop
                            {--dry-run : Sadece analiz et ve önerileri listele, veritabanına kayıt atma}
                            {--json : Raporu saf JSON formatında yazdır}
                            {--limit=25 : Listelenecek maksimum fırsat sayısı}
                            {--apply-drafts : Untargeted fırsatlar için veritabanında taslak ContentOpportunity oluştur}';

    protected $description = "Neil Agarwal'ın Refix SEO Loop sistemini çalıştırır: GSC verilerini 6 stratejik bayrakla analiz eder ve önceliklendirir.";

    public function handle(RefixSeoLoopService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $asJson = (bool) $this->option('json');
        $limit = max(1, (int) $this->option('limit'));
        $applyDrafts = (bool) $this->option('apply-drafts');

        if (! $asJson) {
            $this->info('================================================================');
            $this->info('  REFIX SEO LOOP: OTONOM GOOGLE SEARCH CONSOLE BÜYÜME MOTORU');
            $this->info('================================================================');
            $this->comment('Neil Agarwal (@regalstreak / Refix) mimarisi ile çalışıyor.');
            $this->line('Mod: '.($dryRun ? '<comment>DRY-RUN (Salt Okunur)</comment>' : '<info>LIVE (Kayıt Açık)</info>'));
            $this->newLine();
        }

        $result = $service->runLoop(
            dryRun: $dryRun,
            applyDrafts: $applyDrafts,
            limit: $limit
        );

        if ($result['status'] === 'no_data') {
            if ($asJson) {
                $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            } else {
                $this->warn('Search Console ölçüm verisi bulunamadı veya içe aktarılmadı.');
            }

            return self::SUCCESS;
        }

        if ($asJson) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        // 1. ÖZET İSTATİSTİKLER VE BAYRAK DAĞILIMI
        $this->info(sprintf(
            'Analiz Edilen Toplam Sorgu: %d | Eyleme Dönüştürülebilir Fırsat: %d',
            $result['total_queries_analyzed'],
            $result['actionable_opportunities_count']
        ));
        $this->newLine();

        $flagRows = [];
        $flagLabels = [
            RefixSeoLoopService::FLAG_ALMOST_THERE => 'almost_there (Sıra 3-20, Sayfa 1-2 eşiği)',
            RefixSeoLoopService::FLAG_NO_CLICKS => 'no_clicks (Yüksek Gösterim, Sıfır/Düşük CTR)',
            RefixSeoLoopService::FLAG_DECAYING => 'decaying (Haftalık Tıklama Düşüşü >= %30)',
            RefixSeoLoopService::FLAG_UNTARGETED => 'untargeted (Özel Sayfası Olmayan Fırsat)',
            RefixSeoLoopService::FLAG_WRONG_INTENT => 'wrong_intent (Arama Niyeti != Sayfa Tipi)',
            RefixSeoLoopService::FLAG_AI_MODE => 'ai_mode (7+ Kelimelik AI Overview / Soru Dili)',
            RefixSeoLoopService::FLAG_IGNORE => 'ignore (Zirvede Sıra 1-2 veya Gürültü)',
        ];

        foreach ($result['flag_counts'] as $flag => $count) {
            $flagRows[] = [$flagLabels[$flag] ?? $flag, $count];
        }

        $this->table(['Stratejik Bayrak (Flag)', 'Tespit Sayısı'], $flagRows);
        $this->newLine();

        // 2. ÖNCELİKLENDİRİLMİŞ EYLEM TABLOSU
        $this->info(sprintf('ÖNCELİKLİ İLK %d FIRSAT VE EYLEM PLANI (Opportunity Score Sıralı):', count($result['top_opportunities'])));

        $tableRows = [];
        foreach ($result['top_opportunities'] as $i => $opp) {
            $tableRows[] = [
                $i + 1,
                mb_strimwidth($opp['query'], 0, 32, '...'),
                $opp['flag'],
                sprintf('%.1f', $opp['position']),
                $opp['impressions'],
                $opp['clicks'],
                $opp['opportunity_score'],
                mb_strimwidth($opp['recommended_action'], 0, 48, '...'),
            ];
        }

        $this->table(
            ['#', 'Sorgu', 'Bayrak', 'Sıra', 'Gösterim', 'Tıklama', 'Puan', 'Önerilen Eylem'],
            $tableRows
        );

        if ($applyDrafts) {
            $this->newLine();
            $this->info(sprintf('Oluşturulan Taslak İçerik Fırsatı: %d adet (ContentOpportunity tablosuna kaydedildi).', $result['drafts_created']));
        }

        $this->newLine();
        $this->comment('Refix SEO Felsefesi: "Önce mevcut sayfaları düzelt, yalnızca untargeted sorgular için yeni sayfa yaz."');

        return self::SUCCESS;
    }
}
