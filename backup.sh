#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
php artisan backup:create
