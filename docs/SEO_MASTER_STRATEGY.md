# SEO Master Strategy

House Chef Cyprus Türkçe/Kıbrıs odağında people-first bir yayın modelidir. Amaç arama motoru hacmi değil, doğrulanmış tarifler, anlaşılır ev mutfağı rehberleri ve ölçülebilir kullanıcı yararıdır.

Uygulama içi içerik fırsatları Google veri kaynağı gibi sunulmaz: katalog boşlukları ve anonim zero-result sinyalleri, editoryal önceliklendirme için sınırlı internal sinyallerdir. Search Console bağlanana kadar click/impression/position tahmini üretilmez.

## Güvenlik sınırları

- Rakip siteler yalnız konu/format boşluklarını anlamak için araştırılır; metin, tarif veya yapı kopyalanmaz.
- Kaynak notu, fact-check, özgünlük ve editoryal onay olmadan blog yazısı public yayınlanamaz.
- Otomatik içerik üretimi yalnız taslak oluşturabilir; `SEO_CONTENT_AUTO_PUBLISH=false` varsayılandır.
- Sağlık, alerjen, beslenme ve saklama iddiaları kaynak/fact-check olmadan kesin ifade edilmez.

## Teknik yüzey

`SeoMetadata`, `KeywordTopic`, `ContentBrief`, `ContentOpportunity`, `SeoIssue`, `ZeroResultSearch`, `ContentRevision` ve güvenli sağlayıcı durumları merkezi SEO çalışma alanını oluşturur. Public sayfalar canonical, Türkçe Open Graph ve yalnız gerçek verilere dayalı JSON-LD kullanır.
