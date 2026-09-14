<?php

/*
|--------------------------------------------------------------------------
| IP'den ülke
|--------------------------------------------------------------------------
|
| Ziyaretçi ülke sayacı ülkeyi önce CDN başlığından (CF-IPCountry vb.)
| okur. 12-13 Eylül 2026'da site Cloudflare'den Hostinger CDN'e geçti ve
| yeni CDN hiçbir ülke başlığı göndermediği için sayaç sessizce durdu.
| Başlık yoksa ülke, bu yerel veritabanından IP ile bulunur.
|
| IP yalnız o isteğin belleğinde kullanılır; hiçbir yere yazılmaz ve
| dışarıya gönderilmez. Kayda yine yalnız gün ve ülke kodu geçer.
|
| Veri: DB-IP "IP to Country Lite", CC BY 4.0 (https://db-ip.com). Atıf
| yönetim haritasının kaynak satırında durur. `geoip:update` her ay yeniler.
|
*/

return [
    'database' => env('GEOIP_DATABASE', storage_path('app/geoip/dbip-country-lite.mmdb')),

    // %s yerine YYYY-AA gelir. DB-IP her ayın başında yeni dosya yayınlar.
    'download_url' => env('GEOIP_DOWNLOAD_URL', 'https://download.db-ip.com/free/dbip-country-lite-%s.mmdb.gz'),
];
