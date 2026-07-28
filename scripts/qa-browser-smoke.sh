#!/usr/bin/env bash
set -euo pipefail

base_url="${APP_URL:-http://127.0.0.1:8010}"
routes=(/ /tarifler /login)

for path in "${routes[@]}"; do
    status="$(curl --silent --show-error --output /dev/null --write-out '%{http_code}' "${base_url}${path}")"
    if [[ "${status}" != "200" ]]; then
        echo "Guest smoke failed: ${path} returned ${status}" >&2
        exit 1
    fi
done

php artisan qa:seed-fixtures --validate-only --json
echo '{"status":"passed","mode":"http-smoke","guest_routes":3,"authenticated_browser":"manual_checklist_required","note":"Run the documented browser playbook for Member A, Member B and admin UI evidence."}'
