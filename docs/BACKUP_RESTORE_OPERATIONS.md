# Backup and Restore Operations

Backups are private, checksummed manifests with public IDs and retention metadata. Use `backup:create --type=pre-deploy`, `backup:verify`, `backup:restore-test --target=testing`, `backup:cleanup --dry-run`, and `backup:health --json`. Restore tests never target the production database. Never expose backups, checksums with storage paths, or environment files publicly.
