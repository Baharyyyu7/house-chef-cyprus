# Production logging

Production `daily` log kanalı ve rotasyon kullanır. Loglar request ID, güvenli kullanıcı public ID ve güvenli job bağlamı içerebilir.

Kesinlikle loglanmaması gerekenler: parola, token, cookie, authorization başlığı, ham provider payload'ı, stack trace'in public çıktısı ve private mutlak dosya yolu.

`deploy/logrotate/house-chef` örneği sunucu kullanıcı/grubu yerleştirildikten sonra etkinleştirilir. Hata izleme sağlayıcısı adapter üzerinden yapılandırılır; DSN depoya yazılmaz.
