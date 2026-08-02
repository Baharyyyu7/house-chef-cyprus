# Yönetim Arayüzü Route Envanteri

Bu envanter, `admin.*` adlı gerçek yönetim route'larının ortak shell, erişim, responsive yapı ve empty state sözleşmesine dahil olduğunu kaydeder. Dinamik detay, düzenleme ve alt işlem route'ları aynı parent ekranın shell sözleşmesini devralır.

| Grup | Route ailesi | URL kökü | Erişim | Ortak shell | Responsive / empty QA |
| --- | --- | --- | --- | --- | --- |
| Genel Bakış | `admin.dashboard`, `admin.product-experience.*`, `admin.release-candidate.*` | `/yonetim` | Yönetici | Ortak sidebar/topbar | Ana ekranlar denetlenir |
| Kullanıcı ve akıllı sistemler | `admin.users.*`, `admin.ai-kitchen.*`, `admin.personalization.*` | `/yonetim/kullanicilar`, `/ai-operasyonlari`, `/kisisellestirme-operasyonlari` | Yönetici | Ortak shell, metrik kartları | Tablo/empty/metric denetimi |
| İçerik yönetimi | `admin.recipes.*`, `admin.recipe-verification.*`, `admin.blog.*` | `/yonetim/tarifler`, `/icerik-dogrulama`, `/blog` | Yönetici | Ortak shell, CRUD header/form | Tablo ve form fallback |
| İçerik fabrikası | `admin.content-factory.*`, `admin.production-content.*`, `admin.editorial-operations.*` | `/yonetim/icerik-fabrikasi`, `/production-icerik`, `/editorial-operasyonlar` | Yetkili editoryal roller | Ortak shell, yatay tablo sarmalayıcı | Filtre/empty/pagination |
| Medya | `admin.media.*`, `admin.media-operations.*`, `admin.ai-image-studio.*` | `/yonetim/medya`, `/medya-operasyonlari`, `/ai-gorsel-studyosu` | Yönetici / medya yetkisi | Ortak shell | Candidate/empty/table |
| SEO | `admin.seo.*` | `/yonetim/seo` | Yönetici | Ortak shell | Sorun/fırsat empty state |
| Veri ve beslenme | `admin.ingredients.*`, `admin.measurements.*`, `admin.nutrition.*`, `admin.data-quality.*` | `/yonetim/malzemeler`, `/olculer`, `/beslenme`, `/veri-kalitesi` | Yönetici | Ortak shell, CRUD header | Form/table/detail |
| Sistem | `admin.failed-jobs.*`, `admin.backups.*`, `admin.restore-history.*`, `admin.system-events.*` | `/yonetim/basarisiz-isler`, `/yedekler`, `/restore-gecmisi`, `/sistem-olaylari` | Yönetici | Ortak shell | Güvenli empty/error states |

## Ortak sözleşme

- Desktop'ta fixed sidebar ile shell grid kolonu aynı genişliği kullanır; main alanı `min-width: 0` ile taşmayı engeller.
- Mobilde sidebar yalnız drawer olarak açılır; ana içerik tam genişlik kullanır.
- Tüm yönetim sayfaları tek `admin-shell` içinde çalışır. `hc-container`, `hc-admin-page` ve CRUD ekranları aynı maksimum içerik genişliğine hizalanır.
- Yönetim metrikleri label ve value için ayrı bloklar kullanır. Değer yoksa sentetik trend yerine açık durum metni gösterilir.
- Tablo ekranları yatay sarmalayıcı, küçük ekranlarda normal satır metni ve pagination alanı kullanır.
- Member rolü ilgili yönetim route'larına erişemez; Feature testleri temsilî kritik route'larda bunu doğrular.

## Kapsam ve QA

Static GET admin route'ları Feature testinde ortak shell üzerinden render edilir. Dinamik route'lar, aynı component/layout sözleşmesini kullanan parent ekranlarıyla birlikte; gerçek kaydı olan QA ortamında detay ve form akışlarıyla denetlenmelidir.

## GET ekran envanteri

Bu tablo, 2 Ağustos 2026 tarihli `php artisan route:list --path=yonetim --json` çıktısındaki tüm GET ekranlarını içerir. Her satır ortak `admin-shell` sözleşmesini devralır. `Parametreli` satırlarda gerçek kayıt bağlama gerektirdiği için statik render testi parent ekranını kapsar; detay/form ekranı ise aynı shell ile çalışır.

| Route adı | URL | Grup | Erişim | Mevcut düzen / boş durum | Responsive QA | Gerekli düzeltme | Durum |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `admin.dashboard` | `/yonetim` | Genel Bakış | Admin | Dashboard, veri yoksa güvenli kartlar | Tüm breakpoint | Yok | Doğrulandı |
| `admin.product-experience.index` | `/yonetim/urun-deneyimi` | Genel Bakış | Admin | Ürün deneyimi özeti | Tüm breakpoint | Yok | Doğrulandı |
| `admin.release-candidate.index` | `/yonetim/release-candidate` | Genel Bakış | Admin | Release kontrol özeti | Tüm breakpoint | Yok | Doğrulandı |
| `admin.users.index` | `/yonetim/kullanicilar` | Kullanıcılar | Admin | Kullanıcı tablosu / boş liste | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.users.show` | `/yonetim/kullanicilar/{public_id}` | Kullanıcılar | Admin | Parametreli profil detayı | Parent + shell | Yok | Shell devralır |
| `admin.ai-kitchen.index` | `/yonetim/ai-operasyonlari` | Akıllı sistemler | Admin | Ayrık metrik kartları, sağlayıcı boş durumu | Tüm breakpoint | Yok | Doğrulandı |
| `admin.personalization.index` | `/yonetim/kisisellestirme-operasyonlari` | Akıllı sistemler | Admin | Gizlilik sınırlı metrik özeti | Tüm breakpoint | Yok | Doğrulandı |
| `admin.recipes.index` | `/yonetim/tarifler` | İçerik | Admin | Tarif listesi / boş liste | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.recipes.create` | `/yonetim/tarifler/create` | İçerik | Admin | Tarif formu | Form akışı | Yok | Shell devralır |
| `admin.recipes.edit` | `/yonetim/tarifler/{recipe}/edit` | İçerik | Admin | Parametreli tarif formu | Form akışı | Yok | Shell devralır |
| `admin.recipes.edit-public` | `/yonetim/tarifler/public/{public_id}/duzenle` | İçerik | Admin | Public ID ile düzenleme | Form akışı | Yok | Shell devralır |
| `admin.recipes.ingredients.index` | `/yonetim/tarifler/{recipe}/malzemeler` | İçerik | Admin | Malzeme tablosu | Tablo sarmalayıcı | Yok | Shell devralır |
| `admin.recipes.ingredients.index-public` | `/yonetim/tarifler/public/{public_id}/malzemeler` | İçerik | Admin | Public ID malzeme ekranı | Tablo sarmalayıcı | Yok | Shell devralır |
| `admin.recipes.steps.index` | `/yonetim/tarifler/{recipe}/adimlar` | İçerik | Admin | Adım listesi | Tablo sarmalayıcı | Yok | Shell devralır |
| `admin.recipes.steps.create` | `/yonetim/tarifler/{recipe}/adimlar/ekle` | İçerik | Admin | Adım formu | Form akışı | Yok | Shell devralır |
| `admin.recipes.steps.edit` | `/yonetim/tarifler/{recipe}/adimlar/{step}/duzenle` | İçerik | Admin | Parametreli adım formu | Form akışı | Yok | Shell devralır |
| `admin.recipe-verification.index` | `/yonetim/icerik-dogrulama` | İçerik | Admin | Doğrulama kuyruğu | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.recipe-verification.show` | `/yonetim/icerik-dogrulama/{public_id}` | İçerik | Admin | Tarif doğrulama detayı | Parent + shell | Yok | Shell devralır |
| `admin.blog.index` | `/yonetim/blog` | İçerik | Admin | Blog listesi / boş liste | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.blog.create` | `/yonetim/blog/create` | İçerik | Admin | Blog oluşturma formu | Form akışı | Yok | Shell devralır |
| `admin.blog.edit` | `/yonetim/blog/{post}/edit` | İçerik | Admin | Parametreli blog formu | Form akışı | Yok | Shell devralır |
| `admin.content-factory.index` | `/yonetim/icerik-fabrikasi` | İçerik Fabrikası | Yetkili editoryal | Kartlar, filtreler, geniş tablolarda scroll | Tüm breakpoint | Yok | Doğrulandı |
| `admin.content-factory.fact-checks.index` | `/yonetim/icerik-fabrikasi/fact-check` | İçerik Fabrikası | Yetkili editoryal | Fact-check listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.content-factory.fact-checks.show` | `/yonetim/icerik-fabrikasi/fact-check/{issue}` | İçerik Fabrikası | Yetkili editoryal | Issue detayı | Parent + shell | Yok | Shell devralır |
| `admin.content-factory.duplicate-groups.index` | `/yonetim/icerik-fabrikasi/duplicate-groups` | İçerik Fabrikası | Yetkili editoryal | Duplicate grup listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.content-factory.duplicate-groups.show` | `/yonetim/icerik-fabrikasi/duplicate-groups/{groupKey}` | İçerik Fabrikası | Yetkili editoryal | Grup detayı | Parent + shell | Yok | Shell devralır |
| `admin.content-factory.readiness.index` | `/yonetim/icerik-fabrikasi/readiness` | İçerik Fabrikası | Yetkili editoryal | Batch readiness listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.content-factory.readiness.show` | `/yonetim/icerik-fabrikasi/readiness/{batch}` | İçerik Fabrikası | Yetkili editoryal | Batch detayı | Parent + shell | Yok | Shell devralır |
| `admin.content-factory.reviews.show` | `/yonetim/icerik-fabrikasi/review/{review}` | İçerik Fabrikası | Yetkili editoryal | Review detayı | Parent + shell | Yok | Shell devralır |
| `admin.production-content.index` | `/yonetim/production-icerik` | İçerik Fabrikası | Admin | Production içerik listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.editorial-operations.index` | `/yonetim/editorial-operasyonlar` | İçerik Fabrikası | Yetkili editoryal | Operasyon özeti | Tüm breakpoint | Yok | Doğrulandı |
| `admin.editorial-operations.batches.index` | `/yonetim/editorial-operasyonlar/batchler` | İçerik Fabrikası | Yetkili editoryal | Batch listesi | Tablo sarmalayıcı | Yok | Shell devralır |
| `admin.editorial-operations.batches.show` | `/yonetim/editorial-operasyonlar/batchler/{batch}` | İçerik Fabrikası | Yetkili editoryal | Batch detayı | Parent + shell | Yok | Shell devralır |
| `admin.editorial-operations.final-approvals` | `/yonetim/editorial-operasyonlar/final-onay` | İçerik Fabrikası | Yetkili editoryal | Final onay listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.editorial-operations.sources.index` | `/yonetim/editorial-operasyonlar/kaynak-kuyrugu` | İçerik Fabrikası | Yetkili editoryal | Kaynak kuyruğu | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.editorial-operations.sources.show` | `/yonetim/editorial-operasyonlar/kaynak-kuyrugu/{item}` | İçerik Fabrikası | Yetkili editoryal | Kaynak detayı | Parent + shell | Yok | Shell devralır |
| `admin.editorial-operations.media` | `/yonetim/editorial-operasyonlar/medya` | İçerik Fabrikası | Yetkili editoryal | Medya operasyon listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.editorial-operations.packs.index` | `/yonetim/editorial-operasyonlar/review-paketleri` | İçerik Fabrikası | Yetkili editoryal | Review paketleri | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.editorial-operations.packs.show` | `/yonetim/editorial-operasyonlar/review-paketleri/{pack}` | İçerik Fabrikası | Yetkili editoryal | Paket detayı | Parent + shell | Yok | Shell devralır |
| `admin.editorial-operations.workload` | `/yonetim/editorial-operasyonlar/reviewer-is-yuku` | İçerik Fabrikası | Yetkili editoryal | İş yükü özeti | Tüm breakpoint | Yok | Doğrulandı |
| `admin.media.index` | `/yonetim/medya` | Medya | Admin | Medya listesi / boş durum | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.media.create` | `/yonetim/medya/yukle` | Medya | Admin | Güvenli yükleme formu | Form akışı | Yok | Shell devralır |
| `admin.media.show` | `/yonetim/medya/{publicId}` | Medya | Admin | Public ID medya detayı | Parent + shell | Yok | Shell devralır |
| `admin.media-operations.index` | `/yonetim/medya-operasyonlari` | Medya | Admin | Aday/operasyon listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.ai-image-studio.index` | `/yonetim/ai-gorsel-studyosu` | Medya | Admin | Görsel aday stüdyosu | Tüm breakpoint | Yok | Doğrulandı |
| `admin.ai-image-studio.show` | `/yonetim/ai-gorsel-studyosu/{generation}` | Medya | Admin | Üretim detayı | Parent + shell | Yok | Shell devralır |
| `admin.ai-image-studio.preview` | `/yonetim/ai-gorsel-studyosu/{generation}/onizleme` | Medya | Admin | Güvenli önizleme | Parent + shell | Yok | Shell devralır |
| `admin.seo.index` | `/yonetim/seo` | SEO | Admin | Teknik SEO özeti | Tüm breakpoint | Yok | Doğrulandı |
| `admin.seo.issues` | `/yonetim/seo/sorunlar` | SEO | Admin | Sorun listesi / boş durum | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.seo.opportunities` | `/yonetim/seo/firsatlar` | SEO | Admin | Fırsat listesi / boş durum | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.ingredients.index` | `/yonetim/malzemeler` | Malzeme verisi | Admin | Malzeme listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.ingredients.edit` | `/yonetim/malzemeler/{ingredient}/duzenle` | Malzeme verisi | Admin | Malzeme formu | Form akışı | Yok | Shell devralır |
| `admin.ingredients.edit-public` | `/yonetim/malzemeler/public/{publicId}/duzenle` | Malzeme verisi | Admin | Public ID malzeme formu | Form akışı | Yok | Shell devralır |
| `admin.measurements.units` | `/yonetim/olculer` | Malzeme verisi | Admin | Ölçü listesi/formu | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.measurements.conversions` | `/yonetim/malzeme-donusumleri` | Malzeme verisi | Admin | Dönüşüm listesi/formu | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.nutrition.index` | `/yonetim/beslenme` | Beslenme | Admin | Beslenme merkezi; boş lookup fallback | Tüm breakpoint | Yok | Doğrulandı |
| `admin.nutrition.history` | `/yonetim/beslenme/gecmis` | Beslenme | Admin | Hesaplama geçmişi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.nutrition.snapshots.show` | `/yonetim/beslenme/snapshots/{snapshot}` | Beslenme | Admin | Snapshot detayı | Parent + shell | Yok | Shell devralır |
| `admin.data-quality.index` | `/yonetim/veri-kalitesi` | Beslenme / kalite | Admin | Kalite skoru ve issue listesi | Tüm breakpoint | Yok | Doğrulandı |
| `admin.data-quality.show` | `/yonetim/veri-kalitesi/{issue}` | Beslenme / kalite | Admin | Issue detayı | Parent + shell | Yok | Shell devralır |
| `admin.failed-jobs.index` | `/yonetim/basarisiz-isler` | Sistem | Admin | Failed jobs listesi / boş durum | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.failed-jobs.show` | `/yonetim/basarisiz-isler/{uuid}` | Sistem | Admin | Güvenli job detayı | Parent + shell | Yok | Shell devralır |
| `admin.backups.index` | `/yonetim/yedekler` | Sistem | Admin | Yedek listesi / boş durum | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.restore-history.index` | `/yonetim/restore-gecmisi` | Sistem | Admin | Restore geçmişi / boş durum | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.restore-history.show` | `/yonetim/restore-gecmisi/{restoreHistory}` | Sistem | Admin | Restore detayı | Parent + shell | Yok | Shell devralır |
| `admin.system-events.index` | `/yonetim/sistem-olaylari` | Sistem | Admin | Sistem olayları listesi | Tablo sarmalayıcı | Yok | Doğrulandı |
| `admin.system-events.show` | `/yonetim/sistem-olaylari/{systemEvent}` | Sistem | Admin | Güvenli event detayı | Parent + shell | Yok | Shell devralır |

POST, PUT/PATCH ve DELETE route'ları bu ekranların aksiyon endpoint'leridir; yeni bir layout üretmezler. Parametreli aksiyonların kimliği public identifier veya framework binding ile çözülür; bu envanterdeki ilgili parent ekran altında authorization ve CSRF sözleşmesini devralırlar.

## Görsel QA kanıtı

Yerel, yetkili yönetici oturumunda 320, 375, 430, 768, 1024, 1280, 1440 ve 1920 piksel genişliklerinde temsilî kritik rota setinde shell taşması ve tablo sarmalayıcısı kontrol edildi. Drawer ile açık/koyu tema ayrıca doğrulandı. Temsilî kanıtlar:

- `docs/screenshots/admin-redesign/ai-operations-desktop.png`
- `docs/screenshots/admin-redesign/personalization-desktop.png`
- `docs/screenshots/admin-redesign/content-factory-mobile.png`
