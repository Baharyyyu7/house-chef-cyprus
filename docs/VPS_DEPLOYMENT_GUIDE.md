# Ubuntu 24.04 VPS dağıtım rehberi

Bu rehber, House Chef Cyprus'ı ilk kez canlıya alma prosedürüdür. Uygulama kodu veya `.env` dosyası sunucuda düzenlenmez; her yayın CI'dan geçmiş `visual-redesign-v2` sürümünden üretilir.

## Ön koşullar

- Ubuntu 24.04 LTS, Nginx, PHP-FPM 8.3+, Composer 2, Redis ve MySQL 8+ veya PostgreSQL 15+.
- Ayrı bir, yazılabilir uygulama kullanıcısı; web kökü yalnız `public/` dizinini göstermelidir.
- Production secrets, sunucu dışındaki güvenli secret store veya yetkisi sınırlı `.env` dosyasında bulunur. `.env` Git'e, backup'a veya deploy paketine eklenmez.
- Off-server, şifreli database ve private-media backup hedefi; doğrulanmış mail göndereni; canonical domain ve TLS sertifikası.

## İlk kurulum

1. Uygulamayı `/var/www/house-chef/releases/<release>` altında yayınlayın ve `current` symlink'ini yalnız başarılı smoke testten sonra değiştirin.
2. Sahipliği uygulama kullanıcısına verin; yalnız `storage/` ve `bootstrap/cache/` yazılabilir olsun. `storage/app/private` Nginx tarafından servis edilmez.
3. Production environment değerlerini `.env.production.example` üzerinden girin: `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, güvenli session cookie, Redis cache/queue/session, production database ve dar CORS allowlist.
4. `composer install --no-dev --prefer-dist --optimize-autoloader` çalıştırın; derlenen asset artifact'ini kopyalayın veya `npm ci && npm run build` çalıştırın.
5. `php artisan migrate --force`, `php artisan storage:link`, cache komutları ve aşağıdaki health/smoke kontrollerini çalıştırın.
6. Supervisor worker'ı ve cron scheduler'ı etkinleştirin. İlk backup ve restore-verify sonucu başarılı olmadan trafiği açmayın.

## Yayın sonrası smoke kontrolleri

```bash
php artisan app:environment-check --json
php artisan queue:health --json
php artisan schedule:list
php artisan backup:create --json
php artisan backup:verify --json
curl -fsS https://<canonical-domain>/health
curl -fsS https://<canonical-domain>/api/v1/health
curl -fsS https://<canonical-domain>/sitemap.xml
```

Guest, member ve admin smoke akışları; tarif listesi/detayı, login, private media authorization ve 403 kontrollerini içerir. Onaylanmamış Pexels/Unsplash/AI medyası hiçbir aşamada public edilmez.

## Günlük operasyon

- Redis, queue, scheduler heartbeat, failed job, backup verify ve `/health` metriklerini izleyin.
- Media candidate araması yalnız insan review için private kayıt yaratır; otomatik public yayın kapalı kalır.
- Log retention ve backup saklama süresini yasal/operasyonel gereksinime göre ortam değişkenleriyle yönetin.
