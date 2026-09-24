<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

/**
 * CDN arkasında gerçek ziyaretçi IP'si.
 *
 * `.env` içinde TRUSTED_PROXIES=* tanımlıydı ama hiçbir yere bağlanmamıştı.
 * Sonuç (14 Eylül 2026'da ölçüldü): Laravel her isteği CDN sunucusunun
 * adresinden gelmiş sayıyordu - son 24 saatin oturumları birkaç 172.70.x.x
 * ve 2a02:4780:… adresinde toplanmıştı. Bunun iki gizli etkisi vardı:
 *
 * 1. "IP başına" istek sınırları site geneli çalışıyordu. Kayıt denemesi
 *    saatte 5 ile sınırlı; aynı CDN adresini paylaşan bütün ziyaretçiler bu
 *    beş hakkı ortak kullanıyordu.
 * 2. IP'den ülke bulmak her ziyaretçiyi CDN sunucusunun ülkesine yazardı.
 *
 * Ayar yapılandırmadan ÇALIŞMA ANINDA okunur. bootstrap/app.php içinde env()
 * kullanmak, config:cache açıkken sessizce null döner ve bağlantı yine
 * kopardı.
 *
 * Yalnız X-Forwarded-For güvenilir. Host ve Proto bilerek dışarıda: bu
 * değişikliğin amacı IP; URL üretimi ve yönlendirmeler olduğu gibi kalır.
 *
 * Bilinen sınır: TRUSTED_PROXIES=* iken sunucuya CDN'i atlayıp doğrudan gelen
 * biri X-Forwarded-For uydurabilir. CDN adres aralıkları bilinirse ayar o
 * aralıklarla daraltılmalı (virgülle ayrılmış liste kabul edilir).
 */
final class TrustCdnProxies extends TrustProxies
{
    protected function proxies()
    {
        $deger = trim((string) config('production.trusted_proxies', ''));

        if ($deger === '') {
            return parent::proxies();
        }

        if ($deger === '*') {
            return '*';
        }

        return array_values(array_filter(array_map('trim', explode(',', $deger))));
    }

    protected function headers()
    {
        return Request::HEADER_X_FORWARDED_FOR;
    }
}
