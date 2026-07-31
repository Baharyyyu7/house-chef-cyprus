#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="${APP_ROOT:?APP_ROOT must point to the release directory}"
DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true

run() { if "$DRY_RUN"; then printf '[dry-run] %q ' "$@"; printf '\n'; else "$@"; fi; }
cd "$APP_ROOT"
run composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
run npm ci
run npm run build
run php artisan migrate --force --no-interaction
run php artisan storage:link --force
run php artisan config:cache
run php artisan route:cache
run php artisan view:cache
run php artisan event:cache
run php artisan queue:restart
run php artisan app:environment-check --environment=production --strict --json
run php artisan app:post-deploy-smoke --json
