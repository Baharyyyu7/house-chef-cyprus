<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use MaxMind\Db\Reader;
use Throwable;

/**
 * IP→ülke veritabanını indirir, doğrular ve atomik olarak değiştirir.
 *
 * Yeni dosya, bilinen bir adresi (8.8.8.8 → US) doğru çözmeden eskisinin
 * yerine geçmez. Yarım inmiş ya da bozuk dosya sayacı sessizce
 * durdururdu; bu komut ya çalışan bir dosya bırakır ya da eskisine dokunmaz.
 */
final class GeoIpUpdate extends Command
{
    protected $signature = 'geoip:update {--force : Dosya tazeyse de indir}';

    protected $description = 'DB-IP IP to Country Lite veritabanını indirir, doğrular ve yerine koyar.';

    public function handle(): int
    {
        $hedef = (string) config('geoip.database');

        if (! $this->option('force') && is_file($hedef) && filemtime($hedef) > now()->subDays(20)->getTimestamp()) {
            $this->components->info('Veritabanı taze; indirme gerekmedi.');

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($hedef));

        // Ayın ilk günlerinde yeni dosya henüz yayınlanmamış olabilir; bir
        // önceki ay denenir.
        foreach ([now(), now()->subMonthNoOverflow()] as $ay) {
            $adres = sprintf((string) config('geoip.download_url'), $ay->format('Y-m'));
            $sikistirilmis = $hedef.'.indirme.gz';
            $gecici = $hedef.'.yeni';

            try {
                $yanit = Http::timeout(180)->get($adres);
            } catch (Throwable) {
                continue;
            }

            if (! $yanit->successful() || $yanit->body() === '') {
                continue;
            }

            File::put($sikistirilmis, $yanit->body());

            $giris = @gzopen($sikistirilmis, 'rb');
            $cikis = @fopen($gecici, 'wb');

            if ($giris === false || $cikis === false) {
                @unlink($sikistirilmis);
                $this->components->error('İndirilen dosya açılamadı.');

                return self::FAILURE;
            }

            while (! gzeof($giris)) {
                fwrite($cikis, (string) gzread($giris, 1048576));
            }

            gzclose($giris);
            fclose($cikis);
            @unlink($sikistirilmis);

            if (! $this->dogrula($gecici)) {
                @unlink($gecici);
                $this->components->error('Yeni veritabanı doğrulanamadı; eski dosyaya dokunulmadı.');

                return self::FAILURE;
            }

            rename($gecici, $hedef);
            $this->components->info('Veritabanı güncellendi: '.$ay->format('Y-m'));

            return self::SUCCESS;
        }

        $this->components->error('Veritabanı indirilemedi; eski dosyaya dokunulmadı.');

        return self::FAILURE;
    }

    private function dogrula(string $yol): bool
    {
        try {
            $okuyucu = new Reader($yol);
            $kayit = $okuyucu->get('8.8.8.8');
            $okuyucu->close();
        } catch (Throwable) {
            return false;
        }

        return is_array($kayit) && ($kayit['country']['iso_code'] ?? null) === 'US';
    }
}
