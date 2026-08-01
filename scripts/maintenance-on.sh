#!/usr/bin/env bash
set -euo pipefail
APP_ROOT="${APP_ROOT:?APP_ROOT must point to the application root}"
SECRET="${MAINTENANCE_SECRET:?MAINTENANCE_SECRET must be supplied outside source control}"
cd "$APP_ROOT"
php artisan down --secret="$SECRET" --retry=60 --render='errors::503'
