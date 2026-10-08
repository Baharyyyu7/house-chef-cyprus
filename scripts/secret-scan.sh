#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"; cd "$ROOT"
# docs/ artik taraniyor: zamanlayici anahtari bir kez ornek komutla belgeye
# ve is akisina acik metin olarak girdi. Kilit dosyalari yalniz hash tasir.
DESEN='(AKIA[0-9A-Z]{16}|-----BEGIN (RSA|EC|OPENSSH) PRIVATE KEY-----|github_pat_[A-Za-z0-9_]{20,}|ghp_[A-Za-z0-9]{30,}|_zamanlayici/[0-9a-f]{32,}|sk_live_[A-Za-z0-9]{20,}|sk-ant-[A-Za-z0-9_-]{20,})'
if git grep -nEI "$DESEN" -- ':!*.lock' ':!package-lock.json'; then
  echo 'Olası gizli bilgi bulundu.' >&2; exit 1
fi
echo 'Secret scan passed.'
