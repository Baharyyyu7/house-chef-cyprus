<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\BackupCompletenessProbe;
use App\Services\Integrity\IntegrityReportLog;
use Illuminate\Console\Command;

final class BackupCompletenessCheck extends Command
{
    protected $signature = 'backup:completeness {backup_id?} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yedeğin tablo kümesi canlı şemayla örtüşüyor mu - eksik tablo sessiz veri kaybıdır.';

    public function handle(BackupCompletenessProbe $probe, IntegrityReportLog $gunluk): int
    {
        $rapor = $probe->run($this->argument('backup_id'));

        $gunluk->record('backup:completeness', (string) $rapor['status'], (int) $rapor['missing_tables'], $rapor);

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($rapor['status'] === 'complete') {
            $this->info('Yedek eksiksiz: canlı şemadaki her tablo dökümde var.');
        } elseif ($rapor['status'] === 'incomplete') {
            $this->warn("{$rapor['missing_tables']} tablo yedekte yok:");
            foreach ($rapor['missing'] as $tablo) {
                $this->line('  '.$tablo);
            }
        } else {
            $this->warn('Denetlenemedi: '.$rapor['status']);
        }

        // Çıkış kodu bilinçli olarak başarı: bu bir gözlem, deploy kapısı
        // değil.
        return self::SUCCESS;
    }
}
