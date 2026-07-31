#!/usr/bin/env bash
set -euo pipefail

CURRENT_LINK="${CURRENT_LINK:?CURRENT_LINK must be the current release symlink}"
PREVIOUS_RELEASE="${PREVIOUS_RELEASE:?PREVIOUS_RELEASE must be a verified prior release directory}"
DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true

if "$DRY_RUN"; then
  printf '[dry-run] ln -sfn %q %q\n' "$PREVIOUS_RELEASE" "$CURRENT_LINK"
  printf '[dry-run] queue restart, cache reset and smoke test from previous release\n'
  exit 0
fi
ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK"
cd "$CURRENT_LINK"
php artisan optimize:clear
php artisan queue:restart
php artisan app:post-deploy-smoke --json
