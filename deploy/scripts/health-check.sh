#!/usr/bin/env bash
set -euo pipefail
APP_ROOT="${APP_ROOT:?APP_ROOT must point to the application root}"
cd "$APP_ROOT"
php artisan app:environment-check --environment=production --strict --json
php artisan queue:health --json
php artisan app:post-deploy-smoke --json
