<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\MediaGapReport;
use Illuminate\Console\Command;

/**
 * Görsel üretimi bekleyen tarifleri öncelik sırasıyla listeler.
 *
 * Çıktı doğrudan görsel üreten ajana (Codex, Antigravity) verilebilecek
 * bir görev sırası. Rastgele üretmek yerine önce göze batanı düzeltmek.
 */
final class MediaGapQueue extends Command
{
    protected $signature = 'media:gap-queue {--limit=25 : Listelenecek tarif sayısı} {--json : Makine okunabilir çıktı}';

    protected $description = 'Onaylı görseli olmayan yayındaki tarifleri öncelik sırasıyla listeler.';

    public function handle(MediaGapReport $rapor): int
    {
        $sonuc = $rapor->build((int) $this->option('limit'));

        if ($this->option('json')) {
            $this->line((string) json_encode($sonuc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($sonuc['status'] === 'complete') {
            $this->info('Yayındaki tariflerin hepsinin onaylı görseli var.');

            return self::SUCCESS;
        }

        $this->warn("{$sonuc['missing_media']}/{$sonuc['published_recipes']} yayındaki tarifin onaylı görseli yok.");
        $this->newLine();

        foreach ($sonuc['queue'] as $sira => $satir) {
            $this->line(sprintf(
                '%2d. [%3d] %-42s %s',
                $sira + 1,
                $satir['score'],
                mb_substr($satir['title'], 0, 42),
                $satir['reason']
            ));
        }

        return self::SUCCESS;
    }
}
