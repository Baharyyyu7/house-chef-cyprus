# Visual QA Master Plan

## Test matrisi

Her teslim light/dark, 375/768/1024/1440 px, keyboard focus ve reduced-motion durumunda doğrulanır. Horizontal overflow, text wrap, image crop, focus order, overlay/modal escape, mobile drawer ve contrast her sayfa için kaydedilir.

| Akış | Zorunlu doğrulama |
| --- | --- |
| Home/header/footer | CTA, arama, theme persistence, sticky state, drawer, featured fallback |
| Recipe list/detail | filters, pagination, loading/empty/error, card, serving/favorite/cooking CTA |
| Matching/pantry/favorites | member authorization, filter/form error, empty, action feedback |
| Cooking | step/timer/pause/resume/complete, mobile full-screen davranış |
| Auth/profile | Türkçe metin, validation, verification, password, destructive action confirmation |
| Admin | admin/member policy, filters/table overflow, CRUD form error/success, detail/action feedback |
| Operations | health/backups/events/jobs/data quality için güvenli empty/error ve sensitive data mask |
| Bilgi/SEO (gelecek) | About/How It Works/FAQ/Editorial içerik doğruluğu, breadcrumb, canonical, schema görünürlüğü ve kırık-footer-link yokluğu |
| Görseller/ikonlar | hero/card crop, alt text, source/license state, dark/light contrast ve dekoratif ikon erişilebilirliği |

## QA turları

1. Faz bazlı component QA ve feature tests.
2. Visual QA turu 1: gerçek browser walkthrough; critical/P1 bulguların listesi.
3. Correction: yalnız bulgular ve regresyonlar.
4. Visual QA turu 2: aynı matrix tekrar.

## Local Phase A fixture path

`php artisan qa:seed-fixtures --reset` creates only local/testing member fixtures for authenticated walkthroughs. `scripts/qa-browser-smoke.sh` validates guest routes and fixture readiness; visual review remains a deliberate human/browser step and is never inferred from the script.
5. Full regression: PHPUnit, Pint, PHPStan, build, cache config/route/view, OpenAPI, environment, clean-install, CI.

## Kabul kriterleri

- Koyu yüzeyde AA'ya yakın okunabilir metin, border ve focus.
- Veri yokken sahte chart/metric yok; action feedback açık.
- İşlevsel route, authorization, public ID ve API sözleşmeleri bozulmaz.
- Her ana sayfa için state: normal, loading, empty, validation/error ve authorization/error değerlendirilir.
- SEO ya da bilgi sayfası route'u ancak içerik ve hedef bağlantı hazır olduğunda QA matrisine canlı sayfa olarak eklenir.

## Public Pages V3 / Part 1 kaydı

Tarif listesi ve detay için normal, boş sonuç, light/dark, keyboard focus, mobil drawer, filtre parametresi, porsiyon değişimi, gerçek placeholder/hero ve doğrulanmış içerik görünürlüğü kontrol edilir. Tarayıcı otomasyonu viewport değiştirmeyi desteklemediğinde bu genişlik matrisi responsive class ve HTTP feature testleriyle ayrıca kayıt altına alınır.

## Admin V3 / Part 1 kaydı

Dashboard; 375/768/1024/1440 px, light/dark, sidebar collapse, mobile drawer Escape/dışarı tıklama, period seçimi, gerçek/boş chart, hızlı aksiyon, dikkat alanı, keyboard focus ve member 403 akışlarıyla doğrulanır. Chart canvas yanında metinsel özet zorunludur.
# Public Pages V3 Part 2 QA

Matching filtreleri, pantry'den geçerli ürün aktarımı, expiry durumları, favorites filtreleri, light/dark tokenları ve mobil tek kolon davranışı her yayın öncesi kontrol edilir.
# Admin V3 Part 2 QA

Tarif ve malzeme V3 listeleri 375px, 768px, 1024px ve 1440px genişliklerinde; açık/koyu temada, filtre, sekme, tablonun kontrollü kaydırması ve sticky save alanı ile kontrol edilir.
# Admin Panel V3 Part 2 QA

Household Measures, Ingredient Conversions, Nutrition, recalculation history ve Data Quality yüzeyleri admin oturumunda 375px ve 1440px görünümde kontrol edilir. Member erişim sınırı Feature testlerle doğrulanır.
