# Backlink doğrulaması: ortak günlük kontrol sınırı

Başlangıç yerel 9daa0a2, canlı f0cbaac; aradaki fark yalnız önceki teslim
belgeleri. Devam eden deploy/test süreci yoktu. .claude/settings.local.json
ve izlenmeyen skill dosyaları başka çalışmaya ait; dahil edilmedi.
Eski main-transition kaydı bu dalda yok; yeniden oluşturulmadı.

## Sınırlı düzeltme

- BacklinkVerificationService aynı ortak kilit altında UTC gün sınırlarıyla
  bugün kontrol edilmiş adayları sayar. Reddedilmiş adaylar da tüketilen
  sayıda kalır; dünkü kontroller yeni günü tüketmez.
- config/seo.php: SEO_BACKLINK_VERIFICATION_DAILY_LIMIT varsayılan 20.
  Bu değer mevcut günlük scheduler --limit=20 ile aynı; yeni bir hedef
  veya doldurulması gereken kota değildir. Sıfır değerinde kontrol yapılmaz.
- Tek çağrı sınırı kalan günlük hakla daraltılır. Hak bitince deferred,
  reason=daily_limit ve ertesi gece retry_at döner; DNS/HTTP yapılmaz.
  Sonuçta daily_limit/daily_remaining korunur. Host bekleme süresi ve
  Retry-After korumaları değiştirilmedi.
- Canlı 08:38:22 UTC gözlemi: üç koşu 00:00:12'de 3, 02:15:04'te 9,
  02:15:33'te 2 kontrol; toplam 14. Kontrol edilmesi HTTP yapılması veya
  backlink bulunması demek değildir. 670 adayın check_status dağılımı:
  blocked149, error18, not_found114, rate_limited7, redirect94, unchecked288.
  present0. Kaynak aday / iletişim / doğrulanmış link ayrı kavramlardır.
  Bu tur yeni doğrulama veya iletişim isteği gönderilmedi.

## Doğrulama

- Yeni 4 regresyon önce beklenen sebeplerle başarısız oldu. Değişiklik
  sonrası ilgili 21 test, 86 assertion geçti. PHP sözdizimi ve diff temiz.
- AI-Worker-12 bağımsız üretim kodu incelemesi: engelleyici bulgu yok.
- Tam süit logu /tmp/housechef-20260927-backlink-budget-full.log,
  JUnit /tmp/housechef-20260927-backlink-budget-full.xml: 1.665 test,
  9.188 assertion geçti, 0 error/failure, 954,117 saniye. Dört yeni günlük
  sınır testi JUnit içinde doğrulandı. Bu koşu 325ecea düzeltmesine aittir;
  sonraki başka ajan değişikliklerinin tam süit sonucu diye kullanılamaz.
- GLOB-016 bu düzeltmedir. GLOB-013 ve GLOB-014 ayrı açık kalır.

## Aynı dönemde gerçek Search Console ülke ve URL dili kırılımı

Mülk sc-domain:housechefcyprus.com; 2026-08-27 - 2026-09-23; web.
Kayıtlı api_import gözlemi 06:45:41 UTC, okuma 08:32:24 UTC:
67 tıklama / 5.548 gösterim / 9,892574 konum.

Aynı dönem için tek yeni API okuması: live_api, dataState=final,
dimensions=[page,country], gözlem 08:37:30 UTC. 709 satır; rowLimit5000'e
ulaşılmadı. Ülke API'den; dil URL önekinden türetildi ve ziyaretçinin dili
olarak yorumlanmadı. Yeniden deneme yapılmadı, ortak kilit ve Retry-After
kontrol edildi; token veya gizli değer rapora yazılmadı.

| URL dili | Tıklama | Gösterim | Gösterim ağırlıklı konum |
| --- | ---: | ---: | ---: |
| tr | 6 | 693 | 15,255 |
| en | 1 | 524 | 15,557 |
| de | 0 | 560 | 10,171 |
| fa | 0 | 41 | 8,854 |
| pl | 2 | 60 | 11,767 |
| ru | 1 | 220 | 9,400 |
| pt | 1 | 109 | 11,890 |

Bu kırılımın toplamı 11/2.207; mülk toplamı 67/5.548 değildir. Farkın
nedenini bu tur doğrulamadık; satır sınırına ulaşılmaması tam kapsama
kanıtı sayılmadı. Sayılar yalnız dönen bu kırılım içinde yorumlanmalı;
önceki dönem ya da farklı agregasyonla büyüme hesabı yapılmamalı.

| URL dili / API ülkesi | Tıklama | Gösterim | Konum |
| --- | ---: | ---: | ---: |
| tr / tur (Türkiye) | 3 | 494 | 17,350 |
| tr / cyp (Kıbrıs) | 2 | 119 | 8,630 |
| en / cyp (Kıbrıs) | 0 | 92 | 18,989 |
| en / gbr (Birleşik Krallık) | 0 | 44 | 25,114 |
| en / tur (Türkiye) | 0 | 43 | 8,581 |
| de / deu (Almanya) | 0 | 473 | 9,854 |
| ru / rus (Rusya) | 0 | 60 | 9,150 |
| pl / pol (Polonya) | 2 | 54 | 10,574 |
| pt / bra (Brezilya) | 0 | 69 | 8,304 |
| fa / irn (İran) | 0 | 31 | 5,968 |

Bu kırılımdaki TR öncelikler: fırın ön ısıtma yazısı 97 gösterim/0 tıklama,
Samarella yazısı 67/2, fırın dereceleri 49/0. EN: Kilis cennet çamuru
88/0, kıymalı bezelye 57/0, bidda badadez yazısı 26/0. Bunlar yeni yayın
kararı değildir; revizyondan önce mevcut kanonik URL ve kaynak içerik
kalitesi doğrulanmalıdır. Özellikle İngilizce tarif ölçümleri eski
/en/tarifler/... büyük harfli URL'lere aittir.

## Medya ve dil kapsamı

- 08:38:22 UTC PublicMediaEligibilityService: 534 published/verified,
  139 uygun medya, 395 eksik. Önceki 135/399 ölçümü güncel diye kullanılmadı;
  artış bu turun teslimi veya onayı olarak sunulmuyor.
- Kuyruk 15 tamamlanan, 343 pending, 6 blocked, başkasına ait 2 claimed;
  Automation-Images-20260916 aktif işi 0. Sıradaki HQ1AZ kuyruk slug'ı
  canlı hq1az ile eşleşmediğinden yeni üretim/claim yok.
- tr/en/de/fa/pl/ru/pt etkin ayarları yeniden okundu, değiştirilmedi.
  Her dil için içerik, arayüz, tarif adımı, malzeme, canonical, karşılıklı
  hreflang, sitemap ve schema kapsamının tam hazırlığı bu tur test edilmedi.
  Eksik çeviri hazır veya yayınlanabilir sayılmadı. Blog/çeviri taslağı,
  medya onayı veya içerik yayını yapılmadı.

## 14:31 UTC canlı kapanış

- Canlı sürüm c34134ab7ff3ecd44d6b476c8f227625b3d6f2ca; başka ajanın
  yeni sürümü 325ecea düzeltmesini içeriyor. Tekrar deploy yapılmadı.
  BacklinkVerificationService, config/seo.php ve SeoBacklinksVerify canlı
  SHA256 değerleri test edilen yerel dosyalarla birebir aynı.
- Canlı yapılandırma günlük 20; bugün 14 kontrol kayıtlı. Test için yalnız
  CLI sürecinde limit0, RAM cache ve fake HTTP kullanıldı: deferred,
  reason=daily_limit, checked0, HTTP0, retry_at 28 Eylül 00:00 UTC.
  Üretilen test logu DB transaction rollback ile geri alındı; kalıcı
  ayar veya aday kaydı değiştirilmedi, yayıncılara istek gönderilmedi.
- Scheduler son queue_drain işi 14:31:22 UTC healthy. Bu, diğer tüm
  görevlerin tek tek doğrulanması değildir.
- Measurements servisleri, beş dil JSON'u, birimler/hazırlık çeviri
  dosyaları ve ForeignRecipePagesSpeakTheirLanguageTest başka ajanın
  sürmekte olan çalışmasıdır; dosyalar ve test süreçleri korunmuştur.
- Bu oturumda yalnız dry-run yayın ön kontrolü yapıldığından yeni yayın
  paketi yok; başkasının ara çıktıları silinmedi. GLOB-016 kapatıldı.
