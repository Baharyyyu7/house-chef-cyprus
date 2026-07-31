# House Chef Cyprus

## Production hazırlığı

Domain/VPS geldikten sonra uygulanacak güvenli operasyon sırası: [Go-live runbook](docs/GO_LIVE_RUNBOOK.md). Staging sözleşmesi, VPS şablonları, storage/Redis/PHP-FPM ve post-deploy smoke rehberleri `docs/` altında bulunur. Bu repository gerçek deploy veya DNS değişikliği yapmaz.

Türkçe tarif, malzeme dolabı, beslenme ve veri kalitesi platformu. `visual-redesign-v2` dalındaki V1.0 RC; provenance ve household/canonical ölçülerle seed edilen 50 `published`/`verified` tarif, private medya review akışı, branded fallback zinciri, blog/politika sayfaları ve SEO sitemap/schema altyapısını içerir. SEO & Content Automation katmanı; people-first editoryal kalite kapısı, idempotent audit, içerik brief/fırsatları ve admin operasyon merkezini içerir. Search Console, IndexNow, X dağıtımı ve otomatik public yayın varsayılan olarak kapalıdır. Pexels ve Unsplash yalnız insan onaylı editoryal aday kaynaklarıdır; Unsplash hotlink, görünür attribution ve tekil download tracking sözleşmesiyle kullanılır. Production deploy, DNS/TLS ve mail yetkileri bu depoda yapılmaz; ayrıntılı durum [V1.0 RC kontrol listesinde](docs/FINAL_RELEASE_CHECKLIST.md) yer alır.

## Yerelde başlatma

macOS/Linux için ilk kurulum: `./setup.sh`; çalıştırma: `./start.sh`; durum: `./status.sh`; durdurma: `./stop.sh`.

Uygulama varsayılan olarak `http://127.0.0.1:8010` üzerinde açılır. Lokal demo yöneticisi yalnızca local/testing ortamında seed edilir: `admin@housechef.local` / `ChangeMe123!`.

## Kalite komutları

`./test.sh`, `php artisan app:environment-check --json`, `php artisan api:openapi-check --json` ve `./scripts/clean-install-test.sh` sırasıyla uygulama, ortam, API sözleşmesi ve sıfırdan kurulum denetimlerini çalıştırır.

Production’da `.env` oluşturun; demo yöneticisini seed etmeyin, `APP_DEBUG=false` ve güvenli cookie ayarlarını kullanın. Ayrıntılar için [yerel geliştirme](docs/LOCAL_DEVELOPMENT.md), [güvenlik](SECURITY.md), [VPS deployment](docs/VPS_DEPLOYMENT_GUIDE.md) ve [post-deploy smoke testi](docs/POST_DEPLOY_SMOKE_TEST.md) belgelerine bakın.
