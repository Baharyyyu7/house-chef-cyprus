#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"; cd "$ROOT"
missing=0
while IFS= read -r target; do
  [[ "$target" =~ ^https?:// || "$target" == \#* || -z "$target" ]] && continue
  target="${target%%#*}"
  [[ -e "$target" ]] || { echo "Kırık yerel belge bağlantısı: $target" >&2; missing=1; }
done < <(grep -RhoE '\]\(([^ )]+)' README.md SECURITY.md CONTRIBUTING.md docs 2>/dev/null | sed -E 's/^\]\(//')
[[ "$missing" -eq 0 ]] && echo 'Documentation link check passed.'
