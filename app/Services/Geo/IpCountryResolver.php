<?php

declare(strict_types=1);

namespace App\Services\Geo;

use MaxMind\Db\Reader;
use Throwable;

/**
 * IP adresinden ülke kodu.
 *
 * Yalnız ülke okunur, şehir ya da koordinat değil; veritabanı da yalnız ülke
 * içeriyor. IP bu sınıfın dışına çıkmaz, saklanmaz, loglanmaz.
 *
 * Veritabanı yoksa ya da bozuksa null döner ve sayfa etkilenmez: sayaç bir
 * kaydı kaçırır, ziyaretçi hiçbir şey fark etmez.
 *
 * final değil: testlerde gerçek veritabanı yerine sahte çözücü bağlanır.
 */
class IpCountryResolver
{
    private ?Reader $okuyucu = null;

    private bool $denendi = false;

    public function country(?string $ip): ?string
    {
        // Özel ve ayrılmış aralıklar (10.x, 127.x, 172.16-31.x, ::1) bir
        // ülkeye ait değil. Bunlar gelirse proxy arkasında gerçek IP'ye
        // ulaşılamamış demektir; tahmin yürütmek yerine boş dönülür.
        if ($ip === null || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $okuyucu = $this->okuyucu();

        if ($okuyucu === null) {
            return null;
        }

        try {
            $kayit = $okuyucu->get($ip);
        } catch (Throwable) {
            return null;
        }

        $kod = is_array($kayit) ? strtoupper((string) ($kayit['country']['iso_code'] ?? '')) : '';

        return preg_match('/^[A-Z]{2}$/', $kod) === 1 ? $kod : null;
    }

    public function available(): bool
    {
        return $this->okuyucu() !== null;
    }

    private function okuyucu(): ?Reader
    {
        if ($this->denendi) {
            return $this->okuyucu;
        }

        $this->denendi = true;
        $yol = (string) config('geoip.database');

        if ($yol === '' || ! is_file($yol)) {
            return null;
        }

        try {
            return $this->okuyucu = new Reader($yol);
        } catch (Throwable) {
            return null;
        }
    }
}
