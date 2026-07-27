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
5. Full regression: PHPUnit, Pint, PHPStan, build, cache config/route/view, OpenAPI, environment, clean-install, CI.

## Kabul kriterleri

- Koyu yüzeyde AA'ya yakın okunabilir metin, border ve focus.
- Veri yokken sahte chart/metric yok; action feedback açık.
- İşlevsel route, authorization, public ID ve API sözleşmeleri bozulmaz.
- Her ana sayfa için state: normal, loading, empty, validation/error ve authorization/error değerlendirilir.
- SEO ya da bilgi sayfası route'u ancak içerik ve hedef bağlantı hazır olduğunda QA matrisine canlı sayfa olarak eklenir.
