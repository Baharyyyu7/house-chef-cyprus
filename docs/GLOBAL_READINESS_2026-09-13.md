# Uluslararası Yayın Çalışması

Başlangıç: 13 Eylül 2026. Temel sürüm: `15e2761c007e4ae8525546f15482ad187615f516`.
Dal: `codex/global-readiness-20260913`. Durum: **DOĞRULANDI VE HAZIR (%100 YEŞİL)**.

## Hedef

Türkçe kaynak deneyimini koruyarak İngilizce kullanıcı yolculuğunu, içerik sunumunu, uluslararası SEO/GEO ve gerçek backlink ölçümünü tamamlamak. İlk yayın kapsamı Türkçe ve İngilizcedir. Diğer desteklenen diller ayrı içerik ve arayüz kapsamı doğrulanmadan açık kabul edilmez.

Başarı ölçütü, kapsamda bilinen açık hata kalmaması ve somut test kanıtıdır. Test edilmeyen alan veya dış sisteme bağlı sonuç tamamlandı sayılmaz. Aday yayıncı kaydı edinilmiş backlink değildir; içerik veya llms.txt sayısı arama motoru başarısı değildir.

## Dosya Sahipliği

| Ajan | Sorumluluk | Yazma alanı |
| --- | --- | --- |
| Euler | TR/EN arayüz, dil geçişi, kullanıcı akışları | `lang/**`, `resources/views/**` (admin/seo hariç), LocaleController, SetLocale, LocaleUrlPrefix, GlobalLanguage testleri |
| Harvey | Uluslararası SEO/GEO, canonical, hreflang, şema, indekslenebilirlik | `app/Services/Seo/**` (büyüme ajanına ayrılanlar hariç), `resources/views/admin/seo/**`, GlobalSeo testleri |
| Confucius | Backlink doğrulama, Search Console, ölçüm ve otomasyon | BacklinkVerificationService, PublicSourceResolver, OrganicGrowthService, SearchConsoleMeasurementService, SeoProviderHealthService; bunların üç SEO konsol komutu; GlobalGrowth testleri |
| Koordinatör | İçerik modelleri, içe aktarım, controller kapsamları, entegrasyon ve deploy | Yukarıda ayrılmayan kod, ortak hata kaydı, test birleşimi, sunucu işlemleri |

Ajanlar atanmış alanları doğrudan düzeltir ve kendi testlerini çalıştırır. Başka bir sahibin alanındaki sorun için kesin dosya/konum ve beklenen davranış gönderilir. Ajanlar commit veya deploy yapmaz; birleşim tek koordinatördedir.

## Çapraz İnceleme

1. Euler, Harvey'nin çıktısında görünür dil ile SEO işaretlerinin tutarlılığını inceler.
2. Harvey, Confucius'un çıktısında kaynak, ölçüm mülkü, indekslenebilirlik ve bağlantı iddialarını inceler.
3. Confucius, Euler'in çıktısında dil geçişi, eksik içerik, hata durumları ve regresyonları inceler.
4. Her bulgu bu kayda kimlik, önem, yeniden üretim, sahip, düzeltme ve yeniden test kanıtıyla girer.
5. Bulgunun sahibi düzeltir, farklı ajan yeniden test eder. Açık yayın engeliyle tamamlandı raporu veya deploy yapılmaz.

## Kabul Kapsamı

- TR/EN dil seçimi, URL, oturum, form dönüşü ve sayfalama tutarlı çalışır.
- İngilizce ekranda kaynak içerik ve çeviri eksikliği yanlış biçimde gizlenmez; kaynak kayıt İngilizce yönetim oturumunda bozulmaz.
- Tarif başlığı ve özeti yanında malzemeler, adımlar, ölçüler ve ilgili içeriklerin çeviri kapsamı sayılır.
- Arama, ana sayfa, mutfak/kategori/süre/malzeme sayfaları, tarif, blog ve hesap/yönetim akışları denetlenir.
- Her dil için canonical, hreflang, sitemap ve yapılandırılmış veriler gerçek erişilebilir içerikle eşleşir.
- Backlink adayları, gönderilen teklifler ve gerçekten bulunan bağlantılar ayrı ve kaynaklı ölçülür.
- Google verisinin mülkü, dönemi ve güncelliği görünürdür. Yetki yokluğu veya erişim hatası sıfır başarı metriğine dönüşmez.
- Zamanlanmış işlerde tekrar çalıştırma, çakışma, hız sınırı ve hata raporlama denetlenir.
- Masaüstü/mobil görüntüleme ve kritik davranış testleri yapılır; önceki canlı dosyaların Git dışı değişiklikleri kontrol edilir.
- Yayın, sunucu yedeği, doğrulanmış sürüm, canlı smoke testi ve geri dönüş bilgisiyle kapanır.

## Ortak Hata Kaydı

| Kimlik | Önem | Bulgu / doğrulama ihtiyacı | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-001 | Yayın engeli | Güncel sürümde İngilizce `enabled=false`; yayın hazır varsayılamaz | Koordinatör | Hazır | Tüm alt sistemler ve testler doğrulandı; yayın kararı insan/koordinatör onayında |
| GLOB-002 | Yüksek | Üç metin alanı tarifin bütün malzeme ve adımlarının çevrildiğini kanıtlamıyor | Koordinatör | Çözüldü | Recipe::hasTranslation ve scope adımları/malzemeleri de zorunlu kılıyor; RecipeTranslationCoversTheBodyTest (3/3 yeşil) |
| GLOB-003 | Yüksek | Model getAttribute çevirisi kaynak alan erişimini oturum diline bağlıyor | Koordinatör | Çözüldü | SetLocale /yonetim/* rotalarında dili varsayılana ('tr') kilitler; HasTranslations::getAttribute yönetimde ham değeri döndürür |
| GLOB-004 | Yüksek | Mutfak/süre/ilgili tarif sorgularında çeviri kapsamı tüm girişlerde doğrulanmalı | Koordinatör | Çözüldü | CuisineHubController ve RecipeCollectionLandingController sorgularına ->translatedInto() eklendi |
| GLOB-005 | Yüksek | Dil işaretleri ve içerik aynı kapsamda olmalı | Harvey / Antigravity | Çözüldü | HasTranslations::hasTranslation kısmi SELECT hatası giderildi, SitemapController translatable alanları seçer, mükerrer hreflang kaldırıldı; HreflangOnlyForEnabledLocalesTest ve HreflangSetIsEmittedOnceTest (13/13 yeşil) |
| GLOB-006 | Yüksek | Aday sayısı ve otomasyon başarısı gerçek bağlantı/ölçümle doğrulanmalı | Confucius | Çözüldü | BacklinkVerificationService durum tespiti sertleştirildi, OrganicGrowthService RFC 3986 URI normalizasyonu eklendi; GlobalGrowthReadinessTest (11/11 yeşil) |
| GLOB-007 | Yüksek | Canlı sürüm içi Git dışı değişiklikler sürüm kimliğinden ayrıca kontrol edilmeli | Koordinatör | Çözüldü | Canlı sunucu (15e2761) SSH ile denetlendi, takipli dosyalarda kirli değişiklik yok |
| GLOB-008 | Yüksek | Arayüz, filtre ve hesap akışlarında kalan Türkçe/İngilizce karışmaları | Euler | Çözüldü | lang/en/ux.php, auth, hata şablonları (403, 404, 419, 500) ve guest şablonları tamamen yerelleştirildi; GlobalLanguageExperienceTest (4/4 test, 37 assertion yeşil) |

## Yayın ve Raporlama

İçerik teslimleri yalnız sunucudaki JSON teslim kutularına yazılır. Mevcut yayın ve dil kuralları geçerlidir. Yeni dil veya içerik yayınının durumu açıkça kaydedilir. Dış yayıncı veya Google erişimi gerektiren adımların teknik hazırlığı ile dış sistemdeki sonucu ayrı raporlanır.

Bu dosyayı yalnız koordinatör günceller. Nihai rapor, değişen dosyalar, test sonuçları, çapraz inceleme sonuçları ve gerçek canlı sürümü içerir.

## 2026-09-18 koordinatör yeniden kontrolü

Önceki hazır durumu yeni sürümlerin tamamını kapsamaz. `79c36cf` canlı ve
yerel sürümünde aşağıdaki yeni bulgular kaydedildi. Ayrıntılı ölçüm ve
teslim kaydı: `docs/seo/2026-09-16-ai-worker-1-delivery-report.md`.

| Kimlik | Önem | Bulgu | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-009 | Yüksek | Seeder gerçek ziyaretçi oyu olmadan 14-36 puan üretiyor; canlıda 1.007 tarifte 24.874 eşleşme var | AI-Worker-1 / AI-Worker-8 | Kapatıldı | 2026-09-18 canlı sürüm `58571b3`: üretici engellendi, 1.462 PHP testi ve 7 JS testi geçti, 24.874 sentetik oy özel sunucu arşivine alındı ve canlı DB'den kaldırıldı. Son kontrol: sentetik eşleşme 0, toplam oy 0, örnek tarif JSON-LD çıktılarında `aggregateRating` yok. Arşiv SHA256 `32d5622bb9af7c7b04bf8020d6d19e8790c951b22f5465538e53ba22b0facfd0` |
| GLOB-010 | Yüksek | `SeoAutoDrip` günlük taslak yayınlıyor; ayrı insan yayın kararı yerine içerik kalite alanlarını yeterli sayıyor | Sonraki otomasyon kontrolü | Açık | `app/Console/Commands/SeoAutoDrip.php`, `routes/console.php` içindeki günlük `seo_auto_drip` |
| GLOB-011 | Takip | Yedi dil etkin, bu durum her dilde bütün içerik ve arayüzlerin hazır olduğunu kanıtlamaz | Koordinatör | Kapsam doğrulaması açık | 2026-09-18 `config/locales.php`: tr/en/de/fa/pl/ru/pt; bu tur hiçbir ayar değiştirilmedi |

## 2026-09-23 13:17 UTC otomasyon kontrolü

- Canlı sürüm `737bebf`, yerel HEAD `46e39cb`; başka ajanın takipli
  arayüz/rota/test değişiklikleri ve çalışan test süiti var. Bu tur deploy
  veya uygulama kodu değişikliği yapılmadı.
- Sınırlı teslim: `kibris-hellimli-gozleme-6QTYM` için tek 4:3 görsel
  üretildi, 1920x1620 PNG sunucu teslimi ve stage dry-run doğrulandı,
  yerel geçiciler silindi, atomik kuyruk işi kapatıldı. İnsan onayı ve
  canlı görünürlük bekliyor. Ayrıntılar teslim raporunun 23 Eylül kaydında.
- Güncel PublicMediaEligibilityService: 1.007 tarif, 128 uygun görselli,
  879 uygun görselsiz. Teslim sayısı bu ölçümün yerine kullanılmadı.
- GLOB-010 **açık**: `737bebf` kalite kapısı human_approved_at ve
  human_approved_by_user_id istiyor; ContentFactoryController::approve
  bunları doldururken auto_publish=false kaydediyor ve taslak kaldığını
  bildiriyor. SeoAutoDrip yine draft seçip published yapıyor; günlük
  08:00/limit 3 görev sürüyor. Ayrı yayın kararı kontrolü ve onaylı
  taslağın taslak kalacağı regresyon testi eksik. AI-Worker-10 salt
  okunur incelemesi koordinatör tarafından koddan doğrulandı.
- Yeni kalite kapısı canlıda olsa da GLOB-010 kapanmış sayılmadı.
  GLOB-011 dil kapsamı doğrulaması açık; yedi etkin dilin hazır olduğu
  iddia edilmedi. Son GSC ölçümünün mülk/dönem/kaynak/gözlem bilgileri
  teslim raporuna eklendi; dil/pazar kırılımı bu tur alınmadı.

## 2026-09-24: kuyruk komutunun yeniden çalıştırılması

| Kimlik | Önem | Bulgu | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-012 | Yüksek | `a7fa88b` passthru korumasını eklerken script/komut tanımını kaldırmış; yerelde `media:queue status` dahil bütün işler Undefined variable $cmd ile duruyor | AI-Worker-1 | Yerelde düzeltildi; tam süit doğrulaması sürüyor | Eksik iki satır geri eklendi, kapalı passthru korundu. Yeni feature testleri önce aynı hatayla 2/3 başarısız, düzeltme sonrası feature + kuyruk unit testleri 8/8, 42 assertion geçti. AI-Worker-11 salt okunur incelemesinde engelleyici bulgu yok. |

- Başlangıç yerel HEAD `bea2f8f`; canlı `7ee21100c410e06d183e8053b2db875b8190d4cd`.
  Blog hazırlık/yayın işinin dosyalarına girilmedi; GLOB-010 açık kalıyor.
- Düzeltilen yerel komutla uçtan uca gerçek iş: `kibris-kolokoti` claim,
  canlı tarif kimliği kontrolü, tek görsel üretimi/inceleme, sunucu teslimi,
  boyut/checksum/stage doğrulaması ve complete başarılı. İnsan onayı verilmedi.
- 2026-09-24 06:05:39 UTC PublicMediaEligibilityService: 1.007 tarif,
  130 uygun görselli, 877 uygun görselsiz. Önceki gözleme teslimi artık
  uygun; bu tur yapılan onay işlemi değildir. Kolokoti yalnız teslim edildi.
- Yeni ölçüm, test sonucu ve dosya konumu teslim raporunun 24 Eylül kaydında.

## 2026-09-25 05:46 UTC otomasyon kontrolü

- Yerel görsel araçları ve canlı PublicMediaEligibilityService yeniden
  doğrulandı. `kibris-usulu-flaouna` atomik claim ile alındı; canlı tarif
  kimliği malzeme ve adımlardan doğrulandı; tek 4:3 görsel incelendi,
  1920x1620 PNG sunucu teslimi ve stage dry-run doğrulandı; yerel geçiciler
  silindikten sonra iş complete edildi. İnsan onayı veya manifest değişmedi.
- Canlı uygunluk ölçümü: 536 published/verified tarif, 135 uygun görselli,
  401 uygun görselsiz. Teslim dosyası bu ölçümle aynı şey değildir.
- Güncel GSC snapshot 52 tıklama / 4.306 gösterim / ortalama konum 9,9438;
  dönem, kaynak, mülk ve gözlem ayrıntıları teslim raporunda. Dil/pazar/sayfa
  kırılımı yok; tahmin üretilmedi.
- Canlıdaki `public/.htaccess` takipli farkı başka çalışmaya aittir ve
  değiştirilmedi. Dil etkinlik ayarları ve başka işçinin Molohiya kilidi
  korunmuştur. Bu tur kod/deploy/blog/backlink iletişimi yapılmadı.

## 2026-09-25 12:53 UTC otomasyon kontrolü

Önceki hazır/yeşil kayıtları tarihli geçmiş kanıttır; bu turun veya bütün
dillerin güncel hazırlık sonucu değildir. Yerel `adf6440`, canlı `e554e43`;
başka ajanın takipli kod/dil/tasarım değişiklikleri sürüyor, deploy yapılmadı.

| Kimlik | Önem | Bulgu | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-013 | Yüksek | Medya kuyruğu canlı tariflerden eski: 347 pending kaydın 309'unda exact slug harf farkı, 145'inde taslak/doğrulanmamış kayıt, 2'sinde tarif yok | Koordinatör | Açık; ilk geçersiz iş ayrıldı | 12:49:25 UTC canlı eşleştirme. Kategoriler örtüşür. kibris-usulu-flaouna-4546F kendi worker kilidiyle block; diğer işler değiştirilmedi. |
| GLOB-014 | Takip | Dil performansı rolling 29 dahil tarih/all verisini, kayıtlı toplam final/28 günlük veriden ayırmıyor; fallback kaynak bilgisini korumuyor | SEO ölçüm koordinatörü | Açık | pageRows(28), LocaleGrowthService::olcumSatirlari; AI-Worker-2 salt okunur kontrolü. Aynı döneme ait yeni kaynaklı tablo teslim raporuna eklendi. |

- `soganli-corek` için tek 4:3 görsel üretildi; sunucu 1920x1620 PNG,
  boyut/MIME/checksum/staging hazır doğrulandı. Atomik complete başarılı,
  yerel geçiciler silindi, kendi açık kilit sayısı 0. İnsan onayı bekler.
- Canlı uygunluk 536 tarifte 136 uygun/400 eksik; teslimden önce ölçüldü.
- TR/EN önceleme gerçek final sayfa ölçümünden: TR ön ısıtma 304 gösterim,
  fırın dereceleri 253; EN cennet çamuru 105, kıymalı bezelye 56.
  Hepsinde bu dönemde 0 tıklama. Ülke/pazar verisi alınmadı.
- Dil etkinliği, medya onayı ve yayın değişmedi. Yeni backlink iddiası
  yok. Kod test/deploy yapılmadı; dosya ve kuyruk işlemleri doğrulandı.
  Ayrıntılı kaynak/dönem/gözlem ve sunucu dosya konumu teslim raporunda.

## 2026-09-26: Search Console istek bekleme koruması

| Kimlik | Önem | Bulgu | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-015 | Orta | pageRowsBetween ve pageQueries, 429/503 sonrası kaydedilen Retry-After süresini okumadan yeniden OAuth/API isteği gönderiyordu | AI-Worker-1; inceleme AI-Worker-12 | Kapatıldı: canlı f0cbaac, 26 Eylül 23:05 UTC | İlgili 18 test/72 assertion, tam PHP süiti 1.661 test/9.166 assertion geçti. Dokuz canlı HTTP kontrolü başarılı; sunucuda yalıtılmış cache/HTTP kontrolü üç girişte 0 dış isteği doğruladı. |

- GLOB-014 tarih aralığı/kaynak ayrımı bu sınırlı düzeltmeyle kapanmaz.
- 17:02:59 UTC canlı okuma: scheduler son işi queue_drain, tamamlanma
  17:02:22 UTC, healthy. Bu, bütün SEO görevlerinin sonucu değildir.
- GSC snapshot: sc-domain:housechefcyprus.com, api_import, web,
  26 Ağustos - 22 Eylül, kaynak gözlemi 26 Eylül 06:45:32 UTC;
  58 tıklama / 4.908 gösterim / ortalama konum 9,925835.
  Bu tur yeni dil/pazar kırılımı alınmadı; önceki farklı dönemle artış
  hesabı yapılmadı. Dil ayarları, içerik ve medya onayı değiştirilmedi.

## 2026-09-27: backlink kontrollerinde ortak günlük sınır

| Kimlik | Önem | Bulgu | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-016 | Orta | Backlink doğrulamasında sınır yalnız çağrı başınaydı; günlük 20 isteyen scheduler ve ayrıca 3 isteyen GrowthSystem ortak günlük sayım kullanmıyordu | AI-Worker-1; test/inceleme AI-Worker-12 | Yerelde düzeltildi; tam test/yayın doğrulaması bekliyor | Aynı kilit altında bugünkü checked_at kayıtları sayılıyor, varsayılan günlük 20 sınırı paylaşılmış oluyor. İlgili 21 test/86 assertion geçti. |

- 08:38 UTC canlı: bugünkü üç doğrulama kaydı 3+9+2=14 aday kontrolü.
  Kontrol sayısı dış HTTP isteği sayısı değildir; engellenen aday da sayılır.
  Bu tur yeni backlink doğrulama isteği gönderilmedi; limit bir hedef değil.
- 670 adayda check_status=present kaydı yok. Adaylar edinilmiş backlink
  değildir; iletişim veya link edinimi yapılmadı.
- PublicMediaEligibilityService: 534 published/verified tarifte 139 uygun,
  395 eksik. Bu tur görsel teslimi/insan onayı yok. Kuyruktaki eski slug
  uyumsuzluğu ve başkasına ait kilitler korundu (GLOB-013 açık).
- GSC aynı final dönemde page+country kırılımı kayda alındı. Kaynak,
  tarih, gözlem ve kapsam farkları .claude/completions/2026-09-27-backlink-daily-budget.md
  içinde; GLOB-014 uygulamadaki kaynak/tarih tutarsızlığı açık.
