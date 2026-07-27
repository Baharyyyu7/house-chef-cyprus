# Master Product Redesign, Recipe Data & Admin Program

## Amaç ve sınırlar

Bu program, House Chef Cyprus'ı güvenilir tarif verisi ve güçlü yönetim deneyimiyle tamamlamayı hedefler. Çalışma dalı `visual-redesign-v2` olarak kalır; bu planın kabulü olmadan `main` merge'i, tag, AI Chef, Blog/SEO, Community, Monetization veya Import Engine V1.5 çalışması yapılmaz.

Mevcut Laravel/Blade/Livewire/Alpine/Tailwind mimarisi korunur. Ayrı SPA veya ağır frontend framework eklenmez. Public ID, yetkilendirme, API v1, queue/scheduler, güvenlik ve Foundation A–E sözleşmeleri geriye uyumlu kalır.

## Faz sırası

| Faz | Amaç | Başlıca dosya/alan | Migration | Test ve QA | Risk / bağımlılık | Kapanış kriteri |
| --- | --- | --- | --- | --- | --- | --- |
| 0 | Envanter, veri ve UX açıklarını doğrulamak | Bu plan ve alt strateji belgeleri | Yok | Doküman link denetimi | Düşük; mevcut route/model incelemesi | Kullanıcı planı onayı |
| 1 | Global Design V3, motion, form, table, icon sözleşmesi | `resources/css/app.css`, layoutlar, yeniden kullanılabilir Blade bileşenleri | Yok | Light/dark, keyboard, 375–1440 QA | Orta; mevcut Alpine/Livewire ile uyum | Token, focus, reduced-motion ve bileşen testleri tamam |
| 2 | Recipe domain ve household measurement modeli | Unit, ingredient conversion/density, recipe ingredient migrationları, `UnitConversionService` | Evet, geriye uyumlu | Dönüşüm/precision/uncertainty/nutrition/matching testleri | Yüksek; canonical hesap koruması | Household gösterim + canonical hesap güvenli |
| 3 | Data Trust, provenance ve verification | Editorial review state, source/license/check history | Evet, geriye uyumlu | Trust/data-quality testleri | Yüksek; veri modelinin temeli | Tarif/ingredient/media güven sinyali izlenebilir |
| 4 | İlk 50 doğrulanmış tarif | Editorial seed, kaynak notu, quality checklist | Yalnız gerekli editorial alanlar | Idempotent seed + quality scan | Orta; telif/lisans doğrulaması | 50 özgün, kaynaklı, tutarlı tarif |
| 5 | Tarif görselleri ve ingredient icon sistemi | Media manifest, focal point, SVG icon planı | Gerekirse metadata | License/accessibility QA | Orta | İlk 20 tarifte kontrollü görsel kalite |
| 6 | Public sayfaları kontrollü teslimlerle yenilemek | Tarif listesi/detayı, pantry/favorites | Yok veya presentation metadata | Sayfa bazlı feature + browser QA | Orta; Faz 1–5'e bağlı | Light/dark/mobile/empty/error/loading QA |
| 7 | Matching, Cooking ve Profile/Auth UX | Picker, cooking, profile/Volt görünümü | Gerekirse preference alanları | Matching/cooking/auth regresyonu | Orta | Türkçe, erişilebilir UX |
| 8 | Admin shell, dashboard, grafikler ve CRUD redesign | Admin layout/dashboard/recipe/ingredient/nutrition/operations | Aggregate/trust alanları gerekirse | Policy/table/form/chart QA | Yüksek | Gerçek veri veya dürüst empty state |
| 9 | About, How It Works, FAQ ve Editorial pages | Yeni bilgi mimarisi/modülleri | Evet, yalnız gerektiğinde | Content/FAQ structured data QA | Orta | Gerçek metin ve çalışan route'lar |
| 10 | Blog/SEO teknik temeli | Sitemap, metadata, canonical, structured data | Evet | SEO/link/schema QA | Orta | Editorial onaylı yayın altyapısı |
| 11 | Blog içerik otomasyonu | Brief/review/schedule workflow | Evet | Approval/audit/quality QA | Yüksek | Otomatik yayın yok, editör onayı zorunlu |
| 12 | Dünya mutfakları genişlemesi | Cuisine taxonomy ve editorial katalog | Evet | Taxonomy/provenance QA | Orta | Hiyerarşi ve verification açık |
| 13 | Responsive ve accessibility hardening | Tüm public/member/admin ekranlar | Yok | 375/768/1024/1440, keyboard, contrast | Orta | WCAG AA'ya yakın kritik akışlar |
| 14 | Visual QA turu 1 | Browser senaryoları ve screenshot matrisi | Yok | Gerçek browser QA | Düşük | Bulgu listesi önceliklendirilir |
| 15 | Düzeltme turu | Yalnız QA bulguları | Gerekirse küçük migration | Regresyon | Düşük/orta | Kritik/P1 bulgu kalmaz |
| 16 | Visual QA turu 2 | Aynı QA matrisi | Yok | Gerçek browser QA | Düşük | Onay paketi hazırlanır |
| 17 | Full regression QA | Tüm kalite kapıları | Yok | Test/Pint/PHPStan/build/CI/clean install | Orta | Kullanıcı release onayı |
| 18 | Production readiness | Environment, monitoring, release/rollback checklist | Ortama bağlı | Staging/health/restore QA | Orta | Production onayı |

## Faz 1 — ayrıntılı uygulama planı

### V3 foundation implementation note

Faz 1, `visual-redesign-v2` üzerinde semantic tokenlar, shared Blade primitive'leri, reduced-motion sözleşmesi ve üç düşük-riskli pilotla uygulanır. Bu aşama, household/data içerik migration'ı veya sayfa bazlı geniş redesign başlatmaz.

### Amaç

V3 için tek kaynaklı token, motion, form, table ve icon sözleşmesi kurmak; hiç bir ürün işlevini değiştirmeden sonraki sayfa teslimlerini güvenli ve tutarlı hâle getirmek.

### İş paketleri

1. **Design token audit:** `forest`, `olive`, `sage`, yeşil hex değerler, eski serif başlıklar ve Blade içine gömülü renk/spacing tanımları envanterlenir. `--hc-*` tokenları light/dark için background, surface, elevated surface, accent, semantic durum, text, border, focus, shadow, radius, spacing, typography ve z-index kapsamına genişletilir.
2. **Motion contract:** fast/normal/slow süreler, enter/exit/spring easing, page/reveal/card/button/toast/modal/drawer/tab/filter/skeleton/chart hareket sınıfları oluşturulur. `prefers-reduced-motion` her biri için ilk sınıf davranıştır.
3. **Blade form primitives:** text, number, search, date/time, select, multi-select, checkbox/radio/switch, textarea, file upload, validation/help/disabled/loading durumları için mevcut bileşenler genişletilir veya uyumlu yeni `x-ui.*` bileşenleri kurulur.
4. **Table primitives:** filter toolbar, pagination, sort, empty/loading, row action, controlled overflow ve mobile card fallback için tekrar kullanılabilir yapı kurulur.
5. **Icon decision:** emoji/metin sembolü yerine tek stroke temelli, lisansı uygun hafif SVG icon seti seçilir; decorative ikonlar `aria-hidden`, aksiyon ikonları label taşır.
6. **Pilot:** yalnız bir public ve bir admin olmayan düşük riskli ekranla token/motion/form/table pilotu yapılır; kullanıcı onayı sonrası genişletilir.

### Faz 1 dosya sınırı

`resources/css/app.css`, `tailwind.config.js`, `resources/views/components/*`, `resources/views/layouts/*`, V3 dokümantasyonu, odaklı visual feature testleri. Recipe domain, route/controller/service, API ve auth akışı değiştirilmez.

### Faz 1 kalite kapısı

- Light/dark token geçişinde flash veya düşük kontrast yoktur.
- Klavye focus, form hata/success ve mobile drawer çalışır.
- Reduced-motion altında içerik beklemez veya görünmez kalmaz.
- PHPUnit, Pint, PHPStan, production build, route/view/config cache ve CI geçer.
- Kullanıcı pilot ekranı onaylamadan Faz 2 veya sayfa redesign'ına geçilmez.

## Önceliklendirilmiş backlog

### P0 — programı başlatmak için

- V3 semantic token ve icon sözleşmesi.
- Household conversion/density veri tasarımı; canonical hesapların korunması.
- 12 demo tarifin tekrar eden metin, zorunlu alan ve provenance kalite denetimi.
- Gerçek görsel varlık stratejisi; aynı placeholder'ın tekrar kullanımını bitirme planı.

### P1 — data trust ve ana kullanıcı deneyimi

- Tarif listesi ve detay redesign'i.
- Matching, pantry, favorites ve cooking UX.
- Türkçe profile/auth formları.
- Data Trust/verification ve ilk 50 doğrulanmış editorial tarif.
- Tarif image manifesti ve ingredient icon sözleşmesi.

### P2 — yönetim verimliliği

- Admin dashboard aggregate grafikleri.
- Tarif wizard, ingredient 360° detay, household conversion yönetimi.
- Data Quality bulk işlemleri ve yönetim table/form standardı.

### P3 — release polish

- Visual QA turları, contrast/accessibility, screenshot regression yaklaşımı.
- Trend/metric history bulunuyorsa gerçek grafikler; yoksa açık empty state.
- About/FAQ/Editoryal sayfalar, Blog/SEO teknik temeli ve content workflow yalnız ilgili veri/UX fazları kapandıktan sonra.

## Çıkış kriterleri

Her faz yalnız kendi test/QA/CI kapısından sonra kapanır. Ölçü dönüşümü, recipe content, görsel asset ve admin analytics işleri birbirinin yerine tamamlanmış sayılmaz. Production, mobil, AI ve SEO programın dış bağımlılıkları olarak tutulur.
# Phase 2 status

The V3 redesign now has a measurement-aware recipe data foundation. Household display and canonical nutrition-safe quantities are additive and do not redesign the remaining public screens.
