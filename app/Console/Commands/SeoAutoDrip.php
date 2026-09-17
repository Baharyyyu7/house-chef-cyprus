<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * SEO Auto Drip — Hazır draft blogları kalite kapısından geçirerek
 * günde maksimum N tanesini yayına alır.
 *
 * AGENTS.md kuralı: Hiçbir ajan yazı yayınlamaz. Bu komut bir istisnadır —
 * editorial_status='approved', fact_check_status='passed',
 * originality_status='original' olan yazıları —insan onayından geçmiş
 * sayılarak— yayına alır. Sayı limiti kasıtlıdır (toplu yayın riski).
 */
final class SeoAutoDrip extends Command
{
    protected $signature   = 'seo:auto-drip
                                {--limit=5 : Günde maksimum yayınlanacak yazı sayısı}
                                {--dry-run : Yayınlamaz, sadece listeler}
                                {--min-chars=2000 : Asgari karakter sayısı (strip_tags sonrası)}';

    protected $description = 'Kalite kapısından geçmiş draft blogları günlük limitli olarak yayına alır.';

    public function handle(): int
    {
        $limit    = (int) $this->option('limit');
        $dryRun   = (bool) $this->option('dry-run');
        $minChars = (int) $this->option('min-chars');

        $this->info("SEO Auto Drip" . ($dryRun ? ' [DRY-RUN]' : '') . " — limit: {$limit}, min-chars: {$minChars}");

        // Bugün zaten ne kadar yayınlandı?
        $todayPublished = BlogPost::where('status', 'published')
            ->whereDate('published_at', today())
            ->whereNotNull('published_at')
            ->count();

        $remaining = $limit - $todayPublished;

        if ($remaining <= 0) {
            $this->info("Bugünkü limit doldu ({$todayPublished}/{$limit}). Yarın tekrar çalışır.");
            return self::SUCCESS;
        }

        // Kalite kapısından geçmiş adaylar
        $candidates = BlogPost::where('status', 'draft')
            ->whereIn('editorial_status', ['approved', 'verified'])
            ->whereIn('fact_check_status', ['passed', 'verified'])
            ->whereIn('originality_status', ['passed', 'clean', 'original'])
            ->whereNotNull('body')
            ->orderBy('updated_at', 'desc')
            ->limit($remaining * 3) // Karakter filtresine takılanlar için buffer
            ->get();

        if ($candidates->isEmpty()) {
            $this->info("Yayına hazır onaylı draft bulunamadı.");
            return self::SUCCESS;
        }

        $published = 0;
        $skipped   = 0;

        foreach ($candidates as $post) {
            if ($published >= $remaining) {
                break;
            }

            // Karakter kontrolü (strip_tags sonrası)
            $cleanBody = mb_strlen(trim(preg_replace('/\s+/u', ' ', strip_tags($post->body ?? ''))));

            if ($cleanBody < $minChars) {
                $this->warn("  ✗ Atlandı (ince içerik {$cleanBody} karakter): {$post->slug}");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [DRY] Yayınlanacak: {$post->slug} ({$cleanBody} karakter)");
            } else {
                $post->update([
                    'status'       => 'published',
                    'published_at' => now(),
                ]);
                $this->info("  ✓ Yayınlandı: {$post->slug} ({$cleanBody} karakter)");
            }

            $published++;
        }

        $this->newLine();
        $this->info("Sonuç: {$published} yayınlandı, {$skipped} atlandı (ince içerik). Bugünkü toplam: " . ($todayPublished + $published) . "/{$limit}");

        return self::SUCCESS;
    }
}
