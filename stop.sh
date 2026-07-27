#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/scripts/lib.sh"
stop_process "$VITE_PID"; stop_process "$SCHEDULER_PID"; stop_process "$QUEUE_PID"; stop_process "$SERVER_PID"
echo 'Yerel süreçler durduruldu.'
