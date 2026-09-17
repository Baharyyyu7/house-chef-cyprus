# 2026-09-17 — Trafik Patlaması Faz 1: zamanlayıcı, oylama, GEO kutusu

## Bağlam
Paralel ajan F1+F2+F3 iskeletini çalışırken kurdu
(`StepDurationParser`, `RecipeRating*`, `cook-timer`/`rating` partial'ları,
`aggregateRating`, görünür ikame kutusu). Aynı dosyalara çift implementasyon
yazılmadı; onunkiler devralınıp eksikler kapatıldı.

## Benim değişikliklerim
- `app/Services/Seo/SeoSchemaService.php`: `comment` düğümündeki
  `$kalem->pivot->substitution` → `$kalem->substitution`
  (`recipeIngredients` HasMany'dir, pivot yoktur; ikameler şemaya hiç
  girmiyordu). `pivot->group_name` → `$kalem->group_name`. Sessiz veri
  kaybıydı, crash yoktu (`??` yutuyordu).
- `routes/web.php`: kayıp `use ...RecipeRatingController` importu eklendi
  (route kısa isimle kalmıştı, endpoint 500 veriyordu).
- `app/Http/Controllers/RecipeController.php`: kendi ölü `$stepTimers`
  denemem geri alındı (blade diğer parser'ı kullanıyor).
- Yeni testler: `StepDurationParserTest` (9), `RecipeRatingTest` (7),
  `RecipeInteractiveElementsTest` (7).

## Test bulguları (kalıcı notlar)
- `postJson` çerezleri yalnız `withCredentials()` ile gönderir; şifreli
  çerez gidiş-dönüşü `getCookie(..., false)` + `withUnencryptedCookie` ile
  test edilir.
- Yeni dosya sonrası `composer dump-autoload` gerekir (yoksa 500).
- Tam süit (`php artisan test`) paralel yük altında takılıyor (20+ dk,
  sıfır çıktı); filtreli süitler hızlı ve yeşil.

## Doğrulama
- Yeni 3 dosya: 30/30 yeşil.
- `Seo|Schema|Home|Blog|Cuisine|Collection`: 240/240.
- `Media|Recipe`: 386/386. `Unit`: 13/13.

## Açık / başka lane
- `aggregateRating`, görselsiz tarifte jenerik fotoğrafla basılan Recipe
  düğümüne biniyor (medya lane kararı; BACKLINK_STRATEJISI.md uyarısı
  geçerli: olmayan görseli iddia etmek risklidir).
- GSC/IndexNow credential adımları, 363 görsel kuyruğu: insan + görsel lane.

## Devam oturumu: WebMCP, ajan güvenlik kapıları ve Private Chef

- WebMCP/ajan araçları ve Private Chef landing page zinciri mevcut çalışma
  ağacından devralındı; branch ve origin aynı noktada.
- `LocaleSwitchNavigationTest`, dil seçicinin POST formundan kanonik `href`
  bağlantılarına geçişini doğrulayacak şekilde hizalandı.
- `AgentToolController` içindeki tarif, porsiyon, pişirme adımı ve favori
  araçları yalnız `published` + `verified` tarifleri kullanıyor.
- `save_recipe` kimlik doğrulamasına ek olarak `confirmed: true` istiyor;
  API ve tarayıcı WebMCP şemaları bu alanı zorunlu olarak belgeliyor.
- Private Chef `/private-chef` sayfası, `/ozel-sef` 301 yönlendirmesi, iletişim
  hattı, sitemap kaydı ve FoodService/FAQPage şeması doğrulandı.

## Doğrulama

- Ajan, dil, tarif etkileşimleri, SEO ve Private Chef kapsamı: 72/72 test,
  326 assertion.
- `seo:agent-readiness-score --json`: 100/100.
- `npm run build`: başarılı.
- Ajan API route'ları: 8; Private Chef route'u: 1.
- İlgili PHP dosyaları sözdizimi ve `git diff --check`: temiz.
