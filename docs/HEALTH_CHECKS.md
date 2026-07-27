# Health checks

`/health`, `/api/v1/health` and `/api/v1/status` expose only a compact system state and timestamp. The detailed component snapshot is intentionally admin-only. Cached checks cover application, database, cache, queue, scheduler, public/private storage, free disk and media processing.
