# Kullanıcı yönetimi ve gizlilik

`/yonetim/kullanicilar` yalnız `access-admin` yetkisiyle erişilir. Liste ve detay ekranları profil özeti, doğrulama/durum, favori-dolap-pişirme sayıları ve maskeli aktivite kaydı verir.

Yönetici bir hesabı askıya alabilir, yeniden etkinleştirebilir veya mevcut server-side oturumlarını kapatabilir. Her aksiyon audit log’a kaydedilir. Yönetici kendisini askıya alamaz.

Parola, parola hash’i, token, cookie, oturum kimliği ve ham IP hiçbir yönetim ekranında gösterilmez. Aktivite kayıtları maskeli IP ve HMAC hash ile sınırlıdır; metadata içindeki `password`, `token`, `authorization`, `cookie` ve `session` anahtarları saklanmaz.

Production ortamında demo kullanıcıları oluşturulmaz. Yerel seed hesabı yalnız development/testing içindir ve production’a taşınmamalıdır.
