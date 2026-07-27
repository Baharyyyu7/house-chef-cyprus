#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/scripts/lib.sh"
cd "$PROJECT_ROOT"
command -v composer >/dev/null || { echo 'Composer bulunamadı.'; exit 1; }
command -v npm >/dev/null || { echo 'npm bulunamadı.'; exit 1; }
[[ -f .env ]] || cp .env.example .env
[[ -f database/database.sqlite ]] || touch database/database.sqlite
composer install --no-interaction --prefer-dist
npm ci
"$(php_bin)" artisan key:generate --force
"$(php_bin)" artisan migrate --seed
npm run build
echo 'Kurulum tamamlandı.'
