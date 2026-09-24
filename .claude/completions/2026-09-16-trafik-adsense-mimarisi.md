# Trafik + AdSense mimarisi — OpenCode doğrulama raporu (2026-09-16)

Görevdeki 3 özellik eşzamanlı ajan tarafından uygulanıyordu; aynı dosyalara
dokunmamak için rolüm **inceleme + doğrulama + kritik düzeltme** oldu.

## Durum: 3/3 uygulandı (diğer ajan, commitlenmedi)

1. **Zamanlayıcı:** adım butonu (`recipes/show.blade.php` adım döngüsü,
   `StepDurationParser` minutes + `duration_seconds`) → `hc-start-timer`
   olayı → `recipes/partials/cook-timer.blade.php` yapışkan sayaç
   (`hcCookTimer`, `resources/js/app.js:238`), WebAudio bip, duraklat/sıfırla.
2. **Oylama:** `POST /tarifler/{slug}/oyla` (`RecipeRatingController`,
   throttle 10/1, üyeliksiz, `hc_voter` çerez + IP HMAC, güncellenebilir tek
   oy) → `Recipe::ratingSummary()` → `aggregateRating` şemada (oy yoksa
   basılmaz) → `recipes/partials/rating.blade.php` yıldız widget'ı.
3. **GEO kutusu:** görünür `#tips` bölümü (tips + common_mistakes +
   ikameler, `show.blade.php:158-160`) + şemada `Comment` düğümleri
   (`SeoSchemaService::recipe()`, en fazla 20, görünen metnin aynısı).

Altyapı: `recipe_ratings` migration
(`2026_09_16_160000_create_recipe_ratings_table.php`), 7 dile anahtarlar,
5 yeni test dosyası (30 test).

## Benim değişikliklerim (2 dosya)

- `app/Services/Seo/SeoSchemaService.php:265,287` — `$recipe->exists`
  guard'ı: kaydedilmemiş tarifte ratings/ingredients tablosu sorgulanmaz.
  Gerekçe: tam süit 1371/1372 idi, tek hata `GlobalSeoMetadataTest` (DB'siz
  in-memory tarif → `no such table: recipe_ratings`). Sahibinin dosyasına
  minimal dokunuş; diff'te görünür.
- `tests/Feature/RecipeSchemaWithoutDatabaseTest.php` (yeni) — guard
  regresyon testi. Çakışmasız (yeni dosya).

## Doğrulama

- Yeni 5 test dosyası: 30/30 geçti (çalıştırıldı).
- Hedefli: GlobalSeoMetadata + Rating + GeoCallout 17/17 geçti.
- Tam süit: 1372 test, 1371 geçti + 1 guard hatası → düzeltildi; tam süit
  tekrarı arka planda koşuyor, sonucu bekleniyor.

## Açık güvenlik bulgusu (düzeltmedim — sahibinin dosyası)

`bootstrap/app.php`: `validateCsrfTokens(except: ['tarifler/*/oyla'])`.
Widget zaten `X-CSRF-TOKEN` gönderiyor, muafiyet gereksiz; çerez kimliğiyle
birleşince siteler-arası oy manipülasyonuna kapı aralar (sayı şişirme
throttle ile yavaşlar ama durmaz). Öneri: muafiyeti sil, widget token
gönderiyor zaten; `RecipeRatingTest` POST'larına `_token` ekle. Testler şu
an muafiyete bağımlı (tokensuz POST → 200 bekliyor).

## Yapılmayanlar

Commit/push/deploy yok: çalışma diğer ajanın commitlenmemiş WIP'i; yeşil
süit + onların commit'i beklenmeli. Ücret harcanmadı, görsel indirilmedi,
`locales.php` enabled alanına dokunulmadı.
