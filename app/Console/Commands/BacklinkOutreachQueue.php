<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SeoBacklinkCandidate;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Backlink Outreach Queue — Yüksek otoriteye sahip yayıncılar için
 * kişiselleştirilmiş dış bağlantı iş birliği taslakları ve dosyaları hazırlar.
 *
 * AGENTS.md Kuralı: Otomatik e-posta GÖNDERMEZ.
 * Hazırlanan taslaklar insan incelemesi için storage/app/seo-outreach/ klasörüne JSON/Markdown olarak kaydedilir.
 */
final class BacklinkOutreachQueue extends Command
{
    protected $signature = 'seo:backlink-outreach-queue
                            {--limit=20 : Kuyruğa alınacak aday sayısı}
                            {--dry-run : Dosya yazmaz, sadece listeler}
                            {--export-md : Markdown formatında da özet rapor oluşturur}';

    protected $description = 'Yüksek kaliteli yayıncılar için backlink iletişim dosyalarını ve e-posta taslaklarını hazırlar.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $exportMd = (bool) $this->option('export-md');

        $this->info("Backlink Outreach Kuyruğu Hazırlanıyor (Limit: {$limit})" . ($dryRun ? ' [DRY-RUN]' : ''));

        $candidates = SeoBacklinkCandidate::query()
            ->whereIn('status', ['candidate', 'reviewing'])
            ->whereNotNull('source_url')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) {
            $this->warn('Kuyruğa uygun aday bulunamadı.');
            return self::SUCCESS;
        }

        $dossiers = [];
        $markdownLines = [];
        $markdownLines[] = "# House Chef Cyprus — Backlink İletişim Kuyruğu (" . now()->format('Y-m-d H:i') . ")\n";
        $markdownLines[] = "Bu dosya insan onayı için otomatik üretilmiştir. E-postalar doğrudan gönderilmez.\n";

        foreach ($candidates as $candidate) {
            $domain = $candidate->source_host ?: parse_url($candidate->source_url, PHP_URL_HOST);
            $cleanHost = preg_replace('/^www\./', '', (string) $domain);

            // Hedef konu ve eşleşen varlık
            $topic = $this->detectTopic((string) $candidate->source_url);
            $pitchAsset = $this->resolvePitchAsset($topic);

            $emailTr = $this->buildEmailTemplateTr($cleanHost, $topic, $pitchAsset);
            $emailEn = $this->buildEmailTemplateEn($cleanHost, $topic, $pitchAsset);

            $dossier = [
                'candidate_id' => $candidate->id,
                'public_id' => $candidate->public_id,
                'source_host' => $cleanHost,
                'source_url' => $candidate->source_url,
                'target_url' => $candidate->target_url ?: url('/'),
                'topic' => $topic,
                'recommended_asset' => $pitchAsset,
                'templates' => [
                    'tr' => $emailTr,
                    'en' => $emailEn,
                ],
                'status' => 'ready_for_review',
                'created_at' => now()->toIso8601String(),
            ];

            $dossiers[] = $dossier;

            $this->line("  ✓ [{$candidate->id}] {$cleanHost} → {$pitchAsset['title']} ({$topic})");

            $markdownLines[] = "## {$cleanHost} (#{$candidate->id})";
            $markdownLines[] = "- **Kaynak URL:** `{$candidate->source_url}`";
            $markdownLines[] = "- **Önerilen Kaynak:** [{$pitchAsset['title']}]({$pitchAsset['url']})";
            $markdownLines[] = "- **İngilizce Konu:** `{$emailEn['subject']}`";
            $markdownLines[] = "- **Türkçe Konu:** `{$emailTr['subject']}`\n";
        }

        if (! $dryRun) {
            $dir = storage_path('app/seo-outreach');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $date = now()->format('Y-m-d');
            $jsonFile = "{$dir}/outreach-queue-{$date}.json";
            file_put_contents($jsonFile, json_encode($dossiers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->info("✓ JSON dosyası kaydedildi: {$jsonFile}");

            if ($exportMd) {
                $mdFile = "{$dir}/outreach-queue-{$date}.md";
                file_put_contents($mdFile, implode("\n", $markdownLines));
                $this->info("✓ Markdown raporu kaydedildi: {$mdFile}");
            }
        }

        $this->newLine();
        $this->info("Toplam " . count($dossiers) . " yayıncı için iletişim dosyası hazırlandı.");

        return self::SUCCESS;
    }

    private function detectTopic(string $url): string
    {
        $urlLower = strtolower($url);
        if (str_contains($urlLower, 'gastronomy') || str_contains($urlLower, 'culinary') || str_contains($urlLower, 'mutfak')) {
            return 'gastronomy_education';
        }
        if (str_contains($urlLower, 'travel') || str_contains($urlLower, 'tourism') || str_contains($urlLower, 'visit') || str_contains($urlLower, 'guide')) {
            return 'tourism_and_travel';
        }
        if (str_contains($urlLower, 'wine') || str_contains($urlLower, 'food') || str_contains($urlLower, 'recipe') || str_contains($urlLower, 'tarif')) {
            return 'food_and_wine';
        }
        if (str_contains($urlLower, 'health') || str_contains($urlLower, 'diet') || str_contains($urlLower, 'nutrition')) {
            return 'mediterranean_diet';
        }

        return 'cyprus_culture';
    }

    /**
     * @return array{title: string, url: string, pitch: string}
     */
    private function resolvePitchAsset(string $topic): array
    {
        return match ($topic) {
            'gastronomy_education' => [
                'title' => 'Geleneksel Kıbrıs Mutfağı ve Coğrafi İşaretli Miras Rehberi',
                'url' => url('/blog/kibris-mutfagi-rehberi'),
                'pitch' => 'Hellim, kleftiko ve molehiya gibi coğrafi miras yemeklerinin tarihsel gelişimi ve standart gramajlı adımları.',
            ],
            'tourism_and_travel' => [
                'title' => 'Kuzey Kıbrıs Gastronomi ve Private Chef Rehberi',
                'url' => url('/private-chef'),
                'pitch' => 'Ada ziyaretçileri için villa tatilinde otantik Akdeniz sofrası deneyimi ve yerel şef hizmeti.',
            ],
            'food_and_wine' => [
                'title' => 'Kıbrıs Şarapları ve Yemek Eşleşme Rehberi',
                'url' => url('/blog/kibris-saraplari-rehberi'),
                'pitch' => 'Commandaria ve yerel ada üzümleriyle geleneksel meze sofralarının uzman gastronomi eşleşmeleri.',
            ],
            'mediterranean_diet' => [
                'title' => '30 Günlük Akdeniz Diyeti ve Sağlıklı Beslenme Planı',
                'url' => url('/blog/akdeniz-diyeti-30-gun'),
                'pitch' => 'Zeytinyağı, baklagil ve taze otlar temelli kanıta dayalı Akdeniz beslenme kılavuzu.',
            ],
            default => [
                'title' => 'House Chef Cyprus — Otantik Kıbrıs Tarifleri Hub',
                'url' => url('/mutfaklar/kibris-mutfagi'),
                'pitch' => 'Kuzey Kıbrıs mutfağının doğrulanmış ve gramajlı 900+ geleneksel tarifi.',
            ],
        };
    }

    /**
     * @param array{title: string, url: string, pitch: string} $asset
     * @return array{subject: string, body: string}
     */
    private function buildEmailTemplateTr(string $host, string $topic, array $asset): array
    {
        $subject = "{$host} İçin Kıbrıs Gastronomi & Mutfak Mirası Kaynak Önerisi";
        $body = "Merhaba {$host} Editör Ekibi,\n\n"
            . "{$host} üzerindeki ada kültürü ve gastronomi içeriklerinizi ilgiyle takip ediyoruz.\n\n"
            . "House Chef Cyprus ekibi olarak, geleneksel Kıbrıs mutfağını ve Akdeniz lezzetlerini bilimsel doğruluk, gramajlı reçeteler ve kültürel bağlamla belgeleyen kapsamlı bir kaynak hazırladık:\n\n"
            . "📌 Kaynak: {$asset['title']}\n"
            . "🔗 Bağlantı: {$asset['url']}\n"
            . "💡 İçerik Değeri: {$asset['pitch']}\n\n"
            . "Okuyucularınızın derinlemesine faydalanabileceği bu rehberi ilgili makalenizde kaynak olarak göstermek veya ortak bir gastronomi içeriği geliştirmek isterseniz iletişime geçmekten memnuniyet duyarız.\n\n"
            . "Saygılarımızla,\n"
            . "House Chef Cyprus Yayın Kurulu\n"
            . "https://housechefcyprus.com";

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * @param array{title: string, url: string, pitch: string} $asset
     * @return array{subject: string, body: string}
     */
    private function buildEmailTemplateEn(string $host, string $topic, array $asset): array
    {
        $subject = "Culinary Resource & Authentic Cyprus Gastronomy Reference for {$host}";
        $body = "Dear {$host} Editorial Team,\n\n"
            . "We have been reading your excellent coverage on Mediterranean culinary culture and regional travel guides.\n\n"
            . "At House Chef Cyprus, our culinary research team has curated an in-depth, verified guide to authentic Cypriot gastronomy, historic recipes, and Mediterranean culinary heritage:\n\n"
            . "📌 Primary Resource: {$asset['title']}\n"
            . "🔗 Link: {$asset['url']}\n"
            . "💡 Editorial Value: {$asset['pitch']}\n\n"
            . "If you are expanding your regional food, travel, or culinary sections, our verified step-by-step guides, nutritional facts, and cultural insights could serve as a valuable reference link for your readers.\n\n"
            . "We would be delighted to collaborate or provide custom commentary from our certified chefs.\n\n"
            . "Warm regards,\n"
            . "Editorial Team | House Chef Cyprus\n"
            . "https://housechefcyprus.com";

        return ['subject' => $subject, 'body' => $body];
    }
}
