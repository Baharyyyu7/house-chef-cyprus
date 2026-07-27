#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUNTIME_DIR="$PROJECT_ROOT/storage/app/runtime"
SERVER_PID="$RUNTIME_DIR/server.pid"
QUEUE_PID="$RUNTIME_DIR/queue.pid"
SCHEDULER_PID="$RUNTIME_DIR/scheduler.pid"
VITE_PID="$RUNTIME_DIR/vite.pid"
LOG_DIR="$PROJECT_ROOT/storage/logs"
APP_PORT="${HOUSE_CHEF_PORT:-8010}"

php_bin() { command -v php >/dev/null 2>&1 && command -v php || printf '%s\n' "$HOME/.config/herd-lite/bin/php"; }
ensure_runtime() { mkdir -p "$RUNTIME_DIR" "$LOG_DIR"; }
pid_running() { [[ -f "$1" ]] && kill -0 "$(cat "$1")" 2>/dev/null; }
remove_stale_pid() { [[ -f "$1" ]] && ! pid_running "$1" && rm -f "$1"; }
port_in_use() { lsof -nP -iTCP:"$APP_PORT" -sTCP:LISTEN >/dev/null 2>&1; }
start_process() { local pid="$1" log="$2"; shift 2; remove_stale_pid "$pid"; if pid_running "$pid"; then return; fi; "$@" >>"$log" 2>&1 & echo $! >"$pid"; }
stop_process() { local pid="$1"; remove_stale_pid "$pid"; if pid_running "$pid"; then kill "$(cat "$pid")" 2>/dev/null || true; for _ in 1 2 3 4 5; do pid_running "$pid" || break; sleep 1; done; fi; remove_stale_pid "$pid"; }
status_process() { local label="$1" pid="$2"; if pid_running "$pid"; then echo "$label: çalışıyor (PID $(cat "$pid"))"; else echo "$label: durdu"; fi; }
