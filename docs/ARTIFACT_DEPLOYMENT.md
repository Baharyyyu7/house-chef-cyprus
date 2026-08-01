# Artifact Deployment

Build CI/local release artifacts through `scripts/build-production-artifact.sh`. The artifact includes optimized vendor and built assets but excludes `.env`, VCS data, logs, sessions, cache data and private backups. A SHA-256 sidecar is generated. Upload only after checksum verification and configure environment variables on the host independently.
