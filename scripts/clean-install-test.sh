#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
source "$ROOT/scripts/lib.sh"
WORKDIR="$(mktemp -d "${TMPDIR:-/tmp}/house-chef-clean-install.XXXXXX")"
SERVER_PID=''
cleanup() { [[ -n "$SERVER_PID" ]] && kill "$SERVER_PID" 2>/dev/null || true; rm -rf "$WORKDIR"; }
trap cleanup EXIT

git -C "$ROOT" archive --format=tar HEAD | tar -xf - -C "$WORKDIR"
cd "$WORKDIR"
RUNTIME_PATH="$(dirname "$(php_bin)"):$PATH"
PATH="$RUNTIME_PATH" "$(composer_bin)" install --no-interaction --prefer-dist --no-progress
npm ci --no-audit --no-fund
cp .env.example .env
touch database/database.sqlite
"$(php_bin)" artisan key:generate --force
"$(php_bin)" artisan migrate:fresh --seed
"$(php_bin)" artisan db:seed
npm run build
"$(php_bin)" artisan config:cache
"$(php_bin)" artisan route:cache
"$(php_bin)" artisan view:cache
"$(php_bin)" artisan serve --host=127.0.0.1 --port=18010 >/tmp/house-chef-clean-install.log 2>&1 & SERVER_PID=$!
for _ in 1 2 3 4 5 6 7 8 9 10; do curl -fsS http://127.0.0.1:18010/health >/dev/null && break; sleep 1; done
curl -fsS http://127.0.0.1:18010/health >/dev/null
curl -fsS http://127.0.0.1:18010/api/v1/health >/dev/null
curl -fsS http://127.0.0.1:18010/ >/dev/null
curl -fsS http://127.0.0.1:18010/login >/dev/null
"$(php_bin)" artisan queue:health --json >/dev/null
"$(php_bin)" artisan schedule:list >/dev/null
echo 'Clean install smoke test passed.'
