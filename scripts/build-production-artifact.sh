#!/usr/bin/env bash
set -euo pipefail

DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${ARTIFACT_OUTPUT_DIR:-$ROOT/build-artifacts}"
COMMIT_SHA="${ARTIFACT_COMMIT_SHA:-$(git -C "$ROOT" rev-parse HEAD)}"
VERSION="${ARTIFACT_VERSION:-$COMMIT_SHA}"
ARTIFACT="$OUTPUT_DIR/house-chef-cyprus-assets-$VERSION.tar.gz"
STAGING_DIR=""

run() { if "$DRY_RUN"; then printf '[dry-run] '; printf '%q ' "$@"; printf '\n'; else "$@"; fi; }

cd "$ROOT"
cleanup() { [[ -z "$STAGING_DIR" ]] || rm -rf "$STAGING_DIR"; }
trap cleanup EXIT

run npm ci --no-audit --no-fund
run npm run build
run test -f "$ROOT/public/build/manifest.json"
run mkdir -p "$OUTPUT_DIR"

if ! "$DRY_RUN"; then
  STAGING_DIR="$(mktemp -d "${TMPDIR:-/tmp}/house-chef-assets.XXXXXX")"
  mkdir -p "$STAGING_DIR/public"
  cp -R "$ROOT/public/build" "$STAGING_DIR/public/build"
  printf '%s\n' "$COMMIT_SHA" > "$STAGING_DIR/.house-chef-build-commit"
  tar -C "$STAGING_DIR" -czf "$ARTIFACT" public/build .house-chef-build-commit
fi
if ! "$DRY_RUN"; then
  (
    cd "$OUTPUT_DIR"
    sha256sum "$(basename "$ARTIFACT")" 2>/dev/null || shasum -a 256 "$(basename "$ARTIFACT")"
  ) > "$ARTIFACT.sha256"
  printf 'artifact=%s\nchecksum_file=%s\ncommit=%s\n' "$ARTIFACT" "$ARTIFACT.sha256" "$COMMIT_SHA"
fi
