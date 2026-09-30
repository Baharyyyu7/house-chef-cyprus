# Kıbrıs içki fiyatları blog teslimi

Gözlem ve teslim zamanı: 30 Eylül 2026, Asia/Famagusta

## Teslim edilen taslaklar

1. `guney-kibris-icki-fiyatlari-2026-market-bar-rehberi`
   - Türkçe gövde: 917 kelime
   - Kaynaklar: SupermarketCy bira kategorisi, Trabi Bar Paphos menüsü, Smoky Ocean Limassol menüsü
   - Fiyat para birimi: EUR
2. `kuzey-kibris-icki-fiyatlari-2026-market-bar-rehberi`
   - Türkçe gövde: 999 kelime
   - Kaynaklar: KipraRent Haziran 2026 kaynak derlemesi, Jastin Off Licence Ağustos 2026 pazar notu
   - Fiyat para birimi: TRY; eski kurla sabit EUR karşılığı üretilmedi

Her taslak `en`, `de`, `fa`, `pl`, `ru` ve `pt` yerelleştirmesine sahiptir. Türkçe dahil yedi dilin tamamında başlık, özet ve gövde alanları bulunur.

## Canlılık durumu

- Her iki kayıt `draft` durumundadır.
- `published_at` boştur.
- İnsan onayı yoktur.
- Her kayıtta 18 çeviri alanı vardır: 6 dil x 3 zorunlu alan.
- Yerel ajan kuralı uyarınca otomatik yayın yapılmadı.

## Fiyat sunum kuralları

- Güney ve Kuzey Kıbrıs ayrı yazılarda ele alındı.
- Her sayı ürün hacmi ve gözlem tarihiyle verildi.
- Kampanya fiyatları kampanya olarak işaretlendi.
- Kuzey Kıbrıs fiyatlarının Eylül 2026 raf garantisi olmadığı açıklandı.
- Güvenilir kokteyl bandı bulunmayan Kuzey Kıbrıs için tahmin yazılmadı.
- Sorumlu tüketim ve araç kullanmama uyarısı eklendi.

## Sunucu teslim yolları

- `shared/storage/app/content-import/blog/{slug}.json`
- `shared/storage/app/content-import/translations/{locale}/blog-{slug}.json`

