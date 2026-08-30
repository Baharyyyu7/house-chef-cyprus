<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\ZeroResultRecheck;
use Illuminate\Console\Command;

final class ZeroResultRecheckCommand extends Command
{
    protected $signature = 'search:recheck-zero-results {--json : Makine okunabilir çıktı}';

    protected $description = 'Kayıtlı sıfır sonuçlu aramaları güncel katalogla yeniden dener ve karşılananları kapatır.';

    public function handle(ZeroResultRecheck $servis, IntegrityReportLog $gunluk): int
    {
        $rapor = $servis->run();

        // Koşu kaydediliyor: tek bir sayı ancak bir öncekiyle yan yana
        // konduğunda sitenin iyileşip iyileşmediğini gösteriyor.
        $gunluk->record('search:recheck-zero-results', (string) $rapor['status'], (int) $rapor['unmet'], $rapor);

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info("{$rapor['unmet']} karşılanmamış arama · {$rapor['resolved']} kayıt kapatıldı.");

        foreach ($rapor['resolved_terms'] as $satir) {
            $this->line('  "'.$satir['term'].'" artık '.$satir['results'].' tarif dönüyor ('.$satir['search_count'].' kez arandı)');
        }

        return self::SUCCESS;
    }
}
