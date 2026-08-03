# Yönetim Operasyonları Tamamlama Kaydı

Bu kayıt, House Chef Cyprus'un mevcut kamu sitesi için güvenli yönetim kapsamını izler. Metrikler yalnız veritabanındaki aggregate kayıtlar ve açıkça ölçülebilen operasyon sinyallerinden üretilir; sağlık, e-posta teslimi, yedek veya production doğrulaması varsayılmaz.

| ID | Modül | Mevcut durum | Uygulama | Test | Production doğrulaması | Blokaj / sonraki güvenli adım |
| --- | --- | --- | --- | --- | --- | --- |
| AOC-001 | Yönetim erişimi | `yonetim` rotaları `auth`, `verified`, `can:access-admin` ile korunur. | CODE_COMPLETE | Mevcut admin/member feature testleri | BLOCKED_PRODUCTION_ACCESS | Gerçek member ve admin oturumlarıyla canlı 403/izin QA yapılmalı. |
| AOC-002 | Operasyon özeti | Tarif, blog, üye, iletişim, kuyruk, scheduler, yedek ve restore sinyalleri tek dashboardda aggregate olarak görünür. | CODE_COMPLETE | Dashboard aggregate ve mahremiyet testi | ARTIFACT_READY | Production release aktif SHA ve gerçek sinyaller SSH ile okunmalı. |
| AOC-003 | İçerik yayın kapıları | Tarif/blog yayın ve medya inceleme rotaları yönetim korumasında; belirsiz içerik otomatik yayınlanmaz. | CODE_COMPLETE | Mevcut doğrulama/publikasyon testleri | BLOCKED_EDITORIAL | İnsan editör medya, kaynak ve yüksek riskli iddiaları onaylamalı. |
| AOC-004 | İletişim talepleri | Talepler yalnız admin rotalarında yönetilir; dashboard yalnız sayı gösterir. | CODE_COMPLETE | Admin dashboard mahremiyet testi | BLOCKED_PRODUCTION_ACCESS | SMTP teslimi ve gerçek istek akışı ayrı canlı test gerektirir. |
| AOC-005 | Mail operasyonları | Yapılandırma/readiness durumu ve queue sayıları güvenli biçimde izlenir. | CODE_COMPLETE | Mail operations feature testleri | BLOCKED_EXTERNAL | SMTP, SPF/DKIM/DMARC ve teslim raporları sunucuda doğrulanmalı. |
| AOC-006 | Queue/scheduler | Snapshot ve yenileme aksiyonları admin ile sınırlıdır. | CODE_COMPLETE | Mevcut operasyon testleri | BLOCKED_PRODUCTION_ACCESS | Hostinger cron/queue çalıştırma zamanları SSH ile doğrulanmalı. |
| AOC-007 | Backup/restore | Özel disk yedek manifesti, doğrulama ve restore-test geçmişi mevcut; production restore otomatik değildir. | CODE_COMPLETE | Backup service testleri | BLOCKED_PRODUCTION_ACCESS | Son yedek ve yalnız test veritabanında restore-test sonucu sunucuda doğrulanmalı. |
| AOC-008 | Yayın/incident | Sistem olayları ve audit kayıtları admin arayüzünde bounded olarak sunulur. | CODE_COMPLETE | Mevcut system-event/audit testleri | BLOCKED_PRODUCTION_ACCESS | Aktif release, rollback release ve maintenance durumu SSH ile okunmalı. |

## Güvenlik sınırları

- Dashboard, kişi e-postası, iletişim metni, IP, parola, token veya bağlantı sırrı göstermez.
- Sayılar bounded değildir; kayıt listeleri bounded ve yetkilidir.
- Üretim sağlığı yalnız gerçek snapshot/manifest/veri geldiğinde gösterilir. Yerel geliştirme verisi production kanıtı değildir.
- Yedek geri yükleme, veri silme ve yüksek riskli yayın kararları insan yetkisi ve ayrı onay gerektirir.
