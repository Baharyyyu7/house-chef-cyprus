# V1.0 RC kod hazırlık özeti

House Chef Cyprus, `visual-redesign-v2` dalında 50 doğrulanmış/yayınlanmış tarifle yerel ve CI doğrulamasına hazırlanmıştır. Katalog seed’i tekrar çalıştırılabilir; kullanıcı verisi, parola hash’i, token, oturum kimliği ve ham IP yönetim arayüzüne çıkarılmaz.

Görsel güven sözleşmesi “private review önce, insan onayı sonra public” ilkesini uygular. Onaylanmış dış fotoğraf bulunmadığında tarif kimliğiyle türetilen markalı fallback kullanılır; fallback gerçek yemek fotoğrafı gibi etiketlenmez. Pexels sağlayıcı katmanı hazır olsa da mevcut anahtar güvenli doğrulamada kabul edilmediğinden toplu sorgu veya indirme yapılmamıştır. Eden AI üretimi de bütçe/model onayı olmadan kapalıdır.

RC’nin kod tarafında açık blocker’ı yoktur; productiona çıkış için hosting, domain/DNS/TLS, production mail ve güvenli environment secret’ları gerekir. Bu rapor erişim veya ödeme yetkisi vermez.
