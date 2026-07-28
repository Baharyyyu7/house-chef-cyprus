# Security Regression Audit

## Doğrulanan kontroller

- Admin rotaları `auth`, `verified`, `can:access-admin` zinciri altında.
- Member 403, cooking ownership/IDOR, admin CRUD/admin media/API internal-id davranışlarına dair mevcut Feature testleri var.
- API throttle, AI throttle, registration throttle; CSRF middleware ve password flow route’ları mevcut.
- CSP, `nosniff`, referrer, permissions, frame, COOP/CORP başlıkları yerel yanıtta gözlendi.
- Composer/npm production dependency audit’lerinde bilinen açık yok; Pint/PHPStan başarılı.

## Bulgular

- **AUD-016 P3:** Yerel HTTP yanıtta `X-Powered-By` görünür. Production hardening konfigürasyonunda kaldırıldığı ayrıca doğrulanmalı.
- Admin form payload’larında integer model binding mevcuttur; bu public/API response leak değildir fakat management protocol sınırı dokümante edilmelidir.
- Gerçek member browser oturumu ve production header/CORS/CDN testi bu auditin dışındadır.

Bu belge penetrasyon testi veya saldırı rehberi değildir.
