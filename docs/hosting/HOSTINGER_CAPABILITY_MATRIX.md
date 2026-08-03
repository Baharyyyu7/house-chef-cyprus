# Hostinger Capability Matrix

Son doğrulama: 2026-08-03. Bu belge yalnız Hostinger MCP read-only sonuçlarını ve daha önce gerçek SSH oturumunda doğrulanmış runtime kanıtını kullanır. Gizli değer içermez.

| Capability | Durum | Kanıt / sınır |
|---|---|---|
| Website | AVAILABLE | `housechefcyprus.com`, etkin, ana vhost |
| Plan | AVAILABLE | Business Web Hosting (`hostinger_business_v3`) |
| Web root | AVAILABLE | `/home/u584361532/domains/housechefcyprus.com/public_html` |
| PHP | AVAILABLE | Gerçek SSH denetiminde PHP 8.4; PHP-details MCP çağrısı bu denetimde 500 verdi |
| Composer / Git / SSH | AVAILABLE | Önceki production release ve SSH denetimleriyle doğrulandı |
| Node/npm | NOT_AVAILABLE | Production sunucuda build yapılmaz; Vite artifact CI/yerelde üretilir |
| Scheduler | AVAILABLE | Dakikalık `schedule:run` cron mevcut |
| Queue | AVAILABLE_WITH_LIMITS | Beş dakikalık bounded worker cron mevcut; daemon kullanılmaz |
| MySQL backup | AVAILABLE | `mysqldump` + gzip + private storage akışı üretimde doğrulandı |
| DNS | AVAILABLE | Zone read-only erişimi doğrulandı |
| Mail DNS | AVAILABLE | Hostinger MX, SPF, DKIM ve DMARC kayıtları mevcut |
| CDN | AVAILABLE | Apex ve `www` Hostinger CDN hedeflerine bağlı |
| PHP limits/extensions | NOT_VERIFIED | PHP-details MCP endpoint bu turda 500 verdi; tahmin edilmedi |
| Database inventory | NOT_VERIFIED | MCP filtre doğrulaması başarısız oldu; secret veya isim raporlanmadı |
| HTTP/3, Brotli, inode, connection limits | NOT_VERIFIED | Güvenilir ölçüm yok |

`NOT_VERIFIED`, kullanım dışı anlamına gelmez; yalnız kanıt olmadığı anlamına gelir.
