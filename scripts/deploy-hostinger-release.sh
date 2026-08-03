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
web_root_index() { printf '%s/index.php\n' "$WEB_ROOT"; }
web_root_storage() { printf '%s/storage\n' "$WEB_ROOT"; }

# Hostinger's HTTP layer does not reliably follow a public_html symlink that
# crosses the domain root. Keep public_html physical and stable; only current
# is switched atomically between immutable releases.
ensure_physical_web_root() {
    if "$DRY_RUN"; then
        log "Would ensure physical Hostinger web root at $WEB_ROOT."
        return 0
    fi

    if [[ -L "$WEB_ROOT" ]]; then
        log 'Replacing public_html release symlink with the Hostinger physical web-root fallback.'
        rm -f "$WEB_ROOT"
    fi
    [[ ! -e "$WEB_ROOT" || -d "$WEB_ROOT" ]] || fail 'public_html exists but is not a directory or symlink.'
    mkdir -p "$WEB_ROOT"
}

sync_hostinger_web_root() {
    ensure_physical_web_root
    if "$DRY_RUN"; then
        log "Would sync $CURRENT_LINK/public into physical $WEB_ROOT while preserving host-only hidden files."
        return 0
    fi

    # tar copies dotfiles too, does not delete Hostinger-owned hidden files,
    # and excludes the release storage link so it can be recreated below.
    (
        cd "$CURRENT_LINK/public"
        tar --exclude='./storage' -cf - .
    ) | (
        cd "$WEB_ROOT"
        tar -xf -
    )
}

write_hostinger_web_root_index() {
    if "$DRY_RUN"; then
        log "Would write stable Hostinger bootstrap index at $(web_root_index)."
        return 0
    fi

    cat > "$(web_root_index)" <<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Http\Request;

require __DIR__.'/../current/vendor/autoload.php';

$app = require_once __DIR__.'/../current/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP
}

recreate_web_root_storage_link() {
    local storage_link
    storage_link="$(web_root_storage)"
    if "$DRY_RUN"; then
        log "Would recreate $storage_link -> $SHARED_DIR/storage/app/public."
        return 0
    fi

    if [[ -e "$storage_link" && ! -L "$storage_link" ]]; then
        WEB_ROOT_BACKUP="$SHARED_DIR/webroot-backups/storage.$(date -u +%Y%m%d%H%M%S)"
        log "Preserving existing public_html/storage at $WEB_ROOT_BACKUP before replacing it with the shared storage link."
        mv "$storage_link" "$WEB_ROOT_BACKUP"
    else
        rm -f "$storage_link"
    fi
    ln -s "$SHARED_DIR/storage/app/public" "$storage_link"
}

verify_hostinger_web_root() {
    if "$DRY_RUN"; then
        log 'Would validate physical public_html, stable bootstrap paths, .htaccess, Vite assets, and storage link.'
        return 0
    fi

    test -f "$(web_root_index)" || fail 'HOSTINGER_WEB_ROOT_INDEX_MISSING'
    test -f "$CURRENT_LINK/public/index.php" || fail 'HOSTINGER_RELEASE_PUBLIC_INDEX_MISSING'
    grep -Fq "__DIR__.'/../current/vendor/autoload.php'" "$(web_root_index)" || fail 'HOSTINGER_WEB_ROOT_AUTOLOAD_PATH_INVALID'
    grep -Fq "__DIR__.'/../current/bootstrap/app.php'" "$(web_root_index)" || fail 'HOSTINGER_WEB_ROOT_BOOTSTRAP_PATH_INVALID'
    test -f "$WEB_ROOT/.htaccess" || fail 'HOSTINGER_WEB_ROOT_HTACCESS_MISSING'
    test -f "$WEB_ROOT/build/manifest.json" || fail 'HOSTINGER_WEB_ROOT_BUILD_MANIFEST_MISSING'
    test -L "$(web_root_storage)" || fail 'HOSTINGER_WEB_ROOT_STORAGE_LINK_MISSING'
    [[ "$(readlink -f "$(web_root_storage)")" == "$(readlink -f "$SHARED_DIR/storage/app/public")" ]] || fail 'HOSTINGER_WEB_ROOT_STORAGE_LINK_INVALID'
}

prepare_hostinger_web_root() {
    sync_hostinger_web_root
    write_hostinger_web_root_index
    recreate_web_root_storage_link
    verify_hostinger_web_root
}

hostinger_404_diagnostic() {
    "$DRY_RUN" && return 0
    log "HOSTINGER_WEB_ROOT_DIAGNOSTIC public_html=$WEB_ROOT current_target=$(readlink -f "$CURRENT_LINK" 2>/dev/null || printf unavailable)"
    log "HOSTINGER_WEB_ROOT_DIAGNOSTIC index_exists=$([[ -f "$(web_root_index)" ]] && printf yes || printf no) htaccess_exists=$([[ -f "$WEB_ROOT/.htaccess" ]] && printf yes || printf no) build_manifest_exists=$([[ -f "$WEB_ROOT/build/manifest.json" ]] && printf yes || printf no) storage_link=$([[ -L "$(web_root_storage)" ]] && printf yes || printf no)"
    if [[ -f "$(web_root_index)" ]]; then
        log "HOSTINGER_WEB_ROOT_DIAGNOSTIC autoload_path=$([[ $(grep -Fc "__DIR__.'/../current/vendor/autoload.php'" "$(web_root_index)") -gt 0 ]] && printf current || printf invalid) bootstrap_path=$([[ $(grep -Fc "__DIR__.'/../current/bootstrap/app.php'" "$(web_root_index)") -gt 0 ]] && printf current || printf invalid)"
    fi
}

is_safe_release_directory() {
    local candidate="$1"
    local releases_real candidate_real release_name

    [[ -d "$candidate" && ! -L "$candidate" ]] || return 1
    release_name="$(basename "$candidate")"
    [[ "$release_name" =~ ^[0-9a-f]{7,64}$ ]] || return 1
    releases_real="$(cd -P "$RELEASES_DIR" && pwd)" || return 1
    candidate_real="$(cd -P "$candidate" && pwd)" || return 1
    [[ "$candidate_real" == "$releases_real/"* ]] || return 1
}

prune_old_releases() {
    local listing candidate timestamp inactive_kept=0
    listing="$(mktemp "${TMPDIR:-/tmp}/housechef-release-cleanup.XXXXXX")" || {
        log 'WARNING: release cleanup skipped because its temporary listing could not be created.'
        return 1
    }

    # No process substitution or /dev/fd: Hostinger's shared shell does not
    # reliably provide either. A regular temporary file is portable here.
    if ! find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -nr > "$listing"; then
        log 'WARNING: release cleanup skipped because release listing failed.'
        rm -f "$listing"
        return 1
    fi

    while IFS=' ' read -r timestamp candidate; do
        [[ -n "${candidate:-}" ]] || continue
        if ! is_safe_release_directory "$candidate"; then
            log "WARNING: skipped unsafe release cleanup candidate: $candidate"
            continue
        fi
        if [[ "$candidate" == "$RELEASE_DIR" || ( -n "$PREVIOUS_RELEASE" && "$candidate" == "$PREVIOUS_RELEASE" ) ]]; then
            log "Retaining protected release: $candidate"
            continue
        fi
        if (( inactive_kept < 3 )); then
            inactive_kept=$((inactive_kept + 1))
            log "Retaining newest inactive release: $candidate"
            continue
        fi

        log "Removing stale inactive release: $candidate"
        if ! rm -rf -- "$candidate"; then
            log "WARNING: failed to remove stale release; deployment remains successful: $candidate"
            rm -f "$listing"
            return 1
        fi
    done < "$listing"

    rm -f "$listing"
    return 0
}

rollback_post_switch_failure() {
    [[ "$SWITCHED" == true && -n "$PREVIOUS_RELEASE" && -f "$PREVIOUS_RELEASE/artisan" ]] || return 0

    log "Post-switch validation failed; restoring release symlinks to $PREVIOUS_RELEASE."
    run ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK" || true
    prepare_hostinger_web_root || true
    hostinger_404_diagnostic
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
artisan seo:deployment-guard --environment=production --json
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
if [[ -d "$RELEASE_DIR/resources/editorial/blog-synthetic" ]]; then
    log 'Importing the versioned, production-approved synthetic blog illustrations.'
    artisan content:import-approved-synthetic-blog-media --source-directory="$RELEASE_DIR/resources/editorial/blog-synthetic" --json
fi
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
prepare_hostinger_web_root

log 'Disabling maintenance mode on the newly active release.'
run "$PHP_BIN" "$CURRENT_LINK/artisan" up --no-interaction
MAINTENANCE_ENABLED=false

log 'Running live HTTP smoke only after maintenance mode is disabled.'
if ! run "$PHP_BIN" "$CURRENT_LINK/artisan" app:post-deploy-smoke --json; then
    hostinger_404_diagnostic
    fail 'HOSTINGER_POST_SWITCH_SMOKE_FAILED'
fi
run "$PHP_BIN" "$CURRENT_LINK/artisan" app:database-readiness --json
run "$PHP_BIN" "$CURRENT_LINK/artisan" backup:health --json

log 'Running best-effort release retention only after all critical validations.'
if "$DRY_RUN"; then
    log 'Would retain the active release, the rollback release, and three newest inactive releases.'
elif ! prune_old_releases; then
    log 'WARNING: release retention cleanup can be retried later; it will not roll back a healthy deployment.'
fi

log "Deploy validation complete for $RELEASE_COMMIT."
