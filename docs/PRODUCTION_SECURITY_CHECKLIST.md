# Production security checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, production APP_KEY ve HTTPS APP_URL
- [ ] Secure/HttpOnly/SameSite cookie, dar CORS, CSP report endpointi ve trusted proxy yapılandırması
- [ ] Production DB/Redis credentials secret store'da; `.env`, local SQLite, backups ve private storage web root dışında
- [ ] Nginx dotfile/env/log/sqlite/backup/private path deny kuralları etkin
- [ ] Queue worker ve scheduler ayrı uygulama kullanıcısıyla çalışıyor
- [ ] Backup şifreleme/off-server retention/restore drill doğrulandı
- [ ] Admin MFA/identity politikası, güçlü parola ve session revoke süreci işletim tarafından onaylandı
- [ ] Mail SPF/DKIM/DMARC ve verified sender tamamlandı
- [ ] Provider API anahtarları minimum izinli; auto-publish/paid image generation varsayılan kapalı
- [ ] Dependency, secret, forbidden-file ve docs-link CI kontrolleri yeşil
