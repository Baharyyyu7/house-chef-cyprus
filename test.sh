#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/scripts/lib.sh"
cd "$PROJECT_ROOT"
"$(php_bin)" artisan test && "$(php_bin)" vendor/bin/pint --test && "$(php_bin)" vendor/bin/phpstan analyse --memory-limit=1G && npm run build
