# Database Performance

## Uygulanan strateji

Sorgu kalıplarına göre recipes listeleme/filtreleme, recipe ingredient lookup,
pantry uygunluk, aktif substitution, nutrition fact/snapshot, Data Quality açık
issue, queue reservation ve audit geçmişi için hedefli indeksler eklendi.
Mevcut unique constraint'ler (slug, public ID, favorites, pantry, steps ve
ilişkisel domain kayıtları) tekrar edilmedi.

Arama katmanı artık liste kartlarının kullanmadığı medya alanlarını yüklemez.
Nutrition/allergen/dietary analizlerinde önceden yüklenmiş ilişkiler tekrar
sorgulanmaz. Ingredient matching de önceden eager-loaded recipe ingredients
üzerinde çalışır.

## Pagination

Merkezî limitler `config/performance.php` içindedir: public/API varsayılanı 12,
API üst limiti 50, admin varsayılanı 30'dur. API doğrulaması `per_page > 50`
için 422 döndürmeye devam eder.

Cursor pagination, zaman sıralı büyük audit/failed-job geçmişi ve çok büyük
tarif katalogları için production ölçeği fazında değerlendirilecektir; mevcut
LengthAwarePaginator toplam sonuç bilgisini korur.

## Baseline

`php artisan performance:profile` temel listeleme ve arama yollarının sorgu
sayısı ile süresini gösterir. `--json` CI veya yerel karşılaştırmalar için
makine okunabilir çıktı üretir. Sonuçlar donanım ve cache durumuna bağlıdır;
mutlak hedef değil, regresyon karşılaştırma tabanıdır.
