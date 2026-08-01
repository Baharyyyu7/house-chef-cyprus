# SSH Deployment

Use the guarded `deploy/scripts/deploy.sh` from a versioned release directory. It installs production dependencies, builds assets, migrates with `--force`, caches configuration/routes/views/events, validates the environment and runs smoke checks. Set `APP_ROOT` explicitly; run `--dry-run` first. Roll back code/assets through a known verified release, then run smoke checks; database rollback requires a separately approved plan.
