# Admin Measurement V3

Household ölçü birimleri ve malzemeye özgü dönüşümler, ortak V3 başlık, filtre çubuğu, kontrollü tablo taşması ve form kartlarıyla yönetilir.

- Ölçü kodları benzersizdir; Türkçe tekil/çoğul ad, kısa ad, kategori, hassasiyet ve görünürlük saklanır.
- Pasifleştirme varsayılan güvenli işlemdir: eski tarif verisi korunur, yeni seçimler aktif ölçülerle sınırlanır.
- Dönüşümler malzeme, ev ölçüsü, canonical miktar/birim, yoğunluk, kaynak, güven ve doğrulama bilgisi taşır.
- Düşük güvenli veya kaynaksız kayıtlar yöneticiye görünür; kullanıcıya kesin bir dönüşüm gibi sunulmaz.
- Dahili integer kimlikler tabloda gösterilmez; izin kontrolü admin middleware/policy katmanındadır.
