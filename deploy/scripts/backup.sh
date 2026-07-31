#!/usr/bin/env bash
set -euo pipefail
APP_ROOT="${APP_ROOT:?APP_ROOT must point to the application root}"
cd "$APP_ROOT"
php artisan backup:create --no-interaction
php artisan backup:verify --no-interaction
