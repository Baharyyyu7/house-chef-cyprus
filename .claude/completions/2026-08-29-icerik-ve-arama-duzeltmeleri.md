# İçerik bütünlüğü ve arama düzeltmeleri - 2026-08-29

## 1. Yayındaki tariflerin %27'si pişirilemezdi

176 "yayında + doğrulanmış" tarifin 47'si gerçek tarif değildi.

**34'ünün malzeme listesi birebir aynıydı:**
```
500 g Ana Malzeme        ← asıl malzeme hiç yazılmamış
4 yemek kaşığı Zeytinyağı, 1 adet Kuru Soğan, 2 diş Sarımsak,
1 tatlı kaşığı Tuz, 1 çay kaşığı Karabiber
```
Kumpir, kokoreç, tantuni - üçü de aynı altı satır.

**13'ünde malzemeler tarifle çelişiyordu.** "Mercimek Çorbası" kırmızı et
ve balık istiyordu. Malzeme ID'leri incelendiğinde sebep çıktı: aynı
dönen pencere tekrarlıyor (Havuç/Limon/Zeytinyağı/Pirinç →
Bulgur/Makarna/Un/Yumurta → Süt/Yoğurt/Peynir). Malzemeler tarife göre
değil sırayla atanmış.

Adım metinleri gerçek göründüğü için 2026-08-28'deki yer tutucu
taramasından kaçmışlardı. Yayından alındı (silinmedi): **176 → 129**.
Tekrar tarandı: kalan çelişki 0, kalan yer tutucu 0.

Kolokas tarifi yayında bırakıldı: tarif doğruydu (kolokas, kuzu eti,
kereviz sapı), yanlış olan başlıktı ("tavuklu"). Kıbrıs usulü kolokas
geleneksel olarak kuzuyla yapılır; başlık düzeltildi.

Kullanılmayan ölçüm: "ardışık malzeme ID'si" 55 tarif işaretledi ama
doğru tarifler de ardışık ID taşıyor (malzemeler sırayla oluşturulmuş).
Güvenilmez olduğu için raporlanmadı.

## 2. Yapısal sorgular sıfır sonuç dönüyordu

Ziyaretçiler aradı, hiçbir şey bulamadı - oysa üçünün de karşılığı sitede
vardı:

| sorgu | arama | düzeltme sonrası |
|---|---|---|
| 20 dakikada | 25 | 16 tarif |
| kıbrıs mutfağı | 21 | 16 tarif |
| kolay tarifler | 18 | 24 tarif |

Sebep: sorgu doğru biçimde yapısal filtrelere çözülüyordu ama arama
ayrıca ham metnin başlıkta geçmesini şart koşuyordu. Hiçbir tarifin
başlığında "kıbrıs mutfağı" ifadesi geçmediği için sıfır dönüyordu.
Anlamsız sorgu (`asdfqwer`) hâlâ sıfır dönüyor.

## 3. Zorluk alanı iki dilde saklanıyordu

176 tarifin 112'si `Kolay/Orta/Zor`, 64'ü `easy/medium/hard`. İki özellik
kataloğun ayrık yarılarını görüyordu: herkese açık filtre Türkçe
değerleri, kişiselleştirme profili İngilizce değerleri sorguluyordu.
Kesişimleri boştu. Ayrıca tarif sayfasında `ucfirst()` kullanıldığı için
64 tarifte Türkçe sitede "Easy" yazıyordu.

Kanonik biçime çekildi (İngilizce, gösterim Türkçe). Üretimde 113 kayıt
normalleştirildi. Eski `?difficulty=Kolay` bağlantıları çalışıyor.

## 4. Tek seferlik aramalar içerik önerisi oluyordu

Eşik yoktu; beş "fırsat"tan ikisi `functionalities` (1 arama) ve
`search term string` (2 arama) idi. Eşik eklendi
(`seo.audit.minimum_zero_result_searches`, varsayılan 5). Mevcut bir test
bu davranışa dayanıyordu; eşik zayıflatılmadı, test eşiği aşacak şekilde
güncellendi.

## 5. 1.977 macOS çöp dosyası silindi

`._` AppleDouble metadata dosyaları (315 KB). Yetim tarayıcı çıktısının
yarısını dolduruyor ve gerçek sinyali okunmaz kılıyordu. 3.800 → 1.823.

## Doğrulama

- Test: 820/820 (temiz worktree, varlıklar derlenmiş)
- Uç noktalar: `/`, `/tarifler`, `/blog`, `/malzemeler`, `/health`,
  `/sitemap.xml` → 200
- Zorluk etiketi canlıda Türkçe
- Zamanlayıcı taze, kuyruk boş (pending 0, failed 0)
- SEO denetimi: 1 açık sorun, skor 90
- Yedek: `01M16DE510SN0R7B3HYF0DYYT4`

## Açık kalan

- 86 taslak tarif (39 + 47) - düzeltilmeyi ya da silinmeyi bekliyor
- 1 taslak blog yazısı (mercimek çorbası) - baştan yazılmalı
- 1.823 sahipsiz dosya (227 MB) - silinmedi; geri alınamaz ve aciliyeti yok
- Private Chef sayfası - hizmet doğrulanamadığı için onaylanmadı
- Hostinger cron destek kaydı, Search Console bağlantısı - sahibinde
