#!/usr/bin/env bash
set -euo pipefail

DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true
RELEASE_DIR="${RELEASE_DIR:?RELEASE_DIR must point to the extracted release}"
WEB_ROOT="${WEB_ROOT:?WEB_ROOT must point to the hosting public web root}"
run() { if "$DRY_RUN"; then printf '[dry-run] '; printf '%q ' "$@"; printf '\n'; else "$@"; fi; }

if [[ "$RELEASE_DIR" == / || "$WEB_ROOT" == / ]]; then
  echo 'Refusing broad filesystem target.' >&2
  exit 2
fi
run test -f "$RELEASE_DIR/artisan"
run test -f "$RELEASE_DIR/public/index.php"
run test ! -f "$RELEASE_DIR/.env"
run mkdir -p "$RELEASE_DIR/storage/app/private" "$RELEASE_DIR/storage/app/public" "$RELEASE_DIR/storage/framework/cache" "$RELEASE_DIR/storage/framework/sessions"
run ln -sfn "$RELEASE_DIR/public" "$WEB_ROOT"
run bash -lc "cd $(printf %q "$RELEASE_DIR") && php artisan app:environment-check --environment=production --strict --json"
run bash -lc "cd $(printf %q "$RELEASE_DIR") && php artisan migrate --force --no-interaction"
run bash -lc "cd $(printf %q "$RELEASE_DIR") && php artisan content:import-translations --locale=en --json"
run bash -lc "cd $(printf %q "$RELEASE_DIR") && php artisan optimize"
run bash -lc "cd $(printf %q "$RELEASE_DIR") && php artisan app:post-deploy-smoke --json"
