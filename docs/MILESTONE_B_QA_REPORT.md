# Milestone B QA Report

Bu kayıt local QA ortamında 1 Ağustos 2026 tarihinde alınmıştır. Production ortamı, yayın onayı veya insan editör kararını temsil etmez.

## Veri ve içerik denetimi

- `migrate:fresh --seed` ve ikinci `db:seed` başarıyla tamamlandı; seed işlemi tekrar çalıştırıldığında katalog çoğalmadı.
- Katalog: 500 tarif, 50 yayınlanmış ve doğrulanmış tarif, 200 blog yazısı, 20 yayınlanmış blog yazısı.
- `content:production-audit --json`: 630 kayıt teknik olarak bloklu, `ready_for_human_review=0`; otomatik yayın kapalı ve insan onayı zorunlu.
- `content:fact-check-triage --json`: 2.340 konu sınıflandırıldı (900 critical, 1.080 high, 180 medium, 180 low). Hiçbiri otomatik çözülmedi veya yayınlanmadı.
- `content:fact-check-morning-brief --json`: 18 tarif ve 18 blog batch'i bloklu; insan incelemesi gerektiren gerçek durum açıkça korunuyor.

## Uygulama ve tarayıcı QA

- `app:production-language-audit --json`: public/admin UI kapsamındaki bulgu sayısı 0.
- Tarayıcı QA: `/yonetim/production-icerik`, `/gorsel-ve-medya-politikasi` ve `/tarifler` açıldı.
- İçerik merkezi 375, 768, 1024 ve 1440 px genişlikte doğrulandı; her görünümde doküman genişliği viewport ile aynı kaldı, yatay taşma görülmedi.
- Kamusal medya politikası canonical URL ile, tarif listesi ise eski demo dili olmadan render edildi.

## Kalite kapısı

- PHPUnit: 278 test, 277 başarılı, 1 güvenli skip; 1.342 assertion.
- Pint: başarılı.
- PHPStan: başarılı, 0 hata.
- Vite production build: başarılı.
- OpenAPI kontrolü: 16 path, eksik path yok.
- Environment kontrolü: local hedefte tüm zorunlu kontroller başarılı.
- Composer audit ve production bağımlılık npm audit: güvenlik bulgusu yok.
- Secret scan, forbidden-file scan ve docs link check: başarılı.

Bu kontroller otomatik yayın veya insan doğrulamasını temsil etmez. CI sonucu, commit push edildikten sonra GitHub Actions üzerinde ayrıca doğrulanır.
