# Rollback Runbook

Use `scripts/rollback-hostinger-release.sh --dry-run` for the Hostinger release layout. Confirm the target is a verified directory under `releases/`, announce maintenance, repoint `current` and `public_html`, clear caches and run smoke tests. Database rollback is not automatic; use a forward fix or separately approved restore as a last resort.
