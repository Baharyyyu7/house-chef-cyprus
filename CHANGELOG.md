# Changelog

## Unreleased

- V1.0 Release Candidate operasyon paketi: VPS/Nginx, queue/scheduler, TLS/Cloudflare, mail, monitoring, post-deploy smoke, rollback, production security ve dürüst data/media/SEO/browser QA raporları eklendi. Kod otomatik deploy, DNS değişikliği, ücretli provider çağrısı veya onaysız medya yayını yapmaz.

- Unsplash için insan onaylı, hotlink tabanlı ikincil editoryal aday sağlayıcısı; görünür attribution, UTM, güvenli host doğrulaması ve tekil download tracking eklendi.

- Real Content & SEO catalogue foundation: production seed no longer creates public demo-labelled records; it now seeds the 50-recipe editorial catalogue plus 20 original, reviewed Turkish blog guides. Media truthfulness is explicit: current branded fallbacks are not counted as verified food photography and remain replacement-required until licensed editorial assets are approved.

- SEO, Blog & Content Automation: merkezi metadata, keyword/brief/fırsat/issue/revision kayıtları; people-first yayın kalite kapısı; idempotent teknik audit; anonim zero-result fırsatları; admin SEO operasyon alanı ve güvenli provider/scheduler komutları eklendi. Search Console, IndexNow ve X dağıtımı varsayılan olarak kapalıdır; otomatik public yayın yapılmaz.

- V1.0 RC iç kapanış: Pexels güvenli hata sözleşmesi, private review görünürlüğü, sitemap index/alt sitemap’ler, recipe/article/FAQ structured data ve production image/SEO release belgeleri eklendi. Geçersiz Pexels anahtarı nedeniyle toplu sorgu/indirme yapılmadı; branded fallback zinciri korundu.

- Master Fix Phase B2: BFL FLUX.2 async polling ve Stability Stable Image Core multipart provider adapterleri, iki çağrılık private comparison bütçesi, güvenli provider health ve response validation sözleşmeleri eklendi. BFL/Stability anahtarları yokken hiçbir canlı çağrı yapılmadı.

- Master Fix Phase B1: Gemini image response parser, güvenli provider telemetry, diagnostic recovery scope ve model/endpoint uyumsuzluğu için tek çağrılık kontrol eklendi. HTTP 404 recovery sonucu nedeniyle otomatik retry veya public AI medya yapılmadı.

- Master Fix Phase B: 8 pilot tarifin özgün editoryal veri uyumsuzlukları provenance korunarak düzeltildi; nutrition, allergen, dietary, matching ve Data Quality sonuçları yeniden hesaplandı.
- Master Fix Phase B: 10 tarif / 20 varyasyonluk Gemini Imagen pilotu bütçe ve varyasyon hard-stop ile denendi. Sağlayıcı kullanılabilir görsel payload'ı döndürmedi; otomatik retry, auto-approve ve public yayın yapılmadı. Private-before-public review hattı, güvenli hata kaydı ve public medya görünürlük koruması eklendi.

- Admin Panel V3 Part 2: household ölçü birimleri, ingredient dönüşümleri, nutrition, nutrient, allergen, dietary threshold ve Data Quality yönetim yüzeyleri ortak V3 table/form primitives ile yenilendi.
- Yönetim filtreleri, güvenli detail tabloları, mobile controlled-scroll davranışı ve admin-only Feature testleri genişletildi.

## Admin Panel V3 Part 2 — In progress

- Tarif, doğrulama, adım ve malzeme yönetim ekranları ortak V3 table/form sözleşmesine taşındı.
- Filtreli recipe/ingredient listeleri, sekmeli düzenleme alanları ve responsive sticky save bar eklendi.

## Unreleased

- Added the V3 semantic design token, shared primitive, motion and SVG icon foundation on `visual-redesign-v2`.
- Localized the profile form pilot and applied V3 pilot surfaces to favorites and the admin dashboard.
- Added the Phase 3 editorial content trust model, completeness/publication guard, admin verification queue and idempotent first-50 recipe catalogue.
- Added provenance-safe public recipe summaries, branded placeholder variants and initial ingredient icon metadata pilot.
- Closed Master Redesign Faz 3 documentation at 50 published/verified editorial recipes; Faz 3A/3B/3C intermediate targets are superseded and the controlled 150-recipe expansion remains deferred.
- Defined Public Pages V3 Redesign as the next active scope without starting its implementation.
- Completed Public Pages V3 / Part 1: verified recipe catalogue filters, V3 reusable recipe cards, deterministic media placeholders, detail hero, household serving controls, trust/source summaries, conditional equipment/tips/storage panels and related recipes.
- Added Admin Panel V3 / Part 1: responsive admin shell, grouped navigation, real cached dashboard analytics, Chart.js visualisations, system summary, safe activity/attention panels and reduced-motion support.

## Master Product Redesign — Faz 0 Planı

- Tüm public/member/admin route ve ekran envanteri; recipe measurement/content, admin analytics, motion ve visual QA açıkları belgelendi.
- Uygulama kodu değiştirilmeden V3 tasarım sistemi, household measurement, 50 tarif editorial programı, public/member UX ve admin analytics için bağımlılıklı faz sırası tanımlandı.

## Visual Redesign V2 Preview

- Ana sayfa ile ortak public header/footer, warm ivory–terracotta–charcoal tasarım diliyle yeniden ele alındı.
- Özgün H/C monogram logo ve favikon; AI ile proje için üretilmiş, telifsiz özgün yemek hero görseli eklendi.
- V2 şu an yalnızca `visual-redesign-v2` preview branch’indedir; diğer ürün ekranlarının görsel kapsamı ana sayfa onayına kadar korunur.
- QA correction: `html.dark` tabanlı semantic tema değişkenleri, koyu mod kontrastı, tarif listesi empty state’i, seçilmiş tarif fallback’i, kart etkileşimleri ve reduced-motion polish’i tamamlandı.

## Visual Design Phase 1

- Koyu forest, cream ve gold token'ları ile merkezi House Chef Cyprus tasarım sistemi eklendi.
- Özgün SVG logo/favikon, public ve admin navigasyonu, mobil drawer, tema desteği ve erişilebilir odak stilleri uygulandı.
- Ana sayfa, tarifler, detay, eşleştirme, pantry, favoriler, hesap ve kimlik doğrulama ekranları responsive olarak yenilendi.

## Final Product Core Audit

- Livewire/Alpine CSP regresyonu kapatıldı; lokal gerçek giriş akışı yeniden çalışır durumda doğrulandı.
- Kullanıcıya özel Favorites listesi, görünür pantry düzenleme ve tarif görünürlüğü korumaları tamamlandı.
- System Events listesi gerçek detay ekranına bağlandı; kullanılmayan Favorite modeli kaldırıldı.
- Ürün çekirdeği kontrollü olarak Visual Design aşamasına hazır kabul edildi.

## Foundation E — Final Audit

- API v1 success/error envelope, 16 endpoint OpenAPI 3.1 coverage ve sözleşme kontrol komutu tamamlandı.
- CI kalite kapısı; iki aşamalı seed, cache denetimi, audit/tarama ve local clean-install smoke testiyle genişletildi.
- Environment validation, yerel süreç scriptleri, governance şablonları ve taşınabilirlik denetimi eklendi.

## Foundation A–D

- Recipe/Nutrition/Data Quality, cache/queue/scheduler/performance, security/media ve observability/backup/restore altyapıları tamamlandı.
# Unreleased

- Added recipe ingredient group management with public identifiers, transactional move/reorder controls, fallback-safe deletion and audit entries.
- Completed the pantry household measurement selector, legacy-unit handling and canonical conversion status presentation.

- Added Turkish household measurement taxonomy, ingredient-specific conversion records, canonical/display recipe ingredient fields and measurement administration routes.
- Seeded the 12 demo recipes with household presentation values and explicitly low-confidence demo conversions.
- Completed household integration gaps: admin recipe ingredient management, public household pilot, pantry canonical values, quantity sufficiency matching, recipe ingredient public identifiers and conversion-driven nutrition invalidation.
# Public Pages V3 Part 2

- Matching, pantry ve favorites için V3 public/member arayüzü; açıklanabilir eşleştirme, expiry bilgisi ve filtre sözleşmeleri eklendi.
# Public Pages V3 Part 3

- Mobile-first Cooking Mode ve ortak V3 auth surface uygulandı.
# Master Fix Phase A (geliştirme)

- Admin koyu tema kontrastı semantik V3 tokenlarıyla güçlendirildi.
- Data Quality V2 medya, ölçüm, nutrition ve yerel QA güven sinyallerini açıklanabilir kategori skorlarına bağladı.
- Yerel QA fixture ve tarif görsel denetim komutları eklendi.
