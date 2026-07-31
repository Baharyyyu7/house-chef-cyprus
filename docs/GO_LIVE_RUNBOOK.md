# Go-live runbook

Bu sıra domain/VPS erişimi sağlandıktan sonra uygulanır; bu repository hiçbir DNS veya deployment işlemini otomatik başlatmaz.

1. Ubuntu 24.04, firewall, Nginx, PHP-FPM, MySQL/PostgreSQL, Redis, Supervisor ve Composer'ı kurun.
2. Repository'yi release dizinine alın; `.env.production.example` temelinde yalnız sunucuda `.env` oluşturun.
3. `deploy/` şablonlarındaki `__DOMAIN__`, `__APP_ROOT__`, `__APP_USER__`, `__APP_GROUP__` değerlerini sunucuya özel olarak değiştirin.
4. Önce staging'de strict environment check, migration, seed idempotency, worker, cron ve smoke kontrolünü geçirin.
5. Production'da deploy öncesi backup alın; `deploy/scripts/deploy.sh` ile release'i kurun.
6. TLS, Cloudflare Full (strict), canonical URL, secure cookie ve trusted proxy ayarlarını doğrulayın.
7. `php artisan app:post-deploy-smoke --json`, `queue:health --json`, `schedule:list` ve backup verify geçmeden DNS trafiğini açmayın.
8. İlk 24 saat error/queue/scheduler/backup monitoring'i aktif izleyin. Sorunda `deploy/scripts/rollback.sh` yalnız doğrulanmış önceki release ile kullanılır.

Canlıya çıkış için ayrıca domain, VPS erişimi, DNS/Cloudflare yetkisi, mail sağlayıcı credential'ı, Redis/veritabanı ve onaylı medya kararları gerekir.
