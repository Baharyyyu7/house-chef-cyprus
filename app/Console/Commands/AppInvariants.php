<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\IntegrityReportLog;
use App\Services\Integrity\SiteInvariants;
use Illuminate\Console\Command;

/**
 * Üretim verisinde bir daha olmaması gereken şeyleri her gün ölçer.
 *
 * Test paketi kodu koruyor; bu komut veriyi koruyor. 20-22 Eylül 2026'da
 * bulunan kusurların hiçbiri testleri kırmamıştı: yönlendirmeler yanlış
 * hedefe yığıldı, muhafız günlüğüne günde 9.899 satır yazıldı, kapalı
 * sayfalara 5.207 çeviri işi açıldı. Hepsi ancak biri elle bakınca
 * görüldü.
 *
 * Burada bulunan her ihlal, gerçekten yaşanmış bir kusurun tekrarıdır.
 * Komut ihlal bulursa sıfırdan farklı çıkar; toplu bir veri işleminden
 * sonra elle çalıştırmak da bu yüzden anlamlı.
 */
final class AppInvariants extends Command
{
    protected $signature = 'app:invariants {--json : Makine okunabilir çıktı}';

    protected $description = 'Üretim verisinde bir daha olmaması gereken durumları ölçer.';

    public function handle(SiteInvariants $kurallar, IntegrityReportLog $log): int
    {
        $ihlaller = $kurallar->ihlaller();

        $log->record(
            'app:invariants',
            $ihlaller === [] ? 'ok' : 'attention',
            count($ihlaller),
            ['violations' => $ihlaller]
        );

        if ($this->option('json')) {
            $this->line((string) json_encode(['violations' => $ihlaller], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return $ihlaller === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($ihlaller === []) {
            $this->components->info('Bütün kurallar sağlanıyor.');

            return self::SUCCESS;
        }

        $this->components->error(count($ihlaller).' kural ihlal edildi.');

        foreach ($ihlaller as $ihlal) {
            $this->line('  · '.$ihlal['kural'].' ('.$ihlal['ihlal'].'): '.$ihlal['mesaj']);
        }

        return self::FAILURE;
    }
}
