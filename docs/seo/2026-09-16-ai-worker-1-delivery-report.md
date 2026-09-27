# AI-Worker-1 teslim ve izleme kaydı

Bu dosya, 2026-09-16'da başlayan çalışmanın otomasyonda kullanılan kalıcı
başvurusudur. Dosyanın yokluğu 2026-09-18 kontrolünde giderildi. Aşağıdaki
güncel ölçümler 2026-09-18 UTC gözlemidir; eski raporların hazır iddiaları
güncel canlı doğrulama yerine kullanılmaz.

## 2026-09-18 başlangıç kontrolü

- Yerel ve canlı sürüm: `79c36cf`. Başlangıçta takipli yerel kod temizdi.
  Başka ajanların `.agents/`, `.claude/commands/`, `.worktrees/` ve
  `skills-lock.json` dosyalarına dokunulmadı.
- Canlı takipli farklar yalnız paylaşılan storage bağlantısının beklenen
  `.gitignore` durumuydu. Aktif deploy süreci görülmedi.
- Zamanlayıcı: `app:scheduler-health --json`, son tamamlanma
  `2026-09-18T06:56:33+00:00`, görev `queue_drain`, durum `healthy`.
  Bu ölçüm diğer bütün otomasyonların başarılı olduğunu kanıtlamaz.
- `config/locales.php`: tr/en/de/fa/pl/ru/pt etkin. Hiçbir etkinlik ayarı
  değiştirilmedi; bu tur içerik/arayüz çeviri kapsamını doğrulamıyor.
- Canlı `PublicMediaEligibilityService` ölçümü, 06:58:10 UTC:
  yayınlanmış ve doğrulanmış 1.007 tarif; uygun görselli 105,
  uygun görselsiz 902. Teslim edilmiş dosya sayısı bu ölçüm değildir.

## Search Console

- Mülk: `sc-domain:housechefcyprus.com`; kaynak: `api_import`.
- Dönem: 2026-08-18 - 2026-09-14; gözlem: 2026-09-18 06:47:39 UTC.
- 7 tıklama, 451 gösterim, ortalama konum 13,5521; CTR %1,5521.
- 71 sorguluk örneklem; API bağlantısı mevcut. Bu toplulaştırılmış sonuç
  dil/pazar kırılımı, doğrulanmış backlink veya birincilik kanıtı değildir.
- Bu tur backlink iletişimi, ücretli servis, içerik üretimi/yayını yapılmadı.

## Görsel teslimleri

Önceki beş teslim sunucuda tekrar 1920x1620 olarak doğrulandı:

- `kibris-usulu-tavuklu-kolokas-C0DS3`
- `kibris-usulu-seftali-kebabi-PK57V`
- `kibris-usulu-firin-tavuk-baget-PCR3Q`
- `kibris-usulu-molohiya-T9C33`
- `kibris-usulu-seftali-kebabi-KEFH3`

Bu tur `Automation-Images-20260916` işçisi bir görsel teslim etti:

- `kibris-hellimli-gozleme`: PNG, 1920x1620, 4.373.484 bayt.
- Konum: sunucuda
  `shared/storage/app/media-import/recipes/recipe--kibris-hellimli-gozleme.png`.
- Malzemeler/kimlik kontrolü, tek görsel incelemesi, yükleme, sunucu ölçü
  kontrolü ve kuyruk `complete` işlemi yapıldı; geçici yerel görsel silindi.
- `media:stage-delivered` bu dosyayı hazır buldu. Rapordaki başka 232 eski
  atlanan dosya çözülmüş sayılmadı. İnsan onayı veya canlı gösterim verilmedi.
- Yeni engeller: `limonlu-balik-tepsisi` canlıda taslak;
  `peynirli-kraker` malzemelerinde peynir yok, adımları jenerik.
- Kuyruk: 366 toplam, 8 teslim tamam, 5 engelli, 352 bekleyen,
  AI-Worker-4 üzerinde 1 kilit. Bu oturumun işçi kilidi kalmadı.
  Yerel flock başka bilgisayardaki kuyruğa ortak kilit sağlamaz.

## Seçilen sınırlı düzeltme: gerçek oylama bütünlüğü

06:54:43 UTC canlı denetimde 24.874 oyun tamamı, son sürümdeki
`RecipeTestKitchenRatingsSeeder` tarafından üretilen deterministik
`test-kitchen-ip-{recipe_id}-{i}` SHA256 işaretleriyle eşleşti.
1.007 tarif etkileniyor, o anda işaret dışında kalan oy yoktu.
Bu kayıtlar ziyaretçi değerlendirmesi değildir.

Üretici kapatılıyor; mevcut kayıtlar silinmeden önce sunucuda özel bir
karantina arşivine eksiksiz yazılıp doğrulanacak. Gerçek oylar ve bu sırada
gelen yeni ziyaretçi oyları korunacak. Test/yayın/karantina sonucu aşağıdaki
teslim kapanışına yazılmadan canlı düzeltme tamamlanmış sayılmaz.

### 2026-09-18 canlı kapanış

- Canlı sürüm: `58571b3c16161de2e2b961d3ee29aa1d8233e357`.
- Testler: tam PHP süiti 1.462 test ile başarılı; JavaScript kontrolleri 7/7
  başarılı.
- `RecipeTestKitchenRatingsSeeder` sentetik oy üretmeyecek şekilde engellendi.
- `recipe-ratings:quarantine-synthetic --apply` canlıda çalıştırıldı:
  24.874 sentetik kayıt, 1.007 tarif, değişen veya işlem dışı bırakılan satır 0.
- Özel arşiv:
  `storage/app/private/quarantine/recipe-ratings-0bbbcab6-633e-4cb6-b535-de456613d28f.jsonl`.
- Manifest:
  `storage/app/private/quarantine/recipe-ratings-0bbbcab6-633e-4cb6-b535-de456613d28f.jsonl.manifest.json`.
- Arşiv doğrulaması: 24.874 satır; SHA256
  `32d5622bb9af7c7b04bf8020d6d19e8790c951b22f5465538e53ba22b0facfd0`.
- Son canlı kontrol: sentetik eşleşme 0, toplam oylama satırı 0.
- Örnek tarifler `kibris-hellimli-gozleme` ve `kibris-usulu-molohiya-T9C33`
  HTTP 200 döndü; `Recipe` JSON-LD korunuyor, gerçek oy kalmadığı için
  `aggregateRating` artık üretilmiyor.

## Açık takip

- `SeoAutoDrip` taslakları her gün otomatik yayımlıyor; yorumda insan onayı
  varsayılıyor. Bu davranış taslak teslimi/insan yayın kararı kuralıyla
  ayrı incelenmeli. Bu turun oylama düzeltmesine dahil edilmedi.
- Bütün dil içerikleri, 902 görsel açığı, eski atlanan teslimler ve
  doğrulanmış backlink edinimi tamamlanmış değildir.

## 2026-09-23 13:17 UTC: tek görsel teslimi ve güncel ölçüm

- Bu turun sınırlı iyileştirmesi bir geçerli tarif görselinin teslimidir.
  Yerel HEAD `46e39cb`, canlı sürüm `737bebf17f8c2981bfc273647c88878e823e12cf`.
  Başka ajanın arayüz/rota/test değişiklikleri ve çalışan PHP test süiti
  görüldü. Bu tur uygulama kodu, deploy veya içerik yayını yapılmadı.
- `Automation-Images-20260916` başlangıçta aktif işe sahip değildi.
  Atomik yerel `media:queue claim` tam slug olarak
  `kibris-hellimli-gozleme-6QTYM` döndürdü. Canlı kayıt: ID 1164,
  public_id `01M2MGK1N3J1JWE2NA09SS3BHS`, published/verified.
  Hellim, nane ve ince hamurun katlanarak tavada pişirilmesi canlı
  malzeme/adımlardan doğrulandı. PublicMediaEligibilityService uygun
  medya bulmadı; aynı adlı teslim dosyası ve tarif manifest kaydı yoktu.
- Yerleşik image_gen ile 1448x1086 (tam 4:3) tek yemek fotoğrafı üretildi;
  görsel tek tek incelendi, yazı/logo/filigran/çerçeve/kolaj yok.
  `scripts/upload-recipe-image.sh` ile son dosya 1920x1620 PNG olarak
  sunucuya teslim edildi. Yerel üretilen dosya ve /tmp ara dosyası silindi.
- Sunucu dosyası:
  `/home/u584361532/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/recipe--kibris-hellimli-gozleme-6QTYM.png`.
  4.359.044 bayt; SHA256
  `b2e8d154ae211a068170faec8f575a9d38ad3842e6d19e8b50ad0336e09ac517`.
  Sunucuda getimagesize, MIME, bayt ve checksum doğrulandı.
- Salt okunur `media:stage-delivered` exit 0 ve bu dosya için `hazır`.
  Genel sonuç 4 hazır / 245 atlanan; diğer teslimlerin açık sorunları
  çözülmüş sayılmadı. Manifest veya insan onayı kaydı değiştirilmedi.
- Boyut/dosya doğrulamasından sonra `complete --worker` başarılı.
  Kuyruk 366 toplam, 9 tamamlanan, 351 bekleyen, 5 engelli, 1 başka
  işçiye ait kilit. Bu işçinin açık kilidi yok; AI-Worker-4 kilidi korundu.
  Kuyruk yerel flock kullanıyor; sunucuda ayrı kuyruk dosyası yoktu.
  Canlı `media:queue status` PHP passthru kısıtı nedeniyle çalışmadı;
  yerel artisan kuyruğu kullanıldı, ortak sunucu kilidi iddia edilmiyor.
- 13:17:49 UTC canlı PublicMediaEligibilityService ölçümü:
  1.007 published/verified tarif, 128 uygun görselli, 879 uygun görselsiz.
  Bu sayılar 18 Eylül'deki 105/902 ölçümünün yerine geçer. Yeni dosya
  teslimi insan onayı veya canlı görünürlük anlamına gelmez; 23 tariflik
  önceki değişim bu oturumun sonucu olarak sunulmuyor.
- Sunucu scheduler-health: 13:13:32 UTC son tamamlanan görev queue_drain,
  status healthy, task_count 2. Bu tek ölçüm her görevin başarısını göstermez.
- Son kayıtlı Search Console API ölçümü: mülk
  `sc-domain:housechefcyprus.com`, kaynak `api_import`, web araması;
  dönem 2026-08-23 - 2026-09-19, kaynak gözlemi 2026-09-23 06:45:36 UTC,
  bu tur okuma 13:17:49 UTC. 33 tıklama, 2.953 gösterim, ortalama konum
  10,13173044361666; 332 sorguluk örneklem. Dil/pazar kırılımı bu okumada
  alınmadı; bundan dil/pazar önceliği veya sıralama garantisi çıkarılmadı.
- Canlı config/locales.php: tr/en/de/fa/pl/ru/pt etkin. Ayar değişmedi.
  Her dil için içerik, arayüz, adım, malzeme, canonical, karşılıklı
  hreflang, sitemap ve schema kapsamı bu tur yeniden test edilmedi.
  Yeni backlink adayı/iletişim/doğrulanmış bağlantı veya blog teslimi yok.
- AI-Worker-10 salt okunur çapraz incelemesi GLOB-010'un sürdüğünü
  doğruladı: yeni insan onayı alanları ayrı yayın kararı sağlamıyor.
  ContentFactoryController onaydan sonra taslak kaldığını söylüyor,
  SeoAutoDrip günlük görevde aynı taslağı published yapabiliyor.
  Aktif kalite kapısı çalışmasına müdahale edilmedi; bulgu açık bırakıldı.
- Bu tur uygulama kodu değişmedi. Doğrulamalar: üretim oranı, tek görsel
  incelemesi, sunucu boyut/MIME/checksum, stage dry-run, complete ve
  kilit/yerel temizlik kontrolü. Başka ajanın test süiti bu turun başarılı
  test kanıtı olarak kullanılmadı.

## 2026-09-24: media:queue regresyonu ve Kolokoti doğrulaması

- İşin sürdürüldüğü gerçek gözlem saati 05:55-06:05 UTC; heartbeat
  tetikleme saati 01:18 UTC, ölçüm zamanı yerine kullanılmadı.
  Yerel HEAD `bea2f8f`, canlı sürüm `7ee21100c410e06d183e8053b2db875b8190d4cd`.
  Canlı takipli farklar paylaşılan storage .gitignore bağlantılarıyla
  sınırlı; deploy süreci görülmedi. Önceki rapor/kuyruk değişiklikleri
  korunarak başka ajanların dosyalarına dokunulmadı.
- GLOB-012 kök neden: `a7fa88b`, MediaGenerationQueueCommand içinde
  `$script` ve `$cmd` tanımlarını silmiş. `php artisan media:queue status`
  exit 1 / Undefined variable $cmd verdi. İki tanım geri eklendi;
  passthru devre dışı olduğunda güvenli hata ve CLI yönlendirmesi korundu.
- Yeni `tests/Feature/MediaGenerationQueueCommandTest.php`, gerçek scripti
  izole geçici kuyrukta çalıştırıyor: status salt okunur, boşluk/apostrof
  içeren yol ve argümanlar korunuyor, yanlış sahip exit 1 alıyor, block
  sebebi doğru taşınıyor, kapalı passthru kuyruğu değiştirmiyor.
  İlk çalıştırma 2 başarısız / 1 başarılı (aynı $cmd hatası), düzeltme
  sonrası `MediaGenerationQueueCommandTest` + `MediaGenerationQueueTest`
  8 test / 42 assertion başarılı. Yeni test dosyası Pint, iki PHP dosyası
  php -l ve diff whitespace denetimi başarılı.
- AI-Worker-11 salt okunur kod/test incelemesinde engelleyici bulgu yok;
  testlerin gerçek kuyruğa veya SSH'ye dokunmadığı doğrulandı.
- Tam `php artisan test --compact` süiti başlatıldı; sonuç henüz
  kaydedilmedi. Log: `/tmp/housechef-20260924-media-queue-full-tests.log`.
  Yerel kod/test commit'i `2d03c30`; canlıya kod deploy edilmedi.
  Sonraki tur bu logun son JSON sonucunu ve çalışan süreçleri kontrol
  etmeden ikinci bir tam süit başlatmamalı. Bu tur tam süit yeşil denmedi.
- Gerçek iş testi: Automation-Images-20260916 başlangıçta aktif işe sahip
  değildi. Claim tam slug `kibris-kolokoti` döndürdü. Canlı ID 855,
  public_id `01M2MFDV99CRH5MCG8VPKE3248`, başlık Kıbrıs Kolokotisi,
  published/verified, titleDescribesADish true. Balkabağı, ince bulgur,
  kuru üzüm, un, yağ, tarçın, şeker ve tuz; yarım ay hamur, çatalla
  kapatılmış kenarlar ve fırınlama adımları canlıdan okundu. Uygun medya,
  önceki teslim dosyası veya manifest kaydı bulunmadı.
- Yerleşik image_gen: 1448x1086 (tam 4:3), tek tabakta kolokoti; tek tek
  incelendi. Yazı/logo/filigran/çerçeve/kolaj yok. upload-recipe-image.sh
  sonrası sunucu PNG 1920x1620, 4.167.202 bayt, SHA256
  `a03644459ec6bed8deb07341b3356b0dc97600b5bf900f574d20c91ae4b6dff4`.
  Dosya: `/home/u584361532/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/recipe--kibris-kolokoti.png`.
- 06:05:09 UTC getimagesize/MIME/checksum doğrulandı. Salt okunur
  media:stage-delivered exit 0; hedef dosya hazır, genel 1 hazır / 252
  atlanan. Diğer dosyalar çözülmüş sayılmadı. Ardından complete --worker
  başarılı; yerel üretilen kaynak ve /tmp ara dosyası silindi. Onay
  kaydı/manifest değiştirilmedi, canlı görünürlük verilmedi.
- Son kuyruk: 366 toplam, 10 tamamlanan, 350 bekleyen, 5 engelli,
  AI-Worker-4 üzerinde 1 kilit. Automation-Images-20260916 aktif kilidi 0.
  Yerel storage 1,9 GB; build-artifacts yok. Yeni yerel görsel saklanmadı.
- 06:05:39 UTC PublicMediaEligibilityService: 1.007 published/verified
  tarif, 130 uygun görselli / 877 uygun görselsiz. Önceki teslim
  kibris-hellimli-gozleme-6QTYM artık uygun; bu oturum onay vermedi.
- Scheduler-health 05:55:33 UTC: queue_drain, healthy, task_count 2.
  Bu, ayrı seo_auto_drip görevinin başarı kanıtı değildir.
- Search Console kayıtlı ölçümü: `sc-domain:housechefcyprus.com`,
  `api_import`, web, dönem 2026-08-24 - 2026-09-20, kaynak gözlemi
  2026-09-24 00:00:19 UTC, okuma 06:05:39 UTC. 41 tıklama, 3.661
  gösterim, ortalama konum 10,151324774651734; 394 sorguluk örneklem.
  Dil/pazar kırılımı alınmadı; toplu sonuçtan dil/pazar önceliği üretilmedi.
- tr/en/de/fa/pl/ru/pt etkinliği canlı configten okundu ve değiştirilmedi.
  Her dilin içerik/arayüz/adım/malzeme/canonical/hreflang/sitemap/schema
  kapsamı bu tur yeniden test edilmedi. Yeni blog teslimi, backlink
  iletişimi veya yeni doğrulanmış backlink iddiası yok.

## 2026-09-25 05:46 UTC: Flaouna teslimi ve canlı ölçüm

- Yerel dal `visual-redesign-v2`, başlangıç HEAD `050c6ea`; canlı sürüm
  `7ee21100c410e06d183e8053b2db875b8190d4cd`. Canlıdaki takipli ek fark
  `public/.htaccess` içinde ads.txt için MIME/CORS başlıklarıydı; bu tur
  dosyaya veya deploya müdahale edilmedi. Scheduler-health 05:42:34 UTC,
  son görev queue_drain, status healthy, task_count 2.
- `Automation-Images-20260916` başlangıçta aktif işe sahip değildi.
  Atomik claim tam slug `kibris-usulu-flaouna` döndürdü. Canlı tarif ID
  837, public_id `01M2MFDTWZV56HX96JQX92BFWR`, published/verified;
  görünür başlık `Kıbrıs Usulü Flaouna (Pilavuna)`. PublicMediaEligibilityService
  uygun medya bulmadı; aynı teslim dosyası ve manifest kaydı yoktu.
- Kimlik canlı içerikten doğrulandı: nor peyniri, hellim, yumurta, mahlep,
  damla sakızı, irmik ve susam; mayalı hamurun üç kenardan katlanarak
  ortası açık üçgen yapılması, yumurta sarısı ve susamla fırınlanması.
- Yerleşik image_gen ile 1448x1086, tam 4:3 tek yemek fotoğrafı üretildi.
  Görsel tek tek incelendi: üçgen açık merkez, peynir-nane dolgusu ve
  susamlı koyu altın kabuk tarifle uyumlu; yazı, logo, filigran, çerçeve
  ve kolaj yok. Kullanılan yöntem yerleşik imagegen, CLI/API fallback değil.
- `scripts/upload-recipe-image.sh` sonrası sunucu dosyası 1920x1620 PNG,
  4.583.854 bayt, SHA256
  `bd8ce6ce07d2ff9666d4e2d701256ae804a69f9791074c177fc98b4158a3c053`.
  Konum:
  `/home/u584361532/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/recipe--kibris-usulu-flaouna.png`.
- 05:45:29 UTC sunucuda ölçü, MIME, bayt ve checksum doğrulandı.
  Salt okunur `media:stage-delivered` exit 0 ve hedef dosya `hazır`;
  genel sonuç 2 hazır / 253 atlanan. Diğer dosyalar çözülmüş sayılmadı.
  Ardından complete başarılı. Yerel kaynak ve /tmp ara dosyası silindi.
  Manifest/insan onayı değiştirilmedi; teslim canlı görünürlük değildir.
- Son kuyruk: 366 toplam, 12 tamamlanan, 348 bekleyen, 5 engelli,
  AI-Worker-4 üzerinde 1 kilit. Automation-Images-20260916 aktif kilidi 0.
- 05:46:28 UTC PublicMediaEligibilityService ölçümü: 536 published/verified
  tarif, 135 uygun görselli, 401 uygun görselsiz. Yayındaki tarif toplamı
  önceki 1.007 ölçümden veri kalite çalışmasıyla 536'ya düşmüştür; eski
  130/877 sayısı güncel sonuç olarak kullanılmadı. Flaouna dosyası henüz
  onaylanmadığı için bu uygun sayı teslim başarısı olarak sunulmuyor.
- Search Console mevcut snapshot: mülk `sc-domain:housechefcyprus.com`,
  `api_import`, web; dönem 2026-08-25 - 2026-09-21; kaynak gözlemi
  2026-09-25 00:00:07 UTC, bu tur okuma 05:46:28 UTC. 52 tıklama,
  4.306 gösterim, ortalama konum 9,943799349744543; 436 sorguluk örneklem.
  İlgili en yüksek sorgular: `kıbrıs mezesi` 28/1/7,36; `molohiya`
  16/0/7,88; `home made cypriot restaurant` 15/0/46,93; `kıbrıs nor
  peyniri` 11/0/10,27; `kıbrıs mezeleri` 10/0/8,3
  (gösterim/tıklama/konum). Snapshotta dil, ülke/pazar ve sayfa kırılımı
  yok; sorgu metninden pazar tahmin edilmedi.
- Canlı config/locales.php: tr/en/de/fa/pl/ru/pt etkin; hiçbir enabled
  alanı değiştirilmedi. Dil başına bütün içerik, arayüz, tarif adımı,
  malzeme, canonical, karşılıklı hreflang, sitemap ve schema kapsamı bu
  tur yeniden test edilmedi. Blog taslağı, backlink adayı/iletişimi veya
  doğrulanmış backlink eklenmedi.

## 2026-09-25 12:53 UTC: Soğanlı Çörek teslimi ve eski kuyruk kayıtları

- Başlangıç yerel `visual-redesign-v2/adf6440`, canlı `e554e43`.
  `e0422c3` header düzeltmesi ikisinin de atasıdır; bekleyen eski deploy
  yeniden çalıştırılmadı. RecipeController, ApprovedMediaImportService,
  yedi dil dosyası, home Blade/CSS/JS ve HomeMatchDemoService başka
  çalışmanın alanıdır; dosyalara/test sürecine müdahale edilmedi.
  Eski main-transition dosyası bu checkout'ta yok; yeniden oluşturulmadı.
- Kuyruk ve yükleme scriptleri ile canlı PublicMediaEligibilityService
  yeniden doğrulandı. Başlangıçta bu worker'ın açık kilidi yoktu.
  Claim `kibris-usulu-flaouna-4546F` döndürdü: canlı ID 1325 taslak,
  canlı slug `kibris-usulu-flaouna-4546f`. Aynı worker ile gerekçeli
  block yapıldı; tarif durumu veya slug değiştirilmedi.
- 12:49:25 UTC salt okunur kuyruk/canlı eşleştirmesinde o andaki 347
  pending kaydın 309'unda exact slug harf farkı, 145'inde taslak veya
  doğrulanmamış durum, 2'sinde bulunamayan tarif, 2'sinde aynı adlı
  teslim dosyası bulundu. Kategoriler örtüşür; toplam olarak toplanamaz.
  Başka işçilerin kilitleri veya diğer kuyruk satırları değiştirilmedi.
- İkinci atomik claim: `soganli-corek`, ID 839, public_id
  `01M2MFDTZ69Q81740A3RSFBWNC`, Soğanlı Çörek, published/verified.
  Tam slug eşit, yemek adı geçerli, uygun medya/önceki teslim/manifest
  eşleşmesi yok. Canlı tarif süt, tereyağı ve yumurtalı mayalı hamurun
  hafif karamelize soğanla doldurulup yuvarlanmasını ve fırınlanmasını
  tarif ediyor.
- Yerleşik imagegen ile tek 1448x1086 (tam 4:3) fotoğraf üretildi ve
  tek tek incelendi: tek tabakta yuvarlak altın kabuklu çörekler, kesik
  çörekte soğan dolgusu; yazı/logo/filigran/çerçeve/kolaj yok.
- `upload-recipe-image.sh` teslimi: 1920x1620 PNG, 4.372.088 bayt;
  SHA256 `e3d4c12ff84458fd4d0e119130e605696051f49743edcc04269dfea7eef511cf`.
  Sunucu konumu:
  `/home/u584361532/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/recipe--soganli-corek.png`.
  12:51:33 UTC getimagesize/MIME/bayt/checksum ve salt okunur staging
  exit 0 doğrulandı; hedef satır hazır. Genel 2 hazır / 254 atlanan
  sonucu diğer dosyaların çözüldüğü anlamına gelmez.
- Complete worker doğrulamasıyla başarılı. Kaynak generated_images
  dosyası ve /tmp ara PNG silinmiş olarak kontrol edildi. Son kuyruk:
  366 toplam, 13 tamamlanan, 346 pending, 6 blocked, AI-Worker-4 üzerinde
  1 claimed; Automation-Images-20260916 açık kilidi 0. Onay/manifest
  değiştirilmedi. Teslim, insan onayı veya canlı görünürlük değildir.
- 12:49:25 UTC canlı uygunluk: 536 published/verified, 136 uygun
  görselli, 400 uygun görselsiz. Bu ölçüm Soğanlı Çörek tesliminden
  öncedir ve teslim sonrasındaki yayın artışı olarak sunulamaz.
- Scheduler 12:48:28 UTC okundu: son tamamlanma 12:47:30 UTC,
  queue_drain, healthy, task_count 2. Diğer SEO işlerinin sağlığına
  genellenmedi. tr/en/de/fa/pl/ru/pt enabled true; ayarlar değişmedi.

### Aynı dönemdeki gerçek Search Console dil kırılımı

Mülk `sc-domain:housechefcyprus.com`; web; 2026-08-25 - 2026-09-21
(28 gün). Kayıtlı `api_import` toplamı 52 tıklama / 4.306 gösterim,
konum 9,9438; kaynak gözlemi 2026-09-25 06:45:43 UTC, okuma 12:48:28 UTC.
Karşılaştırılabilir yeni tek API çağrısı `pageRowsBetween` ile aynı
tarihleri ve `dataState=final` kullandı: kaynak live_api, dimension page,
gözlem 12:52:47 UTC, 842 satır (5.000 satır sınırına ulaşılmadı).
Dil URL öneklerinden sınıflandırıldı; ziyaretçi dili veya ülkesi değildir.

| URL dili | Tıklama | Gösterim | Gösterim ağırlıklı konum |
| --- | ---: | ---: | ---: |
| tr | 28 | 2366 | 9,916 |
| en | 7 | 786 | 11,198 |
| de | 5 | 638 | 9,555 |
| fa | 8 | 113 | 6,106 |
| pl | 3 | 117 | 8,581 |
| ru | 0 | 278 | 8,417 |
| pt | 2 | 109 | 11,963 |

Sayfa satırları toplamı 53/4.407; mülk toplamından ayrı agregasyondur.
Ülke/pazar boyutu istenmedi, veri yok. Öncelik adayı TR fırın ön ısıtma
yazısı (304 gösterim/0 tıklama/8,08) ve fırın dereceleri yazısı
(253/0/7,91); EN Kilis cennet çamuru tarifi (105/0/8,52) ve kıymalı
bezelye tarifi (56/0/11,45). Bu gözlemler revizyon önerisidir; metin veya
yayın değişmedi. Diğer dillerin içerik/arayüz/adım/malzeme/canonical/
hreflang/sitemap/schema hazırlığı bu tur yeniden doğrulanmadı.

TASKS.md içindeki 69/6.439 ile bu ölçümler aynı pencere olarak
karşılaştırılmamalı: pageRows(28) 28 Ağustos - 25 Eylül arası 29 dahil
tarihi ve geçici `all` verisini ister. LocaleGrowthService hata/boşlukta
yerel tabloya geçer, dönen sonuca kaynak/tarih eklemez. AI-Worker-2
koddan bağımsız doğruladı; uygulama koduna bu tur girilmedi.

Yeni backlink, iletişim, blog taslağı, yayın veya deploy yok. Kod
değişmediğinden tam PHP süiti çalıştırılmadı ve yeşil iddiası yapılmadı;
gerçek kuyruk claim/block/complete ve dosya/staging kontrolleri geçti.

## 2026-09-26 23:05 UTC: Search Console bekleme koruması teslimi

- Önceki Soğanlı Çörek kaydı e52ddf6 içinde kayıtlı; yeniden teslim
  edilmedi. Yerel/canlı fc7cc3f üzerinden yalnız GLOB-015 düzeltildi.
  pageRowsBetween/pageQueries artık 429/503 sonrasındaki ortak Retry-After
  süresinde yeni OAuth/API isteği göndermiyor. pageRows aynı korumayı alır.
- İlgili testler 18/18, 72 assertion; tam PHP süiti 1.661/1.661,
  9.166 assertion, 0 error/failure. AI-Worker-12 bağımsız incelemesi temiz.
- Canlı f0cbaac, normal release akışı exit 0. Dokuz HTTP uç noktası
  geçti. Sunucuda RAM cache/fake HTTP ile yalıtılmış tekrar kontrolü:
  üç giriş noktası ertelendi, 0 dış istek. Google'a test isteği atılmadı.
  Bu sürümün geçici çalışma kopyası ve paketi temizlendi.
- Kuyruk 366 toplam/15 tamamlanan/343 pending/6 blocked/2 başkasına ait
  kilit; kendi aktif kilidi 0. Yeni claim yok. Sıradaki HQ1AZ son ekli
  işin canlı slug'ı küçük harfli hq1az olduğundan GLOB-013 açık.
- GLOB-014 tarih/kaynak ayrımı bu değişiklikle çözülmedi. En son okunan
  GSC snapshot sc-domain:housechefcyprus.com, api_import/web,
  2026-08-26 - 2026-09-22; kaynak gözlemi 06:45:32 UTC, okuma 17:02:59
  UTC; 58 tıklama, 4.908 gösterim, konum 9,925835. Yeni dil/pazar
  kırılımı alınmadı; bu sayı farklı dönemle büyüme hesabında kullanılmadı.
- Tam kanıt ve log konumları:
  .claude/completions/2026-09-26-gsc-page-backoff.md.

## 2026-09-27 14:31 UTC: ortak günlük backlink sınırı doğrulandı

- 325ecea ile yapılan sınırlı düzeltme tüm backlink kontrol çağrılarına
  ortak günlük sınır uygular. Varsayılan 20, mevcut günlük scheduler
  sınırıyla aynı; bugün yapılmış kontroller de bu sayıdan düşülür.
- İlgili 21 test/86 assertion, tam süit 1.665 test/9.188 assertion geçti.
  Tam koşu bu düzeltme içindir; sonraki değişiklikler için genel yeşil
  iddiası değildir. AI-Worker-12 bağımsız incelemesi temiz.
- Başka ajan canlıyı c34134a'ya ilerletmiş; düzeltme o sürümde mevcut.
  Üç üretim dosyasının SHA256 değeri test edilen kodla aynı. Tekrar
  deploy yapılmadı; sürmekte olan çeviri/Measurements işi korunmuştur.
- Canlı limit20, bugün14 kontrol. CLI içinde limit0/RAM cache/fake HTTP
  testi deferred ve sıfır istekle geçti; test raporu transaction rollback
  ile geri alındı. Yeni dış yayıncı kontrolü veya iletişim yapılmadı.
- 08:37 UTC gerçek GSC page+country kırılımı, dönem/mülk/kaynak/gözlem
  bilgileriyle tamamlanma raporunda. Dönen kırılım 11/2.207, aynı dönemin
  kayıtlı mülk toplamı 67/5.548; fark açıklanmış veya tam kapsam kabul
  edilmedi. Ülke API'den, dil URL'den; TR/EN öncelikler ayrıca yazıldı.
- Yeni görsel üretimi/onayı, içerik yayını veya dil etkinleştirme yok.
  GLOB-016 kapandı; kuyruk kimliği ve GSC kaynak/tarih sorunları açık.
  Kanıt: .claude/completions/2026-09-27-backlink-daily-budget.md.
