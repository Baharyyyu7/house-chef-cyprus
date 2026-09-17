# SEO/GEO Fırsat Haritası — TR çekirdek + EN genişleme (2026-09-17)

Kaynak: canlı örneklem taraması + EN SERP örneklemi (Eyl 2026). Örneklem
konuma göre değişir; kesin pozisyon iddiası yok.

## TR çekirdek (hub tek kanonik)

| Sorgu niyeti | Kanonik URL | Durum |
|---|---|---|
| kıbrıs yemekleri / kıbrıs yemek / yemek kıbrıs / kıbrıs yemek tarif | `/mutfaklar/kibris-mutfagi` | Hub canlı: top liste, FAQ, ItemList, 5 varyant 301 |
| kıbrıs usulü + yemek | ilgili `/tarifler/{slug}` | 957 tarif, Recipe şeması görselli sayfalarda |
| İlk sayfa rakipleri | nefisyemektarifleri, lezzet.com.tr, yemek.com, tatilbudur | Liste formatı 12-20 tarif + video + güncellik tarihi |

## EN fırsat sıralaması (zayıftan güçlüye)

| Öncelik | Sorgu | Neden | Hedef URL |
|---|---|---|---|
| 1 | kolokas recipe | Dev yok, UGC/zayıf bloglar | EN kolokas tarifi (çeviri gerekli) |
| 2 | Cyprus breakfast | 2015 tarihli rakipler, tarif açığı | EN kahvaltı hub + tarifler |
| 3 | sheftalia recipe | Niche bloglar, BBC/Allrecipes yok | EN şeftali kebabı tarifi |
| 4 | flaouna recipe | Tek güçlü rakip (BBC Good Food) | EN flaouna tarifi + video |
| 5 | what to eat in Cyprus | Wiki/.gov güçlü ama tarif devi yok | EN hub (bu turda başlık/SSS EN-leşti) |
| SONRA | kleftiko recipe | BBC + Mediterranean Dish + MyGreekDish | — |
| KAÇIN | halloumi recipes | NYT, RecipeTin Eats (video), BBC, Olive | — |
| KAÇIN (şimdilik) | Cypriot recipes (genel) | Yerleşik hub'lar | Önce uzun kuyruk |

## Bu turda yapılan (kod lane)
- EN Kıbrıs hub: `Cypriot Recipes – Traditional Cyprus Food` başlık/H1/açıklama
  + 3 İngilizce SSS (`CuisineHubController`, `lang/en.json`). TR dalı değişmedi.
- Sitemap hreflang alternates doğrulandı (EN keşfi çalışıyor, ek iş yok).
- Testler: `EnglishCyprusHubTest` (3), `CuisineLandingPagesTest` EN yolu
  `/en/cuisines/...` güncellemesi.

## Lane'lere iş emri
- **Çeviri (Codex)**: kolokas + şeftali kebabı + flaouna + kahvaltı tarifleri
  EN çevirisi öncelikli (EN hub 14/91). `content:translation-readiness`.
- **Görsel (Antigravity)**: aynı tariflerin görselleri (zengin sonuç şartı).
- **i18n (aktif)**: `path_aliases` URL migrasyonu — §35 kontrol listesi
  (hreflang/sitemap/canonicalRedirect matrisi) o lane'de takip edilmeli.
- **İnsan**: GSC OAuth, IndexNow doğrulama, outreach gönderimleri, yayın onayları.
