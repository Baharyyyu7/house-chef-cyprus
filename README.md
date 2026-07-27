# House Chef Cyprus

Türkçe tarif, malzeme dolabı, beslenme ve veri kalitesi platformu. Technical Foundation A–E ve Final Product Core Audit tamamlanmıştır. `visual-redesign-v2` dalındaki V2 preview; ana sayfa, public header/footer ve tema sistemi için kontrollü incelemededir. Tüm sayfa, recipe data ve admin dönüşüm programı [Master Product Redesign Planı](docs/MASTER_PRODUCT_REDESIGN_PLAN.md) içinde fazlanmıştır; kullanıcı onayı olmadan geniş görsel değişiklik başlatılmaz.

## Yerelde başlatma

macOS/Linux için ilk kurulum: `./setup.sh`; çalıştırma: `./start.sh`; durum: `./status.sh`; durdurma: `./stop.sh`.

Uygulama varsayılan olarak `http://127.0.0.1:8010` üzerinde açılır. Lokal demo yöneticisi yalnızca local/testing ortamında seed edilir: `admin@housechef.local` / `ChangeMe123!`.

## Kalite komutları

`./test.sh`, `php artisan app:environment-check --json`, `php artisan api:openapi-check --json` ve `./scripts/clean-install-test.sh` sırasıyla uygulama, ortam, API sözleşmesi ve sıfırdan kurulum denetimlerini çalıştırır.

Production’da `.env` oluşturun; demo yöneticisini seed etmeyin, `APP_DEBUG=false` ve güvenli cookie ayarlarını kullanın. Ayrıntılar için [yerel geliştirme](docs/LOCAL_DEVELOPMENT.md) ve [güvenlik](SECURITY.md) belgelerine bakın.
