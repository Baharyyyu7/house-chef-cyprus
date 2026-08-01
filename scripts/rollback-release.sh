#!/usr/bin/env bash
set -euo pipefail
CURRENT_LINK="${CURRENT_LINK:?CURRENT_LINK must point to current release symlink}"
PREVIOUS_RELEASE="${PREVIOUS_RELEASE:?PREVIOUS_RELEASE must point to a verified prior release}"
DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true
if [[ "$CURRENT_LINK" == / || "$PREVIOUS_RELEASE" == / ]]; then echo 'Refusing broad filesystem target.' >&2; exit 2; fi
if "$DRY_RUN"; then
  printf '[dry-run] validate manifest, repoint %q to %q, clear caches and smoke test\n' "$CURRENT_LINK" "$PREVIOUS_RELEASE"
  exit 0
fi
test -f "$PREVIOUS_RELEASE/artisan"
ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK"
cd "$CURRENT_LINK"
php artisan optimize:clear
php artisan app:post-deploy-smoke --json
