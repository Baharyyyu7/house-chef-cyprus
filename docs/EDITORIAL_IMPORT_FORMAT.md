# Editoryal Tarif İçe Aktarımı

Katalog `database/editorial/first_50.php` altında sürüm kontrollü tutulur. Komut:

```bash
php artisan recipes:import-editorial --validate-only --json
php artisan recipes:import-editorial --dry-run --batch=1 --json
php artisan recipes:import-editorial --batch=all
```

Import slug üzerinden idempotent çalışır; mevcut kaydı günceller, public ID’yi korur ve `--skip-published` yayınlanan kayıtların korunmasını sağlar. Bu ilk katalog yalnız sürüm kontrollü, House Chef Cyprus `original_content` kayıtlarını taşır; dış servis, scraping veya lisanssız içerik getirmez.
