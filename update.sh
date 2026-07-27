#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
git pull --ff-only && composer install --no-interaction --prefer-dist && npm ci && php artisan migrate --force && npm run build
