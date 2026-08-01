#!/usr/bin/env bash
set -euo pipefail

DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${ARTIFACT_OUTPUT_DIR:-$ROOT/build-artifacts}"
VERSION="${ARTIFACT_VERSION:-$(date -u +%Y%m%d%H%M%S)}"
ARTIFACT="$OUTPUT_DIR/house-chef-cyprus-$VERSION.tar.gz"

run() { if "$DRY_RUN"; then printf '[dry-run] '; printf '%q ' "$@"; printf '\n'; else "$@"; fi; }

cd "$ROOT"
run npm run build
run composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
run mkdir -p "$OUTPUT_DIR"
run tar \
  --exclude='.env' --exclude='.env.*' --exclude='.git' --exclude='node_modules' \
  --exclude='storage/logs/*' --exclude='storage/framework/cache/*' --exclude='storage/framework/sessions/*' \
  --exclude='storage/app/private/backups/*' --exclude='build-artifacts' \
  -czf "$ARTIFACT" .
if ! "$DRY_RUN"; then
  sha256sum "$ARTIFACT" > "$ARTIFACT.sha256" 2>/dev/null || shasum -a 256 "$ARTIFACT" > "$ARTIFACT.sha256"
  printf 'artifact=%s\nchecksum_file=%s\n' "$ARTIFACT" "$ARTIFACT.sha256"
fi
