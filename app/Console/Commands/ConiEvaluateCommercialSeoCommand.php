<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\ConiCommercialSeoService;
use Illuminate\Console\Command;

final class ConiEvaluateCommercialSeoCommand extends Command
{
    protected $signature = 'seo:coni-evaluate 
                            {keyword? : Değerlendirilecek tekil anahtar kelime}
                            {--locale=tr : Pazar dil kodu (tr, en, ru, de)}
                            {--all : Hazır Kıbrıs ticari portföyünü değerlendir}
                            {--json : Çıktıyı JSON formatında ver}';

    protected $description = 'ConiSEOBot: Ticari değer matematiksel formülü ile anahtar kelime gelir potansiyeli analizi yapar';

    public function handle(ConiCommercialSeoService $service): int
    {
        $keyword = $this->argument('keyword');
        $all = (bool) $this->option('all');
        $asJson = (bool) $this->option('json');
        $locale = (string) $this->option('locale');

        if ($all || empty($keyword)) {
            $summary = $service->getPortfolioSummary();

            if ($asJson) {
                $this->output->writeln((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return self::SUCCESS;
            }

            $this->info('========================================================================');
            $this->info('📊 CONISEOBOT: TİCARİ DEĞER VE GELİR PORTFÖYÜ ANALİZİ');
            $this->info('========================================================================');
            $this->line("Toplam Kelime Sayısı : <fg=bright-white>{$summary['total_keywords']}</>");
            $this->line("Toplam Arama Hacmi   : <fg=bright-white>{$summary['total_volume']}</> arama/ay");
            $this->line("Aylık Potansiyel Gelir: <fg=green>₺" . number_format($summary['potential_monthly_try'], 2) . "</> (~€" . number_format($summary['potential_monthly_eur'], 2) . ")");
            $this->line("Yıllık Projeksiyon   : <fg=green>₺" . number_format($summary['potential_yearly_try'], 2) . "</>");
            $this->newLine();

            $rows = [];
            foreach ($summary['all_items'] as $item) {
                $rows[] = [
                    $item['keyword'],
                    strtoupper($item['locale']),
                    $item['intent'],
                    $item['volume'],
                    $item['product_fit']['score'],
                    '₺' . number_format($item['expected_value_monthly_try']['low'], 0) . ' - ₺' . number_format($item['expected_value_monthly_try']['high'], 0),
                    '₺' . number_format($item['expected_value_monthly_try']['mid'], 0),
                    $item['priority_score'],
                ];
            }

            $this->table(
                ['Kelime', 'Dil', 'Niyet', 'Hacim', 'Uyum', 'Aralık (Düşük - Yüksek)', 'Beklenen Değer (MID)', 'Skor'],
                $rows
            );

            $this->newLine();
            $this->comment('🎯 En Yüksek Öncelikli Aksiyon:');
            $topItem = $summary['top_opportunities'][0] ?? null;
            if ($topItem) {
                $this->line("➔ <fg=yellow>{$topItem['keyword']}</> ({$topItem['intent']}) : {$topItem['recommendation']}");
            }

            return self::SUCCESS;
        }

        // Tekil kelime analizi
        $result = $service->evaluateKeyword((string) $keyword, ['locale' => $locale]);

        if ($asJson) {
            $this->output->writeln((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info("========================================================================");
        $this->info("🔍 CONISEOBOT KELİME DEĞERLENDİRMESİ: {$result['keyword']}");
        $this->info("========================================================================");
        $this->line("Hedef Dil / Pazar    : " . strtoupper($result['locale']));
        $this->line("Arama Niyeti         : {$result['intent']}");
        $this->line("Ürün/Hizmet Uyumu    : {$result['product_fit']['label']} (Katsayı: {$result['product_fit']['score']})");
        $this->line("Tahmini Hacim        : {$result['volume']} arama/ay");
        $this->line("SERP Özellik İndirimi: {$result['serp']['feature']} (Çarpan: {$result['serp']['discount']})");
        $this->line("Dönüşüm Tipi         : {$result['conversion_type']}");
        $this->newLine();

        $this->line("<fg=cyan>Matematiksel Formül Çıktısı (Aylık):</>");
        $this->line("• Tahmini Tıklama    : Düşük {$result['expected_clicks_monthly']['low']} | <fg=bright-white>MID {$result['expected_clicks_monthly']['mid']}</> | Yüksek {$result['expected_clicks_monthly']['high']}");
        $this->line("• Tahmini Dönüşüm    : Düşük {$result['expected_conversions_monthly']['low']} | <fg=bright-white>MID {$result['expected_conversions_monthly']['mid']}</> | Yüksek {$result['expected_conversions_monthly']['high']}");
        $this->line("• Beklenen Değer TRY : Düşük ₺{$result['expected_value_monthly_try']['low']} | <fg=green>MID ₺{$result['expected_value_monthly_try']['mid']}</> | Yüksek ₺{$result['expected_value_monthly_try']['high']}");
        $this->line("• Beklenen Değer EUR : Düşük €{$result['expected_value_monthly_eur']['low']} | <fg=green>MID €{$result['expected_value_monthly_eur']['mid']}</> | Yüksek €{$result['expected_value_monthly_eur']['high']}");
        $this->line("• Öncelik Puanı      : <fg=bright-yellow>{$result['priority_score']} / 100</>");
        $this->newLine();
        $this->comment("Tavsiye:");
        $this->line("➔ {$result['recommendation']}");

        return self::SUCCESS;
    }
}
