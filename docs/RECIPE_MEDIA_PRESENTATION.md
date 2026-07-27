# Recipe Media Presentation

Tarif kartı ve hero medya sırası:

1. rejected olmayan mevcut recipe media.
2. Tarifin public kimliği (yoksa slug) değerinden SHA-256 ile türetilen 1–8 numaralı markalı SVG placeholder.

Bu seçim deterministiktir: aynı tarif her yüklemede aynı fallback'i alır. Kartlar width, height ve lazy loading kullanır; detay hero öncelikli yüklenir. Haricî görsel indirme veya ücretli üretim bu sunum katmanının parçası değildir.
