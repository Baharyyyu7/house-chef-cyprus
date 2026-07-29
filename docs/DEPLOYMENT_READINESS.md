# Deployment hazırlığı

Bu belge production deployment talimatı değildir; domain bağlama öncesi kontrol listesidir.

- `APP_ENV=production`, `APP_DEBUG=false`, benzersiz `APP_KEY` ve HTTPS URL yapılandırın.
- Gerçek mail, queue worker, scheduler/cron, backup hedefi ve rollback planını staging ortamında doğrulayın.
- Secret’ları yalnız environment secret store’da tutun; `.env`, model dosyaları ve private medya Git’e girmez.
- `php artisan config:cache`, `route:cache`, `view:cache` sonrasında health endpoint’lerini kontrol edin.
- Domain canlıya alınmadan canonical URL, sitemap, robots, e-posta doğrulama ve güvenlik header’ları staging’de test edilmelidir.

Yerel ComfyUI bir production dependency değildir. Kullanılacaksa ayrı worker/host, disk kotası, health check ve private output politikası gerekir.

## Server-only media policy

Production configuration uses `RECIPE_IMAGE_PRIMARY_PROVIDER=edenai` and `RECIPE_IMAGE_FALLBACK_PROVIDER=pexels`; both are disabled until production secrets and a non-zero explicit budget are supplied. `COMFYUI_ENABLED` stays false in production. No browser, Mac terminal, localhost worker or model file is required by the deployed application.

Before enabling a billable provider, separately validate authentication, actual image-model capability, rate limits and credit availability. A configured provider is not evidence that its generation model can be used.
