# PHP-FPM yapılandırması

Ubuntu 24.04 üzerinde PHP sürümü proje `composer.json` gereksinimiyle uyumlu olmalıdır (PHP 8.3+). Web FPM havuzu ile queue worker süreçleri ayrıdır.

- `memory_limit`, upload ve timeout değerlerini VPS RAM/CPU ve yükleme politikasına göre belirleyin; uygulama kodunda sabit yüksek değer kullanmayın.
- `upload_max_filesize` ve `post_max_size`, `MEDIA_MAX_UPLOAD_BYTES` değerinden küçük olmamalıdır.
- OPcache production'da açık, `validate_timestamps=0` olmalı; deploy sonrası PHP-FPM kontrollü yeniden yüklenmelidir.
- `pm=dynamic` veya `ondemand` seçimi trafik profilinden sonra yapılmalı; web havuzu queue worker sayısıyla paylaşılmamalıdır.
- `realpath_cache_size` ve `realpath_cache_ttl` deploy frekansına göre ayarlanmalıdır.

Önerilen denetim: `php -v`, `php-fpm8.3 -tt`, Nginx `fastcgi_pass` soket eşleşmesi ve post-deploy `/health`.
