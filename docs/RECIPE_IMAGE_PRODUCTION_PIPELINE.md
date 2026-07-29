# Tarif görseli production pipeline’ı

1. Provider yalnız server-side çağrılır; anahtar asla tarayıcıya, loga veya API cevabına geçmez.
2. Arama sonucu yalnız aday metadata’sıdır. İndirme HTTPS Pexels allowlist, boyut ve MIME kontrolünden geçerek private diske alınır.
3. Her adayda dış ID, fotoğrafçı/URL, Pexels sayfa URL’si, kaynak URL, boyut, alt metin, attribution, SHA-256 ve perceptual hash saklanır.
4. Aynı provider kaynağı veya checksum ikinci tarife atanamaz. İnsan incelemesi olmadan medya public olmaz.
5. İncelemede yanlış yemek, ana malzeme/sunum uyumsuzluğu, yazı/logo/watermark, kötü crop, eksik attribution veya belirsiz lisans reddedilir.
6. Onay sonrası thumbnail/focal point media pipeline ile üretilir. Reddedilmiş ve private medya public URL üretmez.
7. Uygun gerçek fotoğraf yoksa branded fallback korunur. Bu durum veri kalitesi raporunda görünür, fakat yanlış stok görseli seçilmez.

Pexels başarısızlığı güvenli hata koduyla yönetilir. Rate-limit veya provider hatasında otomatik yayın/tekrar denemesi yapılmaz. AI üretimi ayrı provider abstraction ve hard budget guard ile yürütülür.
