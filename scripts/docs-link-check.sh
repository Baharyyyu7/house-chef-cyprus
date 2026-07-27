#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"; cd "$ROOT"
missing=0
while IFS= read -r match; do
  file="${match%%:*}"; target="${match#*:](}"
  [[ "$target" =~ ^https?:// || "$target" == \#* || -z "$target" ]] && continue
  target="${target%%#*}"
  [[ -e "$(dirname "$file")/$target" ]] || { echo "Kırık yerel belge bağlantısı: $file -> $target" >&2; missing=1; }
done < <(grep -RHEo '\]\(([^ )]+)' README.md SECURITY.md CONTRIBUTING.md docs 2>/dev/null)
[[ "$missing" -eq 0 ]] && echo 'Documentation link check passed.'
