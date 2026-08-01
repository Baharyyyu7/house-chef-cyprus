#!/usr/bin/env bash
set -euo pipefail
APP_ROOT="${APP_ROOT:?APP_ROOT must point to the application root}"
DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true
cd "$APP_ROOT"
if "$DRY_RUN"; then php artisan app:post-deploy-smoke --dry-run --json; else php artisan app:post-deploy-smoke --json; fi
