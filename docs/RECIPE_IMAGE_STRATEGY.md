# Recipe Image Strategy

## Görsel durumları

`missing`, `placeholder`, `generated`, `licensed`, `original`, `verified`, `rejected`. Yayın önceliği: özgün içerik; ardından lisanslı/doğrulanmış içerik; kontrollü ve açıkça işaretlenmiş AI görseli; son olarak marka placeholder'ı.

Pexels ve Unsplash sonuçları yalnız adaydır. Unsplash ikincil sağlayıcıdır; yalnız insan onayından sonra, hotlink URL ve görünür attribution ile `verified` medya olabilir.

## Zorunlu metadata

Her kullanılabilir görsel source/creator, license, license URL veya izin notu, uploaded/checked zamanı, alt text, focal point, crop türevleri, doğrulama durumu ve gerektiğinde content-origin bilgisini taşır. Lisansı belirsiz görsel yayınlanmaz.

## Aşamalı hedef

- İlk 20 öne çıkan tarif: tutarlı hero/card görüntüsü ve alternatif metin.
- İlk 50 doğrulanmış tarif: en az bir kullanılabilir tarif görseli veya dürüst placeholder.
- Mutfak/kategori yüzeyleri: ayrı, lisansı doğrulanmış banner planı.

Rastgele web görseli indirme, görünür kaynak izni olmadan yeniden kullanım ve aynı placeholder'ın gerçek görsel gibi sunulması yasaktır. Faz 3'te sekiz erişilebilir, markalı SVG placeholder varyasyonu eklendi; bunlar `is_placeholder=true`, lisans ve attribution metadata'sı ile açıkça ayrılır.
