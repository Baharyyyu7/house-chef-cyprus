# Backup and restore

SQLite backups are stored under private storage and contain only the database plus a checksum-protected manifest; `.env`, credentials and private raw files are excluded. Use `backup:create`, `backup:list` and `backup:verify`. `backup:restore` verifies first and rejects live/production targets; the current safe contract permits testing-target validation only.

Productionda backup diski uygulama sunucusundan ayrı, erişimi kısıtlı ve saklama politikası tanımlı bir hedef olmalıdır. Her release öncesi backup verify, düzenli olarak da güvenli testing target restore drill'i yapılır; canlı production restore ancak yazma trafiği durdurulup operasyon onayı alındıktan sonra uygulanır.
