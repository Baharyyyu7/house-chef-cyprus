# V1.1 Operasyon ve Stabilizasyon Kaydı

Bu kayıt 3 Ağustos 2026 tarihinde alınan gerçek ve sınırlı kanıtı ayırır. Public HTTP gözlemi, üretim SSH/queue/scheduler/mail/backup kanıtı yerine geçmez.

| ID | Modül | Production kanıtı | Şiddet | Sahip | Uygulama | Test | Deploy | Public doğrulama | Blokaj | Kesin sonraki adım |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| V11-001 | Release baseline | Local ve `origin/visual-redesign-v2`: `2aa2a7efc5238d5db446e86f044bc1ce753e586c` eşit | High | Release owner | CODE_COMPLETE | Git reconciliation tamam | UNKNOWN | Public aktif SHA kanıtlanmadı | BLOCKED_EXTERNAL | SSH ile `readlink -f "$HOME/domains/housechefcyprus.com/current"` ve rollback hedefini oku. |
| V11-002 | Public HTTP | `/`, `/tarifler`, `/blog`, `/login`, `/robots.txt`, `/sitemap.xml`, `/health`, `/api/v1/health` 200 döndü | Medium | Operations | IN_PROGRESS | Salt-okunur HTTP smoke | TESTED | Endpoint erişimi doğrulandı | — | Browser QA ve içerik/asset URL örneklemesi yap. |
| V11-003 | Application health | 2026-08-03T19:57:23Z `/health` ve API health `degraded` döndü | **High** | Production operations | DISCOVERED | Public sözleşme ayrıntı sızdırmıyor | UNKNOWN | Gerçek degraded durum doğrulandı | BLOCKED_EXTERNAL | Yetkili production oturumunda `php artisan app:health --json`, database/queue/scheduler/backup health komutlarını çalıştır; yalnız güvenli sınıflandırılmış çıktıyı incele. |
| V11-004 | Transport security | HTTPS, HSTS, `nosniff`, `SAMEORIGIN`, strict referrer policy ve permissions policy public header’larda görüldü | Low | Operations | CODE_COMPLETE | Header gözlemi | DEPLOYED | TESTED | CSP yalnız `upgrade-insecure-requests`; tam CSP politika kararı ayrı değerlendirme ister | CSP ekleme öncesi tüm third-party kaynakları envanterle. |
| V11-005 | Queue / scheduler | Public endpoint bu sistemler için freshness kanıtı vermez | High | Production operations | CODE_COMPLETE | Yerel komutlar mevcut | UNKNOWN | NOT_AVAILABLE | BLOCKED_EXTERNAL | Hostinger cron ve queue çalıştırıcısı timestamps/failed jobs ile doğrulansın. |
| V11-006 | Mail | Mailer/delivery kanıtı public endpointten çıkarılamaz | High | SMTP owner | CODE_COMPLETE | Health komutu mevcut | UNKNOWN | NOT_AVAILABLE | BLOCKED_SMTP | Tek kontrollü alıcıyla `app:mail-health --no-send --json`, sonra onaylı tek send-test çalıştır. |
| V11-007 | Media reliability | Public sayfalar erişilebilir; tüm görsel URL/variant/MIME denetimi çalıştırılmadı | High | Editorial + media reviewer | CODE_COMPLETE | Denetim komutları mevcut | UNKNOWN | INSUFFICIENT_DATA | BLOCKED_EDITORIAL | Recipe/blog media completion auditlerini productionda probe ile çalıştır; yalnız approved gerçek/sentetik media durumlarını kabul et. |
| V11-008 | Content quality | İnsan kimlik, ölçü, kaynak ve fact-check kanıtı yok | High | Editorial / fact-check | IN_PROGRESS | Private review altyapısı mevcut | UNKNOWN | NOT_AVAILABLE | BLOCKED_EDITORIAL | Yayınlanmış içerikleri private review batch’lerinde insan onayıyla ilerlet. |
| V11-009 | SEO | `robots.txt` erişilebilir; sitemap endpoint 200 ve 4 `loc` etiketi döndü | Medium | SEO owner | CODE_COMPLETE | Public HTTP okuması | TESTED | Kapsam/URL kalitesi doğrulanmadı | BLOCKED_EXTERNAL | `seo:sitemap-audit --json`, Search Console ve canonical örneklemesi yetkili ortamda çalıştırılsın. |
| V11-010 | Member/admin acceptance | Gerçek browser, kullanıcı yetkisi ve e-posta akışı çalıştırılmadı | High | QA owner | CODE_COMPLETE | Feature test kapsamı mevcut; local PHP uyumsuz | UNKNOWN | NOT_AVAILABLE | BLOCKED_EXTERNAL | Kontrollü hesaplarla gerçek browser QA; gerekli kullanıcılar iş sonunda silinsin/anonymize edilsin. |
| V11-011 | Backup / rollback | Public HTTP kanıtı backup veya rollback kanıtı değildir | High | Production operations | CODE_COMPLETE | Script syntax ve private-manifest altyapısı mevcut | UNKNOWN | NOT_AVAILABLE | BLOCKED_EXTERNAL | Taze pre-change backup + checksum verify; isolated testing restore-test; non-destructive rollback dry-run. |
| V11-012 | Local quality tooling | Secret, forbidden-file, docs-link ve NPM high audit geçti; production build geçti | Medium | Engineering | TESTED | Bash deploy/rollback syntax geçti | ARTIFACT_READY | Yerel PHP 8.4.1/Composer yok | BLOCKED_EXTERNAL | PHP 8.4.1 ortamında PHPUnit, Pint, PHPStan, Artisan denetimlerini çalıştır. |

## Bilinen yerel sınırlar

- Bu bilgisayarda `php` ve `composer` PATH üzerinde bulunmuyor; bulunan XAMPP PHP 8.2.4, proje bağımlılıklarının istediği PHP 8.4.1 sürümünden düşük.
- GitHub Actions ve Hostinger SSH/cron/mail araçları bu oturumda doğrulanmış, çağrılabilir yetkili kanal sağlamıyor.
- Bu yüzden V1.1 deploy, release tag ve production-verified iddiası yapılmamıştır.

## Öncelik sırası

1. **V11-003:** Degraded health kök nedenini güvenli production health çıktısıyla belirle ve yalnız gerçek hatayı düzelt.
2. **V11-005 / V11-011:** Queue, scheduler ve backup freshness/verify kanıtını üret.
3. **V11-007 / V11-008:** Public media ve yayınlanmış içerik için insan onaylı gerçek kanıtı tamamla.
4. **V11-010:** Gerçek public/member/admin browser kabulünü çalıştır.
