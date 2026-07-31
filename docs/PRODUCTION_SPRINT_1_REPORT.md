# Production Sprint 1 paketi

Bu sprint gerçek deployment yapmadan staging/production sözleşmeleri, Nginx/Supervisor/Cron/Logrotate şablonları, strict environment kontrolü, deploy/rollback/backup/health scriptleri ve post-deploy smoke komutunu hazırlar.

## Bilinçli dış bağımlılıklar

- Domain, DNS, VPS, SSL ve Cloudflare erişimi kullanıcı/operasyon yetkisi gerektirir.
- Production veritabanı, Redis, SMTP ve object-storage credential'ları repository'de bulunmaz.
- Gerçek medya yalnız editoryal onay sonrası public edilir; branded fallback korunur.
- Staging/production migration veya gerçek e-posta bu lokal paketten yapılmaz.
