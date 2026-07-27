#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"; cd "$ROOT"
if git grep -nEI '(AKIA[0-9A-Z]{16}|-----BEGIN (RSA|EC|OPENSSH) PRIVATE KEY-----|github_pat_[A-Za-z0-9_]{20,}|ghp_[A-Za-z0-9]{30,})' -- ':!docs' ':!*.lock'; then
  echo 'Olası gizli bilgi bulundu.' >&2; exit 1
fi
echo 'Secret scan passed.'
