# Ingredient Domain

Ingredient kayıtları public ULID, canonical ad, kategori, varsayılan birim, aktiflik,
bozulabilirlik, tahmini raf ömrü, saklama tipi ve not alanlarını destekler. Türkçe içerik
varsayılandır; farklı diller için translation tablosu bu sprintte eklenmez.

Alias kayıtları ayrı tabloda, locale=`tr` ve normalize edilmiş değerle tutulur. Normalizasyon
Türkçe karakterlerin ASCII eşdeğerini, küçük harfi ve gereksiz boşlukları ortak hale getirir.
