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
