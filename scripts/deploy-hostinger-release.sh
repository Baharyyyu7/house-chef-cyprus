#!/usr/bin/env bash
set -euo pipefail

# Hostinger shared-hosting release deployer. It never invokes npm or storage:link.
# Build the asset artifact locally/CI and upload it privately before invoking this script.

DRY_RUN=false
ADOPT_WEB_ROOT=false
while (($#)); do
    case "$1" in
        --dry-run) DRY_RUN=true ;;
        --adopt-hostinger-default-web-root) ADOPT_WEB_ROOT=true ;;
        *) printf 'Unknown option: %s\n' "$1" >&2; exit 2 ;;
    esac
    shift
done

APP_HOME="${APP_HOME:?APP_HOME must be the absolute domain directory}"
REPOSITORY_URL="${REPOSITORY_URL:?REPOSITORY_URL must be a read-only repository URL}"
RELEASE_COMMIT="${RELEASE_COMMIT:?RELEASE_COMMIT must be the immutable commit SHA}"
ASSET_ARTIFACT="${ASSET_ARTIFACT:?ASSET_ARTIFACT must point to a prebuilt asset artifact}"
ASSET_CHECKSUM_FILE="${ASSET_CHECKSUM_FILE:-$ASSET_ARTIFACT.sha256}"
BRANCH="${BRANCH:-visual-redesign-v2}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
RELEASES_DIR="$APP_HOME/releases"
SHARED_DIR="$APP_HOME/shared"
CURRENT_LINK="$APP_HOME/current"
WEB_ROOT="$APP_HOME/public_html"
RELEASE_DIR="$RELEASES_DIR/$RELEASE_COMMIT"
MAINTENANCE_ENABLED=false
PREVIOUS_RELEASE=""
SWITCHED=false
WEB_ROOT_ADOPTED=false
WEB_ROOT_BACKUP=""

log() { printf '[hostinger-deploy] %s\n' "$*"; }
run() {
    if "$DRY_RUN"; then
        printf '[dry-run] '; printf '%q ' "$@"; printf '\n'
    else
        "$@"
    fi
}
artisan() { run "$PHP_BIN" "$RELEASE_DIR/artisan" "$@"; }
fail() { log "ERROR: $*"; exit 1; }
active_artisan() {
    if [[ -L "$CURRENT_LINK" && -f "$CURRENT_LINK/artisan" ]]; then
        printf '%s\n' "$CURRENT_LINK/artisan"
    else
        printf '%s\n' "$RELEASE_DIR/artisan"
    fi
}
rollback_post_switch_failure() {
    [[ "$SWITCHED" == true && -n "$PREVIOUS_RELEASE" && -f "$PREVIOUS_RELEASE/artisan" ]] || return 0

    log "Post-switch validation failed; restoring release symlinks to $PREVIOUS_RELEASE."
    run ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK" || true
    if [[ "$WEB_ROOT_ADOPTED" == true && -n "$WEB_ROOT_BACKUP" && -d "$WEB_ROOT_BACKUP" ]]; then
        run rm -f "$WEB_ROOT" || true
        run mv "$WEB_ROOT_BACKUP" "$WEB_ROOT" || true
    else
        run ln -sfn "$CURRENT_LINK/public" "$WEB_ROOT" || true
    fi
    run "$PHP_BIN" "$(active_artisan)" up --no-interaction || true
    SWITCHED=false
}
cleanup() {
    local status=$?
    if [[ "$status" -ne 0 ]]; then
        rollback_post_switch_failure
    fi
    if "$MAINTENANCE_ENABLED"; then
        log 'Leaving maintenance mode after deploy attempt.'
        "$PHP_BIN" "$(active_artisan)" up --no-interaction || true
    fi
    exit "$status"
}
trap cleanup EXIT

[[ "$APP_HOME" != / && "$APP_HOME" == /* ]] || fail 'APP_HOME must be a non-root absolute path.'
[[ "$RELEASE_COMMIT" =~ ^[0-9a-f]{7,64}$ ]] || fail 'RELEASE_COMMIT must be a hexadecimal commit SHA.'
[[ "$REPOSITORY_URL" =~ ^(git@|ssh://|https://) ]] || fail 'REPOSITORY_URL must be a Git SSH/HTTPS URL.'
[[ -f "$ASSET_ARTIFACT" ]] || fail 'Prebuilt asset artifact is missing.'
[[ -f "$ASSET_CHECKSUM_FILE" ]] || fail 'Artifact checksum file is missing.'

log "Validating artifact checksum and release inputs for $RELEASE_COMMIT."
if "$DRY_RUN"; then
    log "Would verify checksum file: $ASSET_CHECKSUM_FILE"
else
    (
        cd "$(dirname "$ASSET_ARTIFACT")"
        if command -v sha256sum >/dev/null 2>&1; then
            sha256sum -c "$(basename "$ASSET_CHECKSUM_FILE")"
        else
            shasum -a 256 -c "$(basename "$ASSET_CHECKSUM_FILE")"
        fi
    ) || fail 'Asset artifact checksum verification failed.'
fi
run tar -tzf "$ASSET_ARTIFACT" >/dev/null
run mkdir -p "$RELEASES_DIR" "$SHARED_DIR/storage/app/public" "$SHARED_DIR/storage/app/private" "$SHARED_DIR/storage/framework/cache" "$SHARED_DIR/storage/framework/sessions" "$SHARED_DIR/logs" "$SHARED_DIR/webroot-backups"
run test -f "$SHARED_DIR/.env"

if [[ -e "$RELEASE_DIR" ]]; then
    fail "Release directory already exists: $RELEASE_DIR. Use a new immutable commit release."
fi

log 'Creating immutable release from the read-only repository.'
run git clone --no-checkout --branch "$BRANCH" "$REPOSITORY_URL" "$RELEASE_DIR"
run git -C "$RELEASE_DIR" checkout --detach "$RELEASE_COMMIT"
if ! "$DRY_RUN"; then
    test "$(git -C "$RELEASE_DIR" rev-parse HEAD)" = "$RELEASE_COMMIT" || fail 'Checked-out release does not match RELEASE_COMMIT.'
fi

log 'Extracting and validating locally/CI-built frontend assets.'
run tar -xzf "$ASSET_ARTIFACT" -C "$RELEASE_DIR"
run test -f "$RELEASE_DIR/public/build/manifest.json"
if ! "$DRY_RUN"; then
    [[ "$(tr -d '[:space:]' < "$RELEASE_DIR/.house-chef-build-commit")" == "$RELEASE_COMMIT" ]] || fail 'Build artifact commit does not match RELEASE_COMMIT.'
fi

log 'Installing PHP production dependencies; npm is intentionally not used on the server.'
run composer install --working-dir="$RELEASE_DIR" --no-dev --prefer-dist --optimize-autoloader --no-interaction
run test ! -e "$RELEASE_DIR/.env"
run ln -s "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
run rm -rf "$RELEASE_DIR/storage"
run ln -s "$SHARED_DIR/storage" "$RELEASE_DIR/storage"
run mkdir -p "$RELEASE_DIR/bootstrap/cache"
run chmod ug+rwx "$RELEASE_DIR/bootstrap/cache" "$SHARED_DIR/storage"
if ! "$DRY_RUN" && [[ -e "$RELEASE_DIR/public/storage" && ! -L "$RELEASE_DIR/public/storage" ]]; then
    fail 'Release public/storage is unexpectedly a directory or file; refusing an unsafe symlink switch.'
fi
run ln -sfn "$SHARED_DIR/storage/app/public" "$RELEASE_DIR/public/storage"

log 'Checking production environment and creating a verified pre-deploy backup.'
artisan app:environment-check --environment=production --strict --json
artisan backup:create --type=pre-deploy --json
artisan backup:verify --json

if [[ -L "$CURRENT_LINK" && -f "$CURRENT_LINK/artisan" ]]; then
    log 'Enabling maintenance mode from the current verified release.'
    PREVIOUS_RELEASE="$(readlink -f "$CURRENT_LINK")"
    run "$PHP_BIN" "$CURRENT_LINK/artisan" down --render=errors::503 --retry=60 --no-interaction
    MAINTENANCE_ENABLED=true
fi

log 'Running forward-only production migration, caches and candidate-only checks.'
artisan migrate --force --no-interaction
artisan config:cache
artisan route:cache
artisan view:cache
artisan event:cache

log 'Atomically switching the current release symlink.'
if ! "$DRY_RUN" && [[ -e "$CURRENT_LINK" && ! -L "$CURRENT_LINK" ]]; then
    fail 'current exists but is not a symlink; refusing an unsafe release switch.'
fi
run ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"
SWITCHED=true
if [[ -d "$WEB_ROOT" && ! -L "$WEB_ROOT" ]]; then
    "$ADOPT_WEB_ROOT" || fail 'public_html is a directory. Re-run with --adopt-hostinger-default-web-root after reviewing its backup.'
    WEB_ROOT_BACKUP="$SHARED_DIR/webroot-backups/public_html.$(date -u +%Y%m%d%H%M%S)"
    run mv "$WEB_ROOT" "$WEB_ROOT_BACKUP"
    WEB_ROOT_ADOPTED=true
fi
run ln -sfn "$CURRENT_LINK/public" "$WEB_ROOT"

log 'Disabling maintenance mode on the newly active release.'
run "$PHP_BIN" "$CURRENT_LINK/artisan" up --no-interaction
MAINTENANCE_ENABLED=false

log 'Running live HTTP smoke only after maintenance mode is disabled.'
run "$PHP_BIN" "$CURRENT_LINK/artisan" app:post-deploy-smoke --json
run "$PHP_BIN" "$CURRENT_LINK/artisan" app:database-readiness --json
run "$PHP_BIN" "$CURRENT_LINK/artisan" backup:health --json

log 'Retaining the active release plus the three newest inactive releases.'
if ! "$DRY_RUN"; then
    mapfile -t stale_releases < <(find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -nr | awk 'NR > 4 {print $2}')
    for stale_release in "${stale_releases[@]:-}"; do
        [[ "$stale_release" == "$RELEASE_DIR" ]] || { log "Retained cleanup candidate (manual review required): $stale_release"; }
    done
fi

log "Deploy validation complete for $RELEASE_COMMIT. No release directories were deleted."
