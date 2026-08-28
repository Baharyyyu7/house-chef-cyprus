#!/usr/bin/env bash
set -euo pipefail

# Builds a complete, immutable Laravel release overlay for the Hostinger
# deployer. The deployer still checks out the exact read-only Git commit, then
# overlays this archive so the server never needs Node/npm. Secrets and runtime
# state remain outside the release in shared paths.

DRY_RUN=false
[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${ARTIFACT_OUTPUT_DIR:-$ROOT/build-artifacts}"
COMMIT_SHA="${ARTIFACT_COMMIT_SHA:-$(git -C "$ROOT" rev-parse HEAD)}"
SHORT_SHA="${COMMIT_SHA:0:7}"
ARTIFACT="$OUTPUT_DIR/house-chef-cyprus-full-$SHORT_SHA.tar.gz"
STAGING_DIR=""

run() { if "$DRY_RUN"; then printf '[dry-run] '; printf '%q ' "$@"; printf '\n'; else "$@"; fi; }

# Arşiv çalışma ağacından paketleniyor ama damga `git rev-parse HEAD` diyor.
# Ağaç kirliyse ikisi ayrışır: commit edilmemiş kod, onu içermeyen bir commit
# adıyla üretime gider. Bu depoda birden fazla ajan çalıştığı için sessizce
# başkasının yarım işini yayınlamak somut bir risk - bir kez gerçekleşti.
#
# Sürüm dosyaları temiz olmalı. Kasıtlı bir istisna gerekiyorsa
# ALLOW_DIRTY_BUILD=1 ile açıkça belirtilir.
surum_yollari=(artisan composer.json composer.lock app bootstrap config database public resources routes scripts storage lang)
kirli="$(git -C "$ROOT" status --porcelain -- "${surum_yollari[@]}" | grep -v '^?? ' || true)"

if [[ -n "$kirli" && "${ALLOW_DIRTY_BUILD:-0}" != "1" ]]; then
    printf 'HATA: calisma agacinda commit edilmemis surum dosyalari var.\n' >&2
    printf 'Artefakt calisma agacindan paketlenir; bunlar %s damgasiyla uretime giderdi.\n' "${COMMIT_SHA:0:7}" >&2
    printf '%s\n' "$kirli" >&2
    printf 'Temiz bir kopyadan uretin (git worktree) ya da bilerek yapiyorsaniz ALLOW_DIRTY_BUILD=1 verin.\n' >&2
    exit 1
fi

cd "$ROOT"
cleanup() { [[ -z "$STAGING_DIR" ]] || rm -rf "$STAGING_DIR"; }
trap cleanup EXIT

run npm ci --no-audit --no-fund
run npm run build
run test -f "$ROOT/public/build/manifest.json"
run mkdir -p "$OUTPUT_DIR"

if ! "$DRY_RUN"; then
    STAGING_DIR="$(mktemp -d "${TMPDIR:-/tmp}/house-chef-release.XXXXXX")"
    mkdir -p "$STAGING_DIR/storage/app/public" "$STAGING_DIR/storage/app/private" \
        "$STAGING_DIR/storage/framework/cache" "$STAGING_DIR/storage/framework/sessions" \
        "$STAGING_DIR/storage/framework/views" "$STAGING_DIR/storage/logs"

    # The explicit source list prevents accidental inclusion of local tooling,
    # artifacts and credentials. Exclusions protect runtime files nested below
    # the required application directories.
    tar -C "$ROOT" \
        --exclude='database/*.sqlite' \
        --exclude='database/*.sqlite-*' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='storage/app/public/*' \
        --exclude='storage/app/private/backups/*' \
        --exclude='storage/app/media-import/*' \
        --exclude='public/storage' \
        --exclude='.DS_Store' \
        --exclude='._*' \
        -cf - artisan composer.json composer.lock app bootstrap config database public resources routes scripts storage lang \
        | tar -C "$STAGING_DIR" -xf -

    printf '%s\n' "$COMMIT_SHA" > "$STAGING_DIR/.house-chef-build-commit"
    tar -C "$STAGING_DIR" -czf "$ARTIFACT" .

    for required in composer.json composer.lock artisan app bootstrap config database public resources routes scripts storage public/build/manifest.json; do
        test -e "$STAGING_DIR/$required"
    done
    test ! -e "$STAGING_DIR/.env"
    test ! -e "$STAGING_DIR/vendor"
    test ! -e "$STAGING_DIR/node_modules"
    test ! -e "$STAGING_DIR/tests"
fi

if ! "$DRY_RUN"; then
    (
        cd "$OUTPUT_DIR"
        sha256sum "$(basename "$ARTIFACT")" 2>/dev/null || shasum -a 256 "$(basename "$ARTIFACT")"
    ) > "$ARTIFACT.sha256"
    printf 'artifact=%s\nchecksum_file=%s\ncommit=%s\n' "$ARTIFACT" "$ARTIFACT.sha256" "$COMMIT_SHA"
fi
