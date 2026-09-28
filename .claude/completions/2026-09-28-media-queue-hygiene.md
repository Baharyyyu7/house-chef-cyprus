# Üç geçersiz görsel işi gerekçeli olarak ayrıldı

27 Eylül 20:31 heartbeat tetiklemesi; gerçek canlı okuma 28 Eylül
05:21:58 UTC. Yerel 9c169fe, canlı bd7ffb4. Eski main-transition kaydı
bu dalda yok; oluşturulmadı. CategoryHubController, CuisineHubController,
home şablonları ve HomeLinksOnlyToHubsThatExistInThatLocaleTest başka
ajanda; korunmuştur. Yeni uygulama kodu veya deploy yok.

## İşlem

| Claim'in tam slug'ı | Canlı ID | Canlı exact slug | Gerekçe |
| --- | ---: | --- | --- |
| kibris-usulu-pisi-lokisi-HQ1AZ | 1391 | kibris-usulu-pisi-lokisi-hq1az | draft, slug farklı |
| geleneksel-tahinli-V9X0W | 833 | geleneksel-tahinli-v9x0w | draft, slug farklı |
| kibris-usulu-TEAG6 | 1425 | kibris-usulu-teag6 | draft, slug farklı, yemek adı eksik |

Her kaydın public_id'si yerel kuyrukla canlıdan eşlendi. Sırasıyla
media:queue claim --worker=Automation-Images-20260916 ve aynı worker ile
block --slug --reason kullanıldı. Claim çıktıları preflight ile tekrar
eşlendi. Tarif, canlı slug veya yayın durumu değiştirilmedi.

"Kıbrıs Usulü" başlığında titleDescribesADish true dönüyor; bu tek başına
gerçek yemek kimliği değildir. İnsan tarafından anlaşılabilir yemek adı
eksik olduğu için üretim yapılmadı ve block gerekçesine yazıldı.

## Doğrulama

- Kuyruk unit+feature: 8 test/42 assertion geçti. AI-Worker-12 bağımsız
  salt okunur inceleme: claim/block sahip kontrolü, otomatik kilit
  devralmama ve atomik yazım için engelleyici bulgu yok.
- Önce 343 pending/6 blocked; sonra 340 pending/9 blocked. Completed15
  ve başkasına ait claimed2 aynı. Kendi aktif kilidi0.
- AI-Worker-4 kibris-usulu-molohiya-0TZW4 (16 Eylül13:31:51 UTC) ve
  Antigravity kibris-usulu-pisi-lokisi-AVVJ1 (25 Eylül13:24:27 UTC)
  kilitlerinin sahibi/tarihi korundu. Yerel flock başka bilgisayarların
  bağımsız kuyruklarına ortak kilit sağlamaz.
- 05:21:58 UTC PublicMediaEligibilityService: 134 published/verified,
  116 uygun medya, 18 eksik. Bu tur görsel üretilmedi, teslim edilmedi
  veya onaylanmadı. Eski kuyruk toplam366 güncel eksik sayısı değildir.
- Scheduler son queue_drain 05:21:27 UTC healthy; bütün görevler için
  toplu sağlık sonucu çıkarılmadı.
- GSC snapshot: sc-domain:housechefcyprus.com, api_import/web,
  2026-08-28 - 2026-09-24, kaynak gözlemi28 Eylül00:00:07 UTC,
  okuma05:21:58 UTC; 72 tıklama/6.333 gösterim/9,871151 konum.
  Yeni Google API isteği veya dil/ülke kırılımı alınmadı. 27 Eylül
  page+country kırılımı farklı dönemdir ve yeni toplamla birleştirilmedi.
- config/locales.php canlı tr/en/de/fa/pl/ru/pt etkin; değiştirilmedi.
  Dil başına içerik/arayüz/adım/malzeme/canonical/hreflang/sitemap/schema
  hazırlığı yeniden doğrulanmadı; tam hazır iddiası yok.
- Yeni backlink adayı, iletişim, doğrulanmış bağlantı, içerik taslağı
  veya yayın yok. GLOB-013 kuyruk yenilemesi ve GLOB-014 ölçüm kaynak
  sorunu açık kalıyor. Tam uygulama süiti bu operasyon için tekrarlanmadı.
