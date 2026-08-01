# Production Environment Contract

Production requires `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, a non-local canonical host, secure/HTTP-only cookies, non-SQLite database, non-sync queue, non-array cache/session, private storage, mail, backup disk and trusted proxies. Use `php artisan app:environment-check --environment=production --strict --json`; it intentionally reports configuration state, never secret values. Templates: `.env.production.example` and `.env.staging.example`.

`APP_RELEASE_STATE` may be `development`, `local_qa`, `staging_ready`, `staging`, `production_ready`, `production` or `maintenance`. Local/testing must never be represented as production.
