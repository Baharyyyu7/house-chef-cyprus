# Production Database

MySQL 8+ or PostgreSQL is the production target; SQLite is local/testing only. Before every production migration: verify a private backup, run `app:database-readiness --json`, review destructive/long-running changes, use `php artisan migrate --force`, and record the release manifest. Prefer forward fixes over automatic destructive rollback.
