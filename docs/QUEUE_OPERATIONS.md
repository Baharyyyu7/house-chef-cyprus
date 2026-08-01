# Queue Operations

Use database queue by default on shared hosting; Redis is optional. Operate named queues `default`, `media`, `content`, `ai`, `seo`, `mail`, and `maintenance` with bounded timeout/retry/backoff policies. Check `app:queue-health --json`; retry/forget remains admin-only and payloads must not disclose secrets or unnecessary user data.
