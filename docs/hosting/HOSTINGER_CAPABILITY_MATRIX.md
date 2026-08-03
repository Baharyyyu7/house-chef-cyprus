# Hostinger Capability Matrix

Son doğrulama: 2026-08-03. Bu belge yalnız Hostinger MCP read-only sonuçlarını ve daha önce gerçek SSH oturumunda doğrulanmış runtime kanıtını kullanır. Gizli değer içermez.

| Capability | Durum | Kanıt / sınır |
|---|---|---|
| Website | AVAILABLE | `housechefcyprus.com`, etkin, ana vhost |
| Plan | AVAILABLE | Business Web Hosting (`hostinger_business_v3`) |
| Web root | AVAILABLE | `/home/u584361532/domains/housechefcyprus.com/public_html` |
| PHP | AVAILABLE | Hostinger MCP: PHP 8.4.19, OPcache açık, 1024M memory, 360 sn execution |
| Composer / Git / SSH | AVAILABLE | Önceki production release ve SSH denetimleriyle doğrulandı |
| Node/npm | NOT_AVAILABLE | Production sunucuda build yapılmaz; Vite artifact CI/yerelde üretilir |
| Scheduler | AVAILABLE | Dakikalık `schedule:run` cron mevcut |
| Queue | AVAILABLE_WITH_LIMITS | Beş dakikalık bounded worker cron mevcut; daemon kullanılmaz |
| MySQL backup | AVAILABLE | `mysqldump` + gzip + private storage akışı üretimde doğrulandı |
| DNS | AVAILABLE | Zone read-only erişimi doğrulandı |
| Mail DNS | AVAILABLE | Hostinger MX, SPF, DKIM ve DMARC kayıtları mevcut |
| CDN | AVAILABLE | Apex ve `www` Hostinger CDN hedeflerine bağlı |
| HTTP/2 / HTTP/3 advertisement | AVAILABLE | Canlı yanıt HTTP/2; CDN `alt-svc` ile HTTP/3 ilan ediyor |
| Gzip | AVAILABLE | Canlı HTML yanıtında gzip doğrulandı |
| HSTS / HTTPS | AVAILABLE | HTTPS 200 ve bir yıllık includeSubDomains HSTS doğrulandı |
| Edge cache | AVAILABLE_WITH_LIMITS | Hostinger CDN aktif; dinamik Laravel HTML yanıtı `DYNAMIC`, kör cache açılmaz |
| PHP limits/extensions | AVAILABLE | 1024M upload, 2048M post, 5000 input vars; GD/Imagick/WebP/AVIF mevcut |
| Database inventory | AVAILABLE | MySQL veritabanı 101 MB / 3072 MB; dış bağlantı kuralı yok |
| OPcache | AVAILABLE | Web ve CLI açık, 256M cache, 16,229 accelerated file limiti |
| Brotli PHP extension | NOT_AVAILABLE | Extension kapalı; CDN edge compression bağımsız değerlendirilir |
| Native command security | AVAILABLE_WITH_LIMITS | `proc_open` kullanılabilir; `exec`, `shell_exec`, `system`, PHP `symlink` kapalı |
| HTTP/3, Brotli, inode, connection limits | NOT_VERIFIED | Güvenilir ölçüm yok |

`NOT_VERIFIED`, kullanım dışı anlamına gelmez; yalnız kanıt olmadığı anlamına gelir.

Mail DNS: Hostinger MX, üç DKIM selector, SPF ve DMARC (`p=none`) doğrulandı. DMARC enforcement değişikliği teslimat gözlemi ve owner onayı olmadan yapılmaz.

Hostinger PHP `expose_php` ayarı güvenli ve geri alınabilir biçimde kapatıldı, `log_errors` açıldı; MCP tekrar okuması ve canlı yanıtta `x-powered-by` başlığının kaybolmasıyla doğrulandı. Uygulama güvenlik başlıkları, HSTS, gzip ve CDN katmanı aktif doğrulandı.
