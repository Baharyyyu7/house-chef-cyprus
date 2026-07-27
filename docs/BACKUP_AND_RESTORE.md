# Backup and restore

SQLite backups are stored under private storage and contain only the database plus a checksum-protected manifest; `.env`, credentials and private raw files are excluded. Use `backup:create`, `backup:list` and `backup:verify`. `backup:restore` verifies first and rejects live/production targets; the current safe contract permits testing-target validation only.
