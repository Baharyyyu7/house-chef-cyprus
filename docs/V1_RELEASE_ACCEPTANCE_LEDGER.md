# V1 Release Candidate Kabul Kaydı

Bu belge yalnız kanıtlanan durumu kaydeder. Yerel kaynak denetimi, canlı production kanıtı veya gerçek tarayıcı/e-posta kanıtı yerine geçmez.

| Kabul ID | Modül | Gereksinim | Mevcut kanıt | Yerel durum | Test durumu | Deployment / production | Blokaj | Sonraki güvenli adım |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| RC-001 | Kaynak | `visual-redesign-v2` local/origin SHA eşitliği | `5b2a840ba8a684367297ca9b40cc8489bc320530` iki tarafta aynı | CODE_COMPLETE | `git diff --check` geçti | BLOCKED_PRODUCTION_ACCESS | Aktif/rollback SHA için bu oturumda doğrulanmış SSH yok | Sunucuda `readlink -f "$HOME/domains/housechefcyprus.com/current"` çalıştır. |
| RC-002 | Kaynak temizliği | İzlenen dosyalarda hata/secret olmaması | Secret, forbidden-file ve docs-link taramaları geçti | TESTED | Araç çıktıları mevcut | ARTIFACT_READY | Kullanıcıya ait `.playwright-cli/`, `build-artifacts/`, `output/` untracked dizinleri korunuyor | Sahipliği belirlenince dry-run cleanup yap. |
| RC-003 | Public içerik | Tarif/blog kimliği, editoryal doğruluk ve medya | Yayın kapıları, medya tamamlama denetimleri ve private review altyapısı mevcut | CODE_COMPLETE | Yerel PHP çalışma zamanı uyumsuz | BLOCKED_EDITORIAL | Her yayımlanmış kayıt için insan kimlik/fact-check/media onayı yok | Editör review kuyruklarını gerçek kaynak ve medya ile tamamla. |
| RC-004 | Public medya | Public dosya, variant, MIME, HTTP ve ilişki doğrulaması | Recipe/blog media audit komutları mevcut | CODE_COMPLETE | Yerel PHP çalışma zamanı uyumsuz | BLOCKED_PRODUCTION_ACCESS | Public URL probe ve gerçek dosya manifesti çalıştırılmadı | Production'da `recipe-media:completion-audit --probe-public --json` ve blog eşleniğini çalıştır. |
| RC-005 | Üye platformu | Auth, doğrulama, reset, profil, koleksiyon, gizlilik akışları | Feature test kapsamı ve verification resend rate-limit mevcut | CODE_COMPLETE | PHP >= 8.4.1 eksik | BLOCKED_PRODUCTION_ACCESS | Gerçek browser/SMTP akışı çalıştırılmadı | Kontrollü member ile gerçek browser QA ve tekil mail testleri yap. |
| RC-006 | Yönetim | Server-side admin Gate, member 403, audit | Central `access-admin` Gate ve rota-gate denetimi eklendi | CODE_COMPLETE | Testler eklendi; PHP >= 8.4.1 eksik | BLOCKED_PRODUCTION_ACCESS | Canlı admin/member izin kanıtı yok | Kontrollü admin/member oturumlarıyla production 403/işlem QA yap. |
| RC-007 | Mail / queue / scheduler | Gerçek teslim, worker ve cron freshness | Safe health komutları ile dashboard sinyalleri mevcut | CODE_COMPLETE | Statik denetim geçti | BLOCKED_EXTERNAL | SMTP teslimi, queue worker ve Hostinger cron timestamp kanıtı yok | SMTP/cron sahibi erişimiyle `app:mail-health --no-send --json`, queue/scheduler health çalıştır. |
| RC-008 | Backup / rollback | Taze yedek, checksum, isolated restore-test, rollback rehearsal | MySQL backup, manifest ve rollback scriptleri mevcut; bash syntax geçti | CODE_COMPLETE | Bash syntax geçti; PHP testleri çalışma zamanı eksik | BLOCKED_PRODUCTION_ACCESS | Taze production yedek/restore-test ve rollback rehearsal kanıtı yok | Production'da yedek oluştur/verify, yalnız testing hedefinde restore-test, rollback dry-run çalıştır. |
| RC-009 | Güvenlik | HTTPS/cookie/CSP/secret/public-file kontrolü | Secret ve forbidden-file scan geçti; Gate/CSRF/rate-limit kodu mevcut | IN_PROGRESS | NPM high audit: 0 vulnerability | BLOCKED_PRODUCTION_ACCESS | HTTP header ve gerçek production env doğrulanmadı | Canlı HTTPS/header/strict environment check uygula. |
| RC-010 | SEO | Canonical, sitemap, image sitemap ve URL sağlık | SEO guard/audit ve Search Console dosyası kaynakta mevcut | CODE_COMPLETE | Docs-link scan geçti | BLOCKED_PRODUCTION_ACCESS | Canlı sitemap, canonical ve Google doğrulaması kanıtı yok | Production'da `seo:sitemap-audit --json` ve URL smoke çalıştır. |
| RC-011 | Performans / erişilebilirlik | Gerçek ölçüm ve browser QA | UX/performance/a11y audit komutları kaynakta mevcut | IN_PROGRESS | Production build geçti | BLOCKED_PRODUCTION_ACCESS | Lighthouse/RUM ve gerçek browser viewport kanıtı yok | Gerçek browserda 320–1920px, light/dark QA ve ölçüm yap. |
| RC-012 | Release | Immutable artifact, deploy, canlı kabul, tag | Hostinger release/artifact scriptleri kaynakta mevcut | ARTIFACT_READY | Deploy/rollback bash syntax geçti | BLOCKED_PRODUCTION_ACCESS | Final artifact, SHA sunucu doğrulaması, deploy ve canlı smoke yapılmadı | Tüm blockerlar çözüldükten sonra immutable artifact ile deploy et; yalnız aktif SHA kanıtlanırsa tag oluştur. |

## Yerel çalışma zamanı sınırı

Bu oturumdaki sistem `php`/`composer` komutlarını sağlamıyor; bulunan XAMPP PHP 8.2.4 ise bağımlılıkların zorunlu tuttuğu PHP 8.4.1 sürümünün altında. Bu nedenle PHPUnit, Pint, PHPStan ve Artisan tabanlı kabul komutları bu makinede **çalıştırılmadı**. Bu durum test başarısızlığı değildir; ancak RC kabulü için gerçek bir blocker’dır.

## Güvenli ve mevcut doğrulama komutları

Production erişimi yetkili kişi tarafından sağlandığında, sırayla ve secret göstermeden:

```bash
php artisan app:environment-check --environment=production --strict --json
php artisan app:database-readiness --json
php artisan content:production-audit --json
php artisan recipe-media:completion-audit --probe-public --json
php artisan blog-media:completion-audit --probe-public --json
php artisan seo:sitemap-audit --json
php artisan app:queue-health --json
php artisan app:scheduler-health --json
php artisan backup:health --json
```

Bu komutlardan hiçbirinin çıktısı, gerçek insan editör/fact-check onayı veya canlı browser kabulünün yerine geçmez.
