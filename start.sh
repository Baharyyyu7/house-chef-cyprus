#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/scripts/lib.sh"
cd "$PROJECT_ROOT"; ensure_runtime
[[ -f .env ]] || { echo 'Önce ./setup.sh çalıştırın.'; exit 1; }
if port_in_use && ! pid_running "$SERVER_PID"; then echo "Port $APP_PORT başka bir süreç tarafından kullanılıyor."; exit 1; fi
start_process "$SERVER_PID" "$LOG_DIR/server.log" "$(php_bin)" artisan serve --host=127.0.0.1 --port="$APP_PORT"
start_process "$QUEUE_PID" "$LOG_DIR/queue-worker.log" "$(php_bin)" artisan queue:work --sleep=1 --tries=3 --timeout=120
start_process "$SCHEDULER_PID" "$LOG_DIR/scheduler.log" "$(php_bin)" artisan schedule:work
start_process "$VITE_PID" "$LOG_DIR/vite.log" npm run dev -- --host 127.0.0.1
echo "Uygulama http://127.0.0.1:$APP_PORT üzerinde başlatıldı. Loglar: $LOG_DIR"
