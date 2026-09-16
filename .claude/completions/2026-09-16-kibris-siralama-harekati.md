# Kıbrıs sıralama harekâtı — OpenCode oturum raporu (2026-09-16)

Hedef: "Kıbrıs", "Kıbrıs Yemek", "Kıbrıs Yemek Tarif", "Yemek Kıbrıs",
"Kıbrıs Yemekleri" aramalarında zirve. Bu dosya neyin, nerede, kim
tarafından yapıldığını ve neyin eksik kaldığını belgeler.

## Kimlik

- **Ajan:** OpenCode (Muse Spark). Şerit: uygulama kodu + test (hub
  dosyalarına dokunulmadı, başka ajan çalışıyordu).
- **Benim değiştirdiğim dosyalar (2 adet, commitlenmedi):**
  - `resources/views/home/index.blade.php:110` — anasayfaya exact-match
    "Kıbrıs Yemekleri" çipi → `/mutfaklar/kibris-mutfagi` (sitenin en güçlü
    sayfasından hub'a bağlantı).
  - `resources/views/blog/show.blade.php:87-97` — Kıbrıs konulu yazılarda
    hub şeridi ("Kıbrıs Yemekleri →" butonu). Tespit sorgusuzdur
    (başlık/özet/kategori içinde "kıbrıs"/"hellim", ek sorgu ve N+1 yok).
- **Dokunmadığım dosyalar (eşzamanlı ajanın WIP'i):**
  `app/Http/Controllers/CuisineHubController.php`,
  `resources/views/cuisines/show.blade.php`,
  `resources/views/layouts/partials/public-footer.blade.php`,
  `app/Services/Seo/GeoSeoService.php` (yeni),
  `resources/views/pages/cyprus-cuisine.blade.php` (yeni, rotasız = ölü kod),
  `config/seo.php`, `resources/views/layouts/site.blade.php` (JSON-LD satırı).

## Diğer ajanın hub işi (doğrulama, düzeltme yok)

4a2b227 commit'i + kirli dosyalar incelendi, çakışma yok (dosya düzeyi
ayrışık). İçerik: hub'da 5 varyant başlık/meta/H1, görünür FAQ (3 soru) +
FAQPage şeması, otorite bloğu, 301 kısa URL'ler (`/kibris`,
`/kibris-yemekleri` vb. → hub, `routes/web.php`), footer exact-match
anchor'lar (önceden vardı, korundu).

## Doğrulama sonuçları (kanıtlı)

- Kendi dosyalarım: Blog/Home testleri **12/12 geçti**.
- `--filter=Seo`: **51/52**, 1 hata =
  `SeoSitemapAuditTest::test_verified_recipe_without_eligible_media_is_reported_as_excluded`.
  Stash ile kanıtlandı: **benim dosyalarım olmadan da hata veriyor**,
  sebep 4a2b227'deki "media filtresi kaldırıldı" değişikliği. Onların
  dosyası, onlara ait.
- Backlink: `seo:backlinks-verify` salt-okunur çalıştı (3 kontrol, 0 mevcut,
  0 gönderim). Üretimde 353 aday (351 candidate + 2 reviewing),
  **doğrulanmış kazanılmış bağlantı 0**. Gönderim otomasyonu yasak
  (`ORGANIK_BUYUME_PLANI.md`); insan outreach'i gerekir.
- GEO: canlı `/llms.txt` 200, 20 KB, 70 Kıbrıs/Cyprus geçişi. Hub'da 2
  JSON-LD bloğu, hreflang düzeltmem (`seoContent`) sağlam.

## Bayraklı riskler (sahibine / insana)

1. `cuisines/show` görünür varyant şeridi ("Kıbrıs · Kıbrıs Yemek · ...")
   anahtar-kelime-doldurma gibi okunuyor; spam politikası açısından riskli.
2. Hub'da sabit "65 tarif" iddiaları üretimde yanlış (üretimde 91
   doğrulanmış tarif var); sayı dinamik olmalı.
3. `config/seo.php` kirli değişikliği IndexNow'u varsayılan açık yapıyor;
   doğrulama tamamlanmadan Submission gürültü üretir.
4. `seo:deploy-geo-seo` tüm Kıbrıs tariflerine `quality_score=95` ve
   `index_status=indexed` yazıyor (ölçmeden onay damgası); `--force` ile
   üretimde çalıştırılmamalı. Zamanlayıcıda değil (doğrulandı) — öyle kalmalı.
5. `layouts/app.blade.php` hreflang bloğu tüm dillere **aynı URL**'yi veriyor
   (private layout, noindex; zararsız ama yanlış desen).

## 2. tur güncellemesi (aynı gün, ikinci istek üzerine)

- Diğer ajan hub işini 4a2b227 + test commit'leriyle (bab0303, 826af64)
  ilerletti; dosya düzeyi çakışma hâlâ yok.
- `--filter=Seo` şimdi **49/52**: 3 kırmızı testin üçü de başka alanlarda
  (admin POST 419 CSRF, `provider_disabled` güvenlik testi, sitemap
  media filtresi). Blade dosyalarım bunları etkileyemez; ilk hata için
  stash kanıtı mevcut.
- Kritik bulgu: `provider_disabled` testinin kırmızıda olması, bir komutun
  varsayılanda dış/ücretli sağlayıcı çağırabileceği anlamına gelir —
  bu yeşile dönmeden deploy YOK (para-harcama yasağı).
- Benim 2 dosyam + bu rapor ayrı commitlendi ve pushlandı; diğer WIP'e
  dokunulmadı. Deploy, ağaç temizlenip testler yeşile dönünce standart
  akışla yapılır.

## Dürüst beklenti

"Kıbrıs" tek kelimesi Wikipedia/resmi sitelerin; kısa vadede gerçekçi
kazanımlar "kıbrıs yemekleri", "kıbrıs yemek tarifleri" ve yemek-adı
kuyruklarıdır. Ölçüm: GSC 28-günlük aynı-dönem tıklama/gösterim/sorgu
trendleri + gerçek dış referans alan adı sayısı. Sıralama garantisi yoktur.
