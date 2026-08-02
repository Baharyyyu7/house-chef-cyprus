# Hostinger First Deploy Runbook

This is a production operation runbook. It does not authorize a deploy by itself; an approved release, verified backup destination and production credentials are required.

## Preconditions

- Confirm the selected immutable SHA is the reviewed release on `visual-redesign-v2`.
- Build the frontend artifact locally or in CI with `scripts/build-production-artifact.sh`; Hostinger has no Node/npm dependency.
- Upload the asset archive and checksum through a private authenticated channel outside `public_html`.
- Create `shared/.env` from `.env.production.example` on the server. Do not copy its contents into chat, Git or deployment logs.
- Configure a read-only repository deploy key and verify `git ls-remote` without changing the repository.
- Confirm the backup destination can create and verify a backup before any migration.

## Exact first-deploy sequence

1. SSH to Hostinger and set only shell variables for the domain directory, immutable SHA, repository URL and private asset artifact path.
2. Validate the uploaded artifact checksum using its adjacent checksum file.
3. Run `scripts/deploy-hostinger-release.sh --dry-run` with the documented variables.
4. Review the dry-run output and confirm `public_html` contains only the Hostinger default landing page or other explicitly approved files.
5. Run the same command with `--adopt-hostinger-default-web-root`. It preserves the old `public_html` directory under `shared/webroot-backups/` rather than deleting it.
6. Add the minute scheduler and bounded database queue entries from `deploy/cron/housechef-hostinger-production.cron.example` in hPanel after the smoke test passes.
7. Verify HTTPS canonical routing, `/health`, application smoke output, scheduler heartbeat, queue health and backup verification.

## Recovery

If the deploy script exits after entering maintenance mode, its exit trap attempts `artisan up`. Do not remove releases automatically. To return application files to a known-good release, run `scripts/rollback-hostinger-release.sh --dry-run`, inspect it, then invoke it with `APP_HOME` and `TARGET_RELEASE`. Database migrations are forward-only; use an approved restore workflow only when a database restore is genuinely required.

## Persistent paths

`shared/.env`, `shared/storage` and `shared/logs` survive releases. `storage/app/private` is not web-accessible. Public local files resolve through the shell-created `public/storage` symlink; do not call `php artisan storage:link` on this plan.
