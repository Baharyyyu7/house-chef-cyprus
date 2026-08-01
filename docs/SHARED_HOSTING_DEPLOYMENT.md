# Shared Hosting Deployment

Set the document root to `release/public`, keep `.env` outside artifacts, and make only `storage/` and `bootstrap/cache/` writable. For cron-only hosts schedule `* * * * * php /path/artisan schedule:run` and bounded queue workers with `queue:work --stop-when-empty --tries=3 --timeout=90` under a host lock.

Use `scripts/deploy-shared-hosting.sh --dry-run` with explicit `RELEASE_DIR` and `WEB_ROOT` before a live release. Take and verify a pre-deploy backup, validate environment, run `migrate --force`, cache, smoke test, then remove maintenance. No `migrate:fresh` in production.
