# Search Console sayfa sorgularında Retry-After

- Başlangıç yerel/canlı: visual-redesign-v2, fc7cc3f. Önceki Soğanlı
  Çörek teslim kayıtları e52ddf6 içinde commit edilmiş; yeniden alınmadı.
  Başkasının .claude/settings.local.json ve izlenmeyen skill dosyaları
  bu işe dahil değil. Header/home dosyalarına dokunulmadı.
- Sorun: pageRowsBetween/pageQueries 429 veya 503 sonrası ortak bekleme
  kaydını oluşturuyor fakat sonraki çağrıda okumuyordu. İki girişte,
  OAuth öncesinde importApi ile aynı cache koruması eklendi. pageRows
  aynı metoda yönlendiğinden korumayı kullanır.
- Regresyonlar: OAuth 429 + saniye, analytics 503 + HTTP tarihi;
  bekleme süresinde sıfır ek dış istek; süre bitiminde normal dönüş.
  İlk koşuda iki senaryo API_REQUEST_FAILED ile tekrar isteği gösterdi.
  Düzeltme sonrası ilgili 18 test, 72 assertion geçti. PHP sözdizimi ve
  git diff --check temiz. AI-Worker-12 bağımsız inceleme: bulgu yok.
- Tam süit logu: /tmp/housechef-20260926-gsc-backoff-full.log;
  JUnit: /tmp/housechef-20260926-gsc-backoff-full.xml. Tam koşu 1.661
  test/9.166 assertion ile geçti; 0 error, 0 failure, 846,807 saniye.
  JUnit'te iki yeni backoff senaryosunun da yer aldığı doğrulandı.
  Kod commit'i f0cbaac. Yayın scriptinde --skip-tests kullanımı bu
  tamamlanmış tam koşuya dayanır; test atlanmış olarak yorumlanmamalı.
- Yeni görsel işi alınmadı: sıradaki kuyruk slug'ı
  kibris-usulu-pisi-lokisi-HQ1AZ, 06:55:42 UTC canlı exact slug
  kibris-usulu-pisi-lokisi-hq1az. GLOB-013 eski kuyruk sorunu açık.
  Worker Automation-Images-20260916 açık işi 0; diğer iki kilit korundu.
- 06:55:42 UTC PublicMediaEligibilityService: 534 published/verified,
  135 uygun medya, 399 eksik. Bu yeni bir görsel teslimi sayısı değildir.
  Soğanlı Çörek teslim dosyası 1920x1620, 4.372.088 bayt, SHA256
  e3d4c12ff84458fd4d0e119130e605696051f49743edcc04269dfea7eef511cf
  ile sunucuda tekrar doğrulandı. İnsan onayı verilmedi.
- 17:02:59 UTC kayıtlı GSC okuması: sc-domain:housechefcyprus.com,
  api_import/web, 2026-08-26 - 2026-09-22, gözlem 06:45:32 UTC,
  58 tıklama/4.908 gösterim/9,925835 konum. Yeni Google isteği yok.
  Dil/pazar verisi bu tur alınmadı; GLOB-014 ayrı açık kayıt.
- tr/en/de/fa/pl/ru/pt etkin ayarları okunup korundu. Bu durum dil başına
  içerik/arayüz/adım/malzeme/canonical/hreflang/sitemap/schema hazırlığı
  kanıtı değildir; bu kapsamlar yeniden doğrulanmadı. Blog, backlink
  iletişimi, backlink doğrulaması veya içerik yayını yapılmadı.
- Yerel storage 2,0 GB; önceden var olan build-artifacts 51 MB. Bu işte
  üretilmedikleri için başka ajanın ara dosyaları silinmedi.

## Canlı doğrulama - 26 Eylül 23:05 UTC

- f0cbaacb0e47cace8859bff57e9f7d7df71786ea normal release.sh akışıyla
  yayımlandı; script exit 0. Dokuz uç nokta canlı smoke kontrolünden geçti;
  /, /tarifler, /blog, /health ayrıca HTTP 200 doğrulandı.
- 23:05:51 UTC sunucu CLI sürecinde yalnız RAM'deki array cache ve
  fake HTTP kullanılarak bekleme anahtarı oluşturuldu. pageRowsBetween,
  pageRows ve pageQueries boş döndü; toplam dış istek 0. Üretim cache'i
  ve gerçek Google API'si değiştirilmedi/çağrılmadı.
- Scheduler son queue_drain işi 23:05:23 UTC healthy. Bu sonuç tüm
  otomasyonların ayrı ayrı çalıştığı iddiası değildir.
- /tmp/hc-release-f0cbaac ve içindeki bu sürüme ait paket kaldırılmış
  olarak kontrol edildi. Başkasına ait 51 MB eski build-artifacts kaldı.
- Release logu: /tmp/housechef-20260926-gsc-backoff-release.log.
  GLOB-015 kapandı; GLOB-013 kuyruk kimliği ve GLOB-014 veri kaynağı/tarih
  sorunları açık. Bu tur yeni görsel claim veya içerik yayını yok.
