#!/usr/bin/env bash
set -euo pipefail
[[ $# -eq 1 ]] || { echo 'Kullanım: ./restore.sh BACKUP_ID'; exit 1; }
cd "$(dirname "$0")"
php artisan backup:restore "$1" --target=testing --verify-only
