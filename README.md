# House Chef Cyprus

## Milestone H — Smart Personalization

Kişiselleştirme açık rıza, kullanıcı sahipliği ve açıklanabilir deterministic kurallarla çalışır. Sadece `published` + `verified` tarifler adaydır; alerjen ve hariç tutulan malzemeler hard constraint'tir. [Mimari](docs/PERSONALIZATION_ARCHITECTURE.md), [gizlilik](docs/PERSONALIZATION_PRIVACY.md) ve [operasyon](docs/PERSONALIZATION_OPERATIONS.md) belgeleri kullanıcı verisinin sınırlarını açıklar.

## Production programı

Domain/VPS geldikten sonra uygulanacak güvenli operasyon sırası: [Go-live runbook](docs/GO_LIVE_RUNBOOK.md). Staging sözleşmesi, VPS şablonları, storage/Redis/PHP-FPM ve post-deploy smoke rehberleri `docs/` altında bulunur. Bu repository gerçek deploy veya DNS değişikliği yapmaz.

## Release Candidate 1

RC1, mevcut ürünü yeni kapsam açmadan doğrular. [RC1 kontrol merkezi](docs/RC1_MASTER_VALIDATION.md), gerçek katalog/route/servis sinyallerini toplar; gerçek cihaz QA, ekran okuyucu, saha Core Web Vitals, hosting/DNS/TLS/SMTP ve insan editoryal onayı yerine geçmez.

## Milestone G — Product Experience

Public, member ve admin yüzeyleri ortak tasarım tokenları, erişilebilir primitive’ler, light/dark sözleşmesi ve mobil davranışla birleşir. [Ürün deneyimi sağlık denetimi](docs/PRODUCT_EXPERIENCE_HEALTH.md) güvenli statik ön kontroller sağlar; gerçek tarayıcı, ekran okuyucu ve saha Core Web Vitals doğrulaması ayrı kabul kapısıdır.

## Milestone F — Launch readiness

Production environment, artifact/shared-hosting/SSH deployment, database, backup/restore-test, queue, scheduler, mail, security, health, manifest, rollback and incident contracts are prepared. Start with [environment contract](docs/PRODUCTION_ENVIRONMENT_CONTRACT.md), [go-live checklist](docs/GO_LIVE_CHECKLIST.md) and [Milestone F infrastructure](docs/MILESTONE_F_INFRASTRUCTURE.md). A real hosting/domain/TLS/SMTP/monitoring setup is still an external human-approved launch gate.

## Real Media Excellence

Medya adayları private review zincirinden geçer; yalnız lisans, attribution ve bağımsız insan onayı tamamlanmış `approved_real_media` kayıtları public recipe/blog yüzeyleri, schema ve image sitemap için uygundur. Branded fallback gerçek fotoğraf değildir. Operasyon komutları ve kuralları için [Milestone D](docs/MILESTONE_D_MEDIA_EXCELLENCE.md) belgesine bakın.

Türkçe tarif, malzeme dolabı, beslenme ve veri kalitesi platformu. `visual-redesign-v2` dalındaki katalog; 50 `published`/`verified` tarif ve 20 yayımlı blogun yanında, insan onayı bekleyen 450 tarif ve 180 blog taslağını içerir. Controlled Content Factory taslakları public API, sitemap ve listelere sızdırmaz; otomatik yayın kapalıdır. Phase 4 [editoryal operasyonlar](docs/EDITORIAL_OPERATIONS_ARCHITECTURE.md), review batch’leri ve kanıt hazırlık kuyruklarını insan kararının yerine geçmeden yönetir.

## Yerelde başlatma

macOS/Linux için ilk kurulum: `./setup.sh`; çalıştırma: `./start.sh`; durum: `./status.sh`; durdurma: `./stop.sh`.

Uygulama varsayılan olarak `http://127.0.0.1:8010` üzerinde açılır. Lokal geliştirme yöneticisi yalnızca local/testing ortamında seed edilir: `admin@housechef.local` / `ChangeMe123!`.

## Kalite komutları

`./test.sh`, `php artisan app:environment-check --json`, `php artisan api:openapi-check --json` ve `./scripts/clean-install-test.sh` sırasıyla uygulama, ortam, API sözleşmesi ve sıfırdan kurulum denetimlerini çalıştırır.

Production’da `.env` oluşturun; local geliştirme yöneticisini seed etmeyin, `APP_DEBUG=false` ve güvenli cookie ayarlarını kullanın. Ayrıntılar için [Production Manifestosu](docs/PRODUCTION_PROGRAM_MANIFESTO.md), [içerik standardı](docs/PRODUCTION_CONTENT_STANDARD.md), [yerel geliştirme](docs/LOCAL_DEVELOPMENT.md), [güvenlik](SECURITY.md), [VPS deployment](docs/VPS_DEPLOYMENT_GUIDE.md) ve [post-deploy smoke testi](docs/POST_DEPLOY_SMOKE_TEST.md) belgelerine bakın.
