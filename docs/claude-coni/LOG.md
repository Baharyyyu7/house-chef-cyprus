# Claude Coni defteri

Her çalışma sonuna bir girdi eklenir; eski girdi silinmez ve değiştirilmez.
Ayrıntılı durum üretimde: `php artisan coni:durum --json`.

## 2026-09-24 (kuruluş)

- **Ölçüm (28 gün, Search Console, sayfa bazında):**
  tr 35 tık / 2963 gös (368 sayfa) · en 10 / 1036 (186) · de 5 / 826 (135) ·
  ru 2 / 380 (110) · pt 2 / 157 (55) · fa 9 / 145 (69) · pl 4 / 142 (66).
  7 günlük toplam 52 tık / 4419 gös, TO %1,18, sıra 9,66.
- **Ölçüm kusuru düzeltildi:** `seo:daily-growth-report` "önceki"yi en son
  rapordan alıyordu; görev 07:05'te iki kez koştuğu için 37 saniye önceki
  koşuyla karşılaştırıp her sabah "aynı" diyordu. Artık önceki güne bakıyor.
  Sayfa sorgusu satır sınırı 1000 → 5000 (28 günlük liste kesiliyordu).
- **İçerik kusuru bulundu:** yabancı dil tariflerinin adım çevirileri kalıp
  (her dilde 588/588). ~580 indekse açık sayfa. `RecipeTranslationGate` ile o
  dilde noindex + site haritası dışı. Gömme (`/embed/`) sayfaları dizine
  açıktı ve kanonikleri 301 veren eski yazıma gidiyordu: noindex + doğru kanonik.
- **Gözlem:** yabancı dil sayfalarına gelen gösterimin büyük kısmı Almanya,
  Avusturya, Danimarka, Bulgaristan'dan Türkçe aramalar ("kapya biber" 198,
  "cennet çamuru", "bezelye", "zeytinyağlı taze fasulye"). Başlıkta Türkçe
  yemek adı kalmalı. TR fırın sayfaları (352 ve 254 gösterim, sıra ~8, 0 tık)
  cevabı arama sonucunda verilen sorgular; başlık zaten sayı içeriyor, kaldıraç
  sıra.
- **Bugün yapılan:** Claude Coni kuruldu (defter, günlük döngü 07:20, iç
  bağlantı kaldıracı, SERP/çeviri komutları, geri alma). Deploy sonrası
  ilk işler bu girdinin altına eklenecek.
- **Bilinen eksik:** sayfa başına üye kaydı ölçülmüyor; reklam geliri sayfa
  başına ölçülmüyor. Dönüşüm şimdilik reklam taşıyan sayfaya gelen tıklama.
