# Production deployment

House Chef Cyprus is deployed as a standard Laravel application. A production release is independent of any developer laptop, local ComfyUI process, local queue worker, or local filesystem runtime.

## Required runtime

- PHP 8.3+, Composer, Node build tooling
- MySQL 8+/PostgreSQL 15+ and Redis are recommended
- A persistent queue worker and one scheduler process
- HTTPS, writable application storage, off-server backup storage, and an outbound mail provider

Uygulanabilir Ubuntu/Nginx ve worker ayarları için [VPS deployment rehberi](VPS_DEPLOYMENT_GUIDE.md), [Nginx yapılandırması](NGINX_CONFIGURATION.md) ve [queue/scheduler production rehberi](QUEUE_AND_SCHEDULER_PRODUCTION.md) kullanılır.

## Safe release sequence

1. Create a release from the approved branch; never edit source on the server.
2. Copy `.env.production.example` to a protected environment store and fill real secrets there only.
3. Run `composer install --no-dev --optimize-autoloader` and build assets.
4. Run `php artisan migrate --force`, `storage:link`, and cache the configuration/routes/views.
5. Restart queue workers, install the scheduler cron, then call `/health` and the API health endpoints.
6. Run authenticated and guest smoke checks, verify backup creation, and retain the previous release until checks are green.

Generated images require an independently configured provider, explicit generation enablement, and a non-zero approved budget. Pexels candidates and AI output are private until an administrator approves them.

## Never deploy

- `.env`, backup archives, local SQLite files, private review media, or user secrets
- a `sync` queue, `array` cache, `APP_DEBUG=true`, or local-only image providers
