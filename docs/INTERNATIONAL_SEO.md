# Uluslararası SEO — İngilizce Sürüm (`/en`)

Search Console verileri gösterimlerin önemli kısmının Türkiye dışından (Güney Kıbrıs, Almanya, İngiltere, İran, Polonya, İsviçre, Portekiz, Avusturya, Hollanda…) geldiğini gösteriyor. Bu ziyaretçilerin çoğu Türkçe arama yapmadığı için site, Türkçe URL'lere dokunmadan paralel bir İngilizce sürümle genişletildi.

## URL yapısı

| Türkçe (kök, değişmedi) | İngilizce |
| --- | --- |
| `/` | `/en` |
| `/tarifler` | `/en/recipes` |
| `/tarifler/{tr-slug}` | `/en/recipes/{en-slug}` |
| `/blog` | `/en/blog` |
| `/blog/{tr-slug}` | `/en/blog/{en-slug}` |
| `/hakkimizda` | `/en/about` |

- Her sayfa karşılığını `<link rel="alternate" hreflang="tr|en|x-default">` ile karşılıklı olarak bildirir. `x-default`, Türkçe ve İngilizce dışındaki dillerde arama yapanlar için İngilizce sürümdür (`LOCALIZATION_X_DEFAULT`).
- Karşılığı yayında olmayan bir sayfa için hreflang üretilmez; filtreli/sorgulu URL'ler `noindex,follow` kalır ve hreflang taşımaz.
- `<html lang>`, `og:locale` (`tr_TR` / `en_GB`), `og:locale:alternate`, WebSite ve Recipe/BlogPosting şemalarındaki `inLanguage` dile göre üretilir.
- `/sitemaps/en.xml` İngilizce URL'leri `xhtml:link` alternatifleriyle listeler; `/sitemaps/pages.xml`, `/sitemaps/recipes.xml` ve `/sitemaps/blog.xml` İngilizce karşılıkları da bildirir. Sitemap index yeni haritayı otomatik içerir.
- Türkçe sayfada tarayıcı dili Türkçe olmayan ziyaretçiye kapatılabilir bir “This page is also available in English” bandı gösterilir. Otomatik yönlendirme **yapılmaz** (Googlebot ve kullanıcı tercihi için yanlış olur).

## İçerik kaynağı

- `resources/translations/en/recipes.php` (50 tarif, `recipes/batch-*.php`) ve `resources/translations/en/blog.php` (20 yazı), Türkçe slug ile anahtarlanmış sürümlü editoryal çevirilerdir.
- İlk 10 tarif `PublishedRecipeBatchOneCatalog` içeriğinin birebir çevirisidir. Kalan 40 tarif, aynı yemeklerin İngilizce okur için yazılmış editoryal sürümleridir; Türkçe kayıt değiştiğinde editörün İngilizce sürümü de gözden geçirmesi gerekir.
- Çeviri, Türkçe tarif/yazı `published` (+ tarifte `verified`) değilse hiçbir yerde görünmez; çeviri bir taslağı asla yayına almaz.

## İçe aktarma

```bash
php artisan content:import-translations --locale=en --json
php artisan content:import-translations --locale=en --dry-run
```

Komut idempotenttir (içerik hash'i değişmeyen kayıt `unchanged` sayılır). Kaynağı olmayan slug'lar `missing_source` olarak raporlanır ve hata sayılmaz; bozuk katalog girdisi (`invalid`) deploy'u durdurur. Komut `scripts/deploy-hostinger-release.sh` ve `deploy/scripts/deploy.sh` içinde migration'dan sonra, ayrıca `DatabaseSeeder` → `ContentTranslationSeeder` üzerinden çalışır.

## Deploy sonrası Search Console adımları

1. Search Console → Site Haritaları: `https://housechefcyprus.com/sitemap.xml` yeniden gönderin (veya `https://housechefcyprus.com/sitemaps/en.xml` ayrıca ekleyin).
2. URL denetimi ile `https://housechefcyprus.com/en` ve birkaç `/en/recipes/...` sayfası için dizine eklenme isteyin.
3. Performans → Filtre: “Sayfa” içerir `/en/` ile İngilizce sürümün gösterim/tıklama gelişimini izleyin; “Ülke” sekmesini karşılaştırın.
4. Birkaç hafta sonra “Dizin oluşturma → Sayfalar” raporunda `hreflang` veya “alternatif sayfa” uyarılarını kontrol edin.
