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

        $this->info('✓ GEO Korpusu: Önbellekler temizlendi ve en güncel mevsimsel verilerle ısıtıldı.');
        $this->info("✓ IndexNow: {$report['indexnow']['submitted']} URL arama motorlarına bildirildi (HTTP {$report['indexnow']['http_status']}).");
        $this->info("✓ Backlink Havuzu: {$report['backlinks']['created']} yeni, {$report['backlinks']['updated']} güncellenen aday (Toplam {$report['backlinks']['total_prospects']}).");
        $this->info("✓ İçerik Boşlukları: {$report['content_gaps']['created']} yeni, {$report['content_gaps']['updated']} güncellenen fırsat (Toplam {$report['content_gaps']['total_opportunities']}).");
        $this->info("✓ Aktif Katalog: {$report['catalogue']['verified_recipes']} doğrulanmış tarif, {$report['catalogue']['public_blogs']} editoryal rehber yayında.");

        return self::SUCCESS;
    }
}
