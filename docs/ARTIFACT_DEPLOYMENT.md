# Artifact Deployment

Build CI/local release artifacts through `scripts/build-production-artifact.sh`. The artifact is a complete Laravel release overlay (application source, migrations, public assets and built Vite manifest); it intentionally excludes `.env`, VCS data, `vendor`, `node_modules`, tests, logs, sessions, cache data and private backups. Composer is installed on the server from the exact locked source release. A SHA-256 sidecar is generated. Upload only after checksum verification and configure environment variables on the host independently.
