# Hostinger Shared Hosting Deployment

This runbook uses immutable releases and never runs Node/npm on Hostinger. Build a commit-bound frontend artifact locally or in CI, upload it privately, and run `scripts/deploy-hostinger-release.sh` over SSH. The server clones only with a read-only deploy key.

## Layout and web root

```text
~/domains/housechefcyprus.com/
  releases/<commit-sha>/
  shared/.env
  shared/storage/
  shared/logs/
  current -> releases/<commit-sha>
  public_html -> current/public
```

`public_html` begins as a Hostinger directory. The first deploy requires the explicit `--adopt-hostinger-default-web-root` option, which moves that directory to `shared/webroot-backups/`; it never deletes it. Later deploys switch symlinks atomically.

## First release

1. In CI/local, check out the exact release SHA and run `ARTIFACT_COMMIT_SHA=<sha> scripts/build-production-artifact.sh`.
2. Upload the generated `house-chef-cyprus-assets-<sha>.tar.gz` and its checksum through an authenticated private channel. Verify the checksum over SSH.
3. Create `shared/.env` from `.env.production.example` outside Git. Supply real secrets only in that server file.
4. Add a read-only Git deploy key to the repository and verify it can clone the selected branch.
5. Run the deployer once with `--dry-run`, then run it with `--adopt-hostinger-default-web-root` only after reviewing `public_html`.
6. Add the entries from `deploy/cron/housechef-hostinger-production.cron.example` with the actual absolute app path.

### Read-only deploy key

Generate the key on the deployment machine or use an existing dedicated production key. Register only its public half as a **read-only deploy key** on `Baharyyyu7/house-chef-cyprus`; never add it as a personal key and never commit the private half. Verify with a non-mutating `git ls-remote` before the first release. The deployer accepts the SSH repository URL through `REPOSITORY_URL`; do not place credentials in that variable or in shell history.

Example validation invocation (does not change the server):

```bash
APP_HOME="$HOME/domains/housechefcyprus.com" \
REPOSITORY_URL='git@github.com:Baharyyyu7/house-chef-cyprus.git' \
RELEASE_COMMIT='<full-sha>' \
ASSET_ARTIFACT="$HOME/private-artifacts/house-chef-cyprus-assets-<sha>.tar.gz" \
scripts/deploy-hostinger-release.sh --dry-run
```

The live invocation uses the same variables and adds `--adopt-hostinger-default-web-root` on the first release only. It uses `migrate --force`; it never uses `migrate:fresh`.

See [Hostinger first-deploy runbook](HOSTINGER_FIRST_DEPLOY_RUNBOOK.md) for the gated production sequence and [rollback runbook](ROLLBACK_RUNBOOK.md) for application-file rollback.

## Storage, queue and rollback

PHP-level `storage:link` is not used because PHP symlink is disabled on this Hostinger plan. The deployer uses shell `ln -s` for `release/storage -> shared/storage` and `release/public/storage -> shared/storage/app/public`. Private media remains outside public storage.

Use database queue plus the bounded cron worker shown in the cron example unless a separately provisioned Redis worker is available. Scheduler runs every minute.

Run `scripts/rollback-hostinger-release.sh --dry-run` before a live rollback. It changes application symlinks only; migrations are forward-only, so database rollback requires a separately approved restore plan.
