# Tarif Görsel Gerçeklik Denetimi

`php artisan recipe-images:audit` gerçek/doğrulanmış görsel, yer tutucu, eksik medya, tekrar, alt metin, thumbnail ve lisans kapsamını raporlar. `--json`, `--missing-only` ve `--placeholder-only` seçenekleri otomasyon ve önceliklendirme içindir.

İlk pilot seçim yalnız rapordaki yayınlanmış tariflerden yapılır; farklı mutfak ve kategori dengesi gözetilir. Bu denetim görsel üretmez veya dış ücretli API çağrısı yapmaz.
