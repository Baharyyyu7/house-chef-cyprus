# Production mail kurulumu

House Chef Cyprus local log mailer ile canlıya çıkmaz. Seçilen sağlayıcı için doğrulanmış domain/gönderen, SPF, DKIM ve DMARC tamamlanır.

1. Sağlayıcı SMTP veya API credentialını yalnız production secret store'a ekleyin.
2. `MAIL_FROM_ADDRESS` gerçek ve doğrulanmış bir gönderen olmalıdır.
3. Password reset ve e-posta doğrulama için staging/test alıcısında smoke test yapın.
4. Bounce, suppression ve teslimat olayları kişisel veri içermeyen alarm politikasıyla izlenir.
5. Testi başarıyla geçmeyen e-posta akışı için kayıt veya şifre reset çağrıları açılmaz.

Mail credentialı, raw e-posta içeriği, reset URL'si veya alıcı listeleri log/backup/doküman içine konulmaz.
