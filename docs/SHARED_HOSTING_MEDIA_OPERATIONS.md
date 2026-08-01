# Shared Hosting Media Operations

All Milestone D commands use bounded batch limits and can be run by cron. Database queues remain sufficient for thumbnails; no VPS, Redis or long-running worker is required. Run provider searches deliberately, use `--dry-run` first, and resume small optimization batches after a timeout or disk-quota incident.

There is no automatic public release path. Human review remains the release boundary on shared hosting and managed infrastructure alike.
