#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
php artisan test && php vendor/bin/pint --test && php vendor/bin/phpstan analyse --memory-limit=1G && npm run build
