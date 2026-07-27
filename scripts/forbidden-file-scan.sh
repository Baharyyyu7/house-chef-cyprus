#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"; cd "$ROOT"
for pattern in '^\.env$' '^database/.*\.sqlite$' '^storage/app/.*/?backups/' '(^|/)\.DS_Store$' '(^|/)auth\.json$'; do
  if git ls-files | grep -E "$pattern"; then echo "Yasak dosya izlendi: $pattern" >&2; exit 1; fi
done
echo 'Forbidden-file scan passed.'
