<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\AutonomousGrowthEngine;
use Illuminate\Console\Command;

final class SeoAutonomousGrowth extends Command
{
    protected $signature = 'seo:autonomous-growth
        {--dry-run : Sadece planlanan büyüme ve besleme operasyonlarını simüle eder}
        {--force : Dış gönderim ve önbellek tazelemesini doğrudan zorlar}
        {--json : Operasyon raporunu JSON formatında sunar}';

    protected $description = 'GEO, IndexNow, Backlink ve İçerik Fırsatlarını sürekli arka planda besleyip büyüten otonom motor.';

    public function handle(AutonomousGrowthEngine $engine): int
    {
        $this->line('⚡ House Chef Otonom Büyüme & Besleme Motoru başlatılıyor...');

        if ($this->option('dry-run')) {
            $this->info('[DRY-RUN] Simülasyon modu: GEO korpusu, IndexNow kuyruğu ve backlink havuzu taranıyor...');
            $this->line('  - GEO Önbellekleri: seo:llms_txt_tr, seo:llms_txt_en, seo:llms_full_txt_tr, seo:llms_full_txt_en tazelenecek.');
            $this->line('  - IndexNow: Güncel 84+ URL arama motorlarına iletilecek.');
            $this->line('  - Backlink: Visit North Cyprus, Visit Cyprus, KTTO vb. otoriter hedefler taranacak.');
            $this->line('  - Besleme: Sıfır sonuçlu aramalar ve boşluklar taranacak.');

            if ($this->option('json')) {
                $this->line((string) json_encode(['dry_run' => true, 'status' => 'simulated'], JSON_UNESCAPED_UNICODE));
            }

            return self::SUCCESS;
        }

        $report = $engine->runCycle((bool) $this->option('force'));

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $geoLocales = isset($report['geo']['locales']) ? implode(', ', $report['geo']['locales']) : 'tr, en';
        $geoCount = $report['geo']['corpora_count'] ?? 4;
        $this->info("✓ GEO Korpusu: {$geoCount} AI korpusu ({$geoLocales}) temizlendi ve en güncel verilerle ısıtıldı.");
        $this->info("✓ IndexNow: {$report['indexnow']['submitted']} URL arama motorlarına bildirildi (HTTP {$report['indexnow']['http_status']}).");

        if (isset($report['gsc_momentum'])) {
            $mom = $report['gsc_momentum'];
            $dirIcon = $mom['direction'] === 'growing' ? '🚀' : ($mom['direction'] === 'declining' ? '⚠️' : '📊');
            $this->info("{$dirIcon} GSC Büyüme İvmesi: %{$mom['growth_velocity_pct']} ({$mom['direction']}) · {$mom['striking_distance_count']} Vurucu Mesafe Sorgusu · {$mom['trending_keywords_count']} Trend Kelime.");
        }

        $marketSummary = [];
        if (! empty($report['backlinks']['markets'])) {
            foreach ($report['backlinks']['markets'] as $pazar => $stat) {
                $marketSummary[] = strtoupper($pazar) . ": {$stat['total']}";
            }
        }
        $marketStr = ! empty($marketSummary) ? ' [' . implode(' · ', $marketSummary) . ']' : '';
        $this->info("✓ Backlink Havuzu: {$report['backlinks']['created']} yeni, {$report['backlinks']['updated']} güncellenen aday (Toplam {$report['backlinks']['total_prospects']}){$marketStr}.");
        $this->info("✓ İçerik Boşlukları: {$report['content_gaps']['created']} yeni, {$report['content_gaps']['updated']} güncellenen fırsat (Toplam {$report['content_gaps']['total_opportunities']}).");
        $this->info("✓ Aktif Katalog: {$report['catalogue']['verified_recipes']} doğrulanmış tarif, {$report['catalogue']['public_blogs']} editoryal rehber yayında.");

        if (isset($report['daily_targets'])) {
            $targets = $report['daily_targets'];
            $this->info('🎯 Günlük Büyüme Hedefleri (100 SEO · 100 GEO · 100 Backlink):');
            $this->line("   - SEO Hedefi: {$targets['seo']['achieved']}/{$targets['seo']['target']} (%{$targets['seo']['progress_percent']}) " . ($targets['seo']['target_met'] ? '✓ Başarıldı' : '… Devam ediyor'));
            $this->line("   - GEO Hedefi: {$targets['geo']['achieved']}/{$targets['geo']['target']} (%{$targets['geo']['progress_percent']}) " . ($targets['geo']['target_met'] ? '✓ Başarıldı' : '… Devam ediyor'));
            $this->line("   - Backlink Hedefi: {$targets['backlinks']['achieved']}/{$targets['backlinks']['target']} (%{$targets['backlinks']['progress_percent']}) " . ($targets['backlinks']['target_met'] ? '✓ Başarıldı' : '… Devam ediyor'));
        }

        return self::SUCCESS;
    }
}
