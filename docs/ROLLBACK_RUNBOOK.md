# Rollback Runbook

Use `scripts/rollback-release.sh --dry-run` before a real rollback. Confirm a prior release is verified, announce maintenance, repoint only the current release link, clear caches and run smoke tests. Database rollback is not automatic; use forward fix or separately approved restore as a last resort.
