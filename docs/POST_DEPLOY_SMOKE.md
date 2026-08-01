# Post-deploy Smoke Test

Run `php artisan app:post-deploy-smoke --dry-run --json` before deployment and the live variant only against an approved URL. It covers public pages, auth entry points, sitemap/robots, health and published-content boundaries. Pair it with environment, database, queue, scheduler, mail no-send and backup health checks.
