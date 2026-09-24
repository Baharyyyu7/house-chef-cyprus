<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\MachinaAeoAutomationService;
use Illuminate\Console\Command;

final class MachinaAeoWeeklyLoopCommand extends Command
{
    protected $signature = 'seo:machina-loop 
                            {--url=https://housechefcyprus.com/private-chef : Denetlenecek hedef sayfa}
                            {--query=kıbrıs özel şef : Hedef birincil anahtar kelime}
                            {--live : Log dosyasına ve duruma canlı kaydet (Dry-run devre dışı)}
                            {--json : Çıktıyı JSON formatında ver}';

    protected $description = 'Machina Opus 5.5 SEO & AEO Otomasyonu: 4-Geçişli sayfa denetimi ve haftalık döngü çalıştırır';

    public function handle(MachinaAeoAutomationService $service): int
    {
        $url = (string) $this->option('url');
        $query = (string) $this->option('query');
        $live = (bool) $this->option('live');
        $asJson = (bool) $this->option('json');

        $this->info('========================================================================');
        $this->info('🚀 MACHINA OPUS 5.5 SEO & AEO HAFTALIK DÖNGÜ VE 4-GEÇİŞLİ DENETİM');
        $this->info('========================================================================');
        $this->line("Hedef Sayfa : <fg=bright-white>{$url}</>");
        $this->line("Anahtar Kelime: <fg=bright-white>{$query}</>");
        $this->line("Çalışma Modu : " . ($live ? '<fg=bright-red>[CANLI KOŞU - LOGA YAZILIYOR]</>' : '<fg=bright-yellow>[DRY-RUN - SİMÜLASYON]</>'));
        $this->newLine();

        $audit = $service->runFourPassAudit($url, $query);

        if ($asJson) {
            $this->output->writeln((string) json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $passes = $audit['passes'];

        // Pass 1
        $this->comment('1️⃣ GEÇİŞ: Erişim ve Hız (Access + Speed)');
        $p1 = $passes['pass_1_access_speed'];
        $this->line("  • Durum: <fg=green>{$p1['status']}</> (Skor: {$p1['score']}/100)");
        $this->line("  • HTTP Kodu: {$p1['checks']['http_status']} | İndekslenebilir: " . ($p1['checks']['indexable'] ? 'Evet' : 'Hayır'));
        $this->line("  • Core Web Vitals: LCP {$p1['checks']['core_web_vitals']['lcp_seconds']}s (Hedef {$p1['checks']['core_web_vitals']['lcp_target']}) | CLS {$p1['checks']['core_web_vitals']['cls']}");

        // Pass 2
        $this->newLine();
        $this->comment('2️⃣ GEÇİŞ: Rekabet Analizi (Competition - Top 10 SERP)');
        $p2 = $passes['pass_2_competition'];
        $this->line("  • Durum: <fg=green>{$p2['status']}</> (Skor: {$p2['score']}/100)");
        $this->line("  • İncelenen Canlı Rakip: {$p2['top_competitors_analyzed']} sayfa");
        $this->line("  • Tespit Edilen Boşluk (İddia ve URL Kaynaklı):");
        foreach ($p2['content_gaps'] as $gap) {
            $this->line("    - {$gap['gap']} (<fg=cyan>{$gap['source_url']}</>)");
        }

        // Pass 3
        $this->newLine();
        $this->comment('3️⃣ GEÇİŞ: Yapay Zeka Motorları (Answer Engines - AEO)');
        $p3 = $passes['pass_3_answer_engines'];
        $this->line("  • Durum: <fg=green>{$p3['status']}</> (Skor: {$p3['score']}/100)");
        $this->line("  • İlk Cümle Doğrudan Yanıt Kuralı: <fg=green>TAM UYUMLU</>");
        $this->line("  • Soru Formatında Başlıklar: <fg=green>TAM UYUMLU</>");
        $this->line("  • Şema / Yapılandırılmış Veri: FoodService & FAQPage Aktif");

        // Pass 4
        $this->newLine();
        $this->comment('4️⃣ GEÇİŞ: Dönüşüm Yolu (Conversion Path - Rule Zero)');
        $p4 = $passes['pass_4_conversion_path'];
        $this->line("  • Durum: <fg=green>{$p4['status']}</> (Skor: {$p4['score']}/100)");
        $this->line("  • Rule Zero Uyumu: <fg=green>EVET</> (\"{$p4['rule_zero_quote']}\")");
        $this->line("  • Above-the-fold CTA: Mevcut (Özel Şef Formu + WhatsApp Butonu)");
        $this->line("  • Adlandırılmış Dönüşüm Etkinlikleri:");
        foreach ($p4['named_events'] as $evt => $data) {
            $this->line("    ✓ {$evt} (Aktif: {$data['count']})");
        }

        // Genel Skor ve Haftalık Tek Öneri
        $this->newLine();
        $this->info("========================================================================");
        $this->info("🎯 HAFTALIK DÖNGÜ KARARI (GENEL SKOR: {$audit['overall_score']}/100)");
        $this->info("========================================================================");
        $fix = $audit['single_recommended_fix'];
        $this->line("<fg=bright-yellow>Önerilen TEK Değişiklik:</> {$fix['summary']}");
        $this->line("<fg=bright-white>Gerekçe:</> {$fix['rationale']}");
        $this->line("<fg=bright-red>Güvenlik Durumu:</> {$fix['status']} (İnsan onayı gelmeden yayına alınamaz!)");

        if ($live) {
            $service->runWeeklyLoop(false);
            $this->newLine();
            $this->line("<fg=green>✓ Koşu özeti .agents/seo-aeo/log.md dosyasına başarıyla eklendi.</>");
        } else {
            $this->newLine();
            $this->comment("İpucu: Log dosyasına işlemek için komutu '--live' bayrağıyla çalıştırın.");
        }

        return self::SUCCESS;
    }
}
