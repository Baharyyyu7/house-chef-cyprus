# Monitoring and Health

`/health` is intentionally minimal. Detailed operational health is admin-protected. CLI health commands report `healthy`, `degraded`, `unhealthy`, `unavailable` or `not_configured`, never synthetic healthy data. Configure external uptime monitoring only after a real endpoint/token is provisioned.
