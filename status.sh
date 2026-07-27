#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "$0")/scripts/lib.sh"
status_process 'Laravel' "$SERVER_PID"; status_process 'Queue' "$QUEUE_PID"; status_process 'Scheduler' "$SCHEDULER_PID"; status_process 'Vite' "$VITE_PID"
