# Staging kurulumu

Staging, production ile aynı PHP, veritabanı, Redis, Nginx ve Supervisor düzenini kullanır; ancak production verisi, public indeksleme, sosyal paylaşım ve ücretli görsel üretimi içermez.

1. `.env.staging.example` dosyasını sunucuda `.env` olarak kopyalayın ve yalnız staging sırlarını girin.
2. `APP_DEBUG=false`, HTTPS, secure cookie ve Redis'i zorunlu kabul edin.
3. `SEO_INDEXING_ENABLED=false`, `X_API_ENABLED=false` ve tüm görsel üretim bayrakları `false` kalmalıdır.
4. `php artisan app:environment-check --environment=staging --strict --json` geçmeden deploy etmeyin.
5. Staging'de yalnız onaylı QA fixture kullanılabilir; production seed veya gerçek kullanıcı verisi taşınmaz.

Staging alanı bir test ortamıdır; gerçek e-posta yerine log/sandbox mailer kullanılmalıdır.
