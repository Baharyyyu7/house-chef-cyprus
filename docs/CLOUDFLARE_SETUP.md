# Cloudflare hazırlık kontrol listesi

- Canonical domaini belirleyin; `www`/apex yönlendirmesini tek hedefe sabitleyin.
- DNS kayıtlarını VPS sağlayıcısının IP'sine yöneltin; proxy durumunu TLS doğrulamasından sonra etkinleştirin.
- SSL/TLS modunu **Full (strict)** kullanın; origin sertifikası geçerli değilse trafiği açmayın.
- Uygulama `TRUSTED_PROXIES` yapılandırması yalnız doğrulanmış Cloudflare proxy IP'lerini kabul etmelidir.
- Cache kuralı yalnız public, değişmez asset'lere uygulanır. Auth, admin, API mutation, private media, sitemap üretimi ve HTML sayfaları yanlışlıkla ortak cache'lenmez.
- WAF/rate limit kuralları login, password reset ve API için uygulama limiter'larını tamamlar; onların yerine geçmez.
- Cloudflare API tokenı yalnız gerekli zone izinleriyle oluşturulur ve repo/dokümana yazılmaz.
