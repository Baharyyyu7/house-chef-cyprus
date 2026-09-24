# Öksüz Sayfa Kalmasın — Tamamlama Notu

## Başlangıç ölçümü (canlı `06efeda`)

- Sitemap adresi: **46**
- Hub kaynağı: ana sayfa, tarifler, blog, mutfaklar, malzemeler
- Sıfır iç bağlantılı adres: **8**
- Öksüzler: altı güven/editoryal sayfa ile `/zorluk/orta` ve `/zorluk/zor`
- Koleksiyon dağılımı: 18 malzeme, 5 mutfak, 2 süre, 3 zorluk
- `lastmod`: 5/46 (**%10,9**)

## Yapılanlar

- `content:link-integrity`, pages sitemap'i hub bağlantılarına ters eşleyerek
  `orphaned`, `orphan_urls` ve adres başına `backlinks` üretir.
- Üretimde kategori eşiği ölçüldü: 8 kategori en az 4 doğrulanmış/yayındaki
  tarif taşıyor. `/kategoriler` ve `/kategoriler/{slug}` bu tek merkezi eşikle
  eklendi; liste, 404 kararı ve sitemap aynı kaynağı kullanır.
- Tarif arşivi bütün var olan süre/zorluk sayfalarına görünür bağlantı verir.
  Footer, sitemap'teki güven/editoryal sayfalara doğrudan bağlantı verir.
- `/blog`, yalnız o sayfada görünen 12 yazıyla `CollectionPage` + `ItemList`
  taşır. `ListItem` yalnız `@type`, `position`, `url` içerir.
- Mutfak, malzeme ve kategori sitemap `lastmod` değeri ilgili koleksiyondaki
  en son doğrulanmış/yayındaki tarifin `published_at` tarihidir; tarih yoksa
  alan basılmaz.
- Şema sözleşmesi muhafızı tablosu çözülemeyen yazımları JSON'da raporlar.
  Zincirli model sorguları çözüldü: çözülen yazım **439 → 456**, atlanan
  yazım **91 → 52**. Kalan dağılım: 30 değişken, 15 zincir, 7 property.

## Aynı adlı farklı genişlikte sütunlar

Muhafız bütün varyantları `column_length_variants` alanında listeler. Özellikle:

- `zero_result_searches.suggested_action = varchar(40)`: kasıtlı olarak kısa,
  yalnız uygulama içi göreli koleksiyon yolu saklar (`/sure/20-dakika`).
- `data_quality_issues.suggested_action = varchar(255)`: kasıtlı olarak geniş,
  insan için serbest biçimli düzeltme önerisi saklar.

Ad aynı olsa da veri sözleşmeleri farklıdır; bu nedenle şema değişikliği
yapılmadı. `public_id` 26/255 gibi tarihsel istisnalar ile `status`, `provider`,
`source_url` gibi alanlar da raporda görünür; otomatik eşitleme güvenli değildir.

## Doğrulama

- Hedefli testler: **47/47**, 151 assertion
- Tam paket: **980/980**, 5.087 assertion
- Pint: temiz
- `git diff --check`: temiz
- Üretim şema sapması: **0**
- Kanıtlanmış dar-sütun taşması: **0**

Kapanış canlı ölçümleri deploy doğrulamasından sonra bu görevin teslim
raporunda yan yana verilecektir.
