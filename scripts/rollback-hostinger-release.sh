#!/usr/bin/env bash
set -euo pipefail

DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true && shift
APP_HOME="${APP_HOME:?APP_HOME must be the absolute domain directory}"
TARGET_RELEASE="${TARGET_RELEASE:?TARGET_RELEASE must be a verified release directory}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
CURRENT_LINK="$APP_HOME/current"
WEB_ROOT="$APP_HOME/public_html"

run() { if "$DRY_RUN"; then printf '[dry-run] '; printf '%q ' "$@"; printf '\n'; else "$@"; fi; }
fail() { printf '[hostinger-rollback] ERROR: %s\n' "$*" >&2; exit 1; }
[[ "$APP_HOME" != / && "$APP_HOME" == /* && "$TARGET_RELEASE" == "$APP_HOME"/releases/* ]] || fail 'Unsafe rollback path.'
run test -f "$TARGET_RELEASE/artisan"
run test -f "$TARGET_RELEASE/public/index.php"

if [[ -L "$CURRENT_LINK" && -f "$CURRENT_LINK/artisan" ]]; then
    run "$PHP_BIN" "$CURRENT_LINK/artisan" down --render=errors::503 --retry=60 --no-interaction
fi
run ln -sfn "$TARGET_RELEASE" "$CURRENT_LINK"
run ln -sfn "$CURRENT_LINK/public" "$WEB_ROOT"
run "$PHP_BIN" "$CURRENT_LINK/artisan" optimize:clear
run "$PHP_BIN" "$CURRENT_LINK/artisan" app:post-deploy-smoke --json
run "$PHP_BIN" "$CURRENT_LINK/artisan" up --no-interaction
printf '[hostinger-rollback] Application files rolled back. Database migrations are forward-only and are not reverted.\n'
