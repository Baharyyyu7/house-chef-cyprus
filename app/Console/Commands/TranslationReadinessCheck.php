<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\TranslationReadiness;
use App\Services\Integrity\IntegrityReportLog;
use Illuminate\Console\Command;

/**
 * Çeviriye hazır tarifleri listeler.
 *
 * Türkçe tarafı eksik bir tarifi çevirmek hatayı dille çarpar; kuyruğa
 * yalnız kendi dilinde tam olanlar girer.
 */
final class TranslationReadinessCheck extends Command
{
    protected $signature = 'content:translation-readiness {--limit=40} {--json}';

    protected $description = 'Çeviriye hazır tarifleri ve hazır olmayanların engellerini listeler.';

    public function handle(TranslationReadiness $servis, IntegrityReportLog $gunluk): int
    {
        $rapor = $servis->report((int) $this->option('limit'));

        // Koşu kaydediliyor: tek bir sayı ancak bir öncekiyle yan yana
        // konduğunda sitenin iyileşip iyileşmediğini gösteriyor.
        $gunluk->record('content:translation-readiness', (string) $rapor['status'], isset($rapor['blocked']) ? (int) $rapor['blocked'] : null, $rapor);

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info("Yayındaki {$rapor['published']} tarifin {$rapor['ready']} tanesi çeviriye hazır.");

        if ($rapor['blocked'] > 0) {
            $this->newLine();
            $this->warn("{$rapor['blocked']} tarif hazır değil:");

            foreach ($rapor['blockers'] as $engel => $adet) {
                $this->line("  {$engel}: {$adet}");
            }
        }

        return self::SUCCESS;
    }
}
