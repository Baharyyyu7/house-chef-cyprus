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

## 2026-09-28
- Ölçüm: TR 37 tıklama / 3832 gösterim (pos 9.54), EN 7 / 1413 (pos 9.68). Dün referans yok.
- Google hata düzeltme: 43 sayfa "yayında değil" raporlanıyordu.
  - Blog nüshaları (-B253B, -D8VCZ, -3Y13B) → SlugRedirect ile canonical'a 301.
  - Geri kalan draft/verified tarifler (lahmacun, kazandibi vb.) → publish edilmedi; içerik kalite review bekliyor.
- Bugün yapılan SERP:
  - #163 TR firin-kac-dakikada-isinir: başlığa "180°C 10-15 dk, 200°C 12-17 dk" eklendi (468 göst / 0 tık)
  - #164 TR firin-dereceleri-rehberi: "Kek 170°C, Ekmek 200°C, Börek 180°C" formatına geçildi (324 göst / 0 tık)
  - #165 TR kuru-maya-yas-maya: "1 küp (42g) yaş = 14g kuru maya" öne çıkarıldı (167 göst / 0 tık)
  - #166 EN types-of-turkish-borek: başlığa somut varyant isimleri eklendi (80 göst / 0 tık / 5. sıra)
- Skills: Nicholas Dulait'in 6 SEO skill'i eklendi (money-keywords, page-brief, ship-page, geo-rankings, pattern-drip, weekly-seo) → commit bf59ea0, deploy bekleniyor (release.sh PID 73387).
- Nüshalar: 5 canonical çift mevcut — düzeltme kuyruğunda değil, izleniyor.
- Bekleyen: #163-166 → 2 hafta sonra tıklama ölçümü. Draft tarifler (lahmacun vb.) için kalite incelemesi.

### 2026-09-28 (devam — GEO + backlink)
- GEO analizi (EN):
  - "best Cypriot food 2026" → artandthensome.com, continenthop.com, cyprustravelhub.com öne çıkıyor. Bizim adımız hiçbirinde yok.
  - "best halloumi recipe 2026" → HelloFresh, Olive Magazine, Scrummy Lane. Bizim adımız yok.
  - Önerilen başlık değişiklikleri: meze rehberi "Best Cypriot Meze 2026" formatına — page-brief üzerinden geçirilecek.
- Backlink adayları (EN): 4 aday → docs/claude-coni/outreach/2026-09-28-en-backlink.md
  - whatsincyprus.com (info@whatsincyprus.com)
  - artandthensome.com (Alissa Walker, sosyal medya)
  - scrummylane.com (Helen Schofield, About sayfası)
  - continenthop.com (@laviniadsouza)
  Taslak metinler hazır. **Gönderme kararı kullanıcının.**
- SERP (EN devam):
  - #167 EN halloumi-vs-feta-vs-paneer: "Feta vs Paneer vs Halloumi" sırası düzeltildi (87 göst / 0 tık / sıra 5-7)
  - #168 EN cacik-vs-tzatziki-vs-talattouri: soru formu eklendi (40 göst / 0 tık)
- AdSense: Kullanıcı yeniden inceleme isteği gönderdi (2026-09-28). 1-2 hafta bekleniyor.

## 2026-09-28 (devam)
- Çeviri: 10 tarif × 6 dil = 60 aday, 58 yazıldı/açıldı, 2 zaten gerçek çeviriydi (atlandı).
  Açılan sayfalar: yesil-mercimek-salatasi (86 EN gösterim), yogurtlu-pogaca (39), eskisehir-cig-borek (31),
  babaganus (26), yayla-corbasi (26), kibris-kleftiko (22), kirmizi-mercimek-corbasi (22),
  yesil-mercimek-corbasi (22), supangle (22), katmer (18 gösterim).
- Bekleyen: kalan ~46 tarif (sıradaki 10 impression listelenmeli).
