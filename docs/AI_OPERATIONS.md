# AI Operations

Admin-only `/yonetim/ai-operasyonlari` shows provider health, request count, cache hits, safe failures, fallback count, latency, cost and safety-event counts. Commands:

```bash
php artisan ai-kitchen:health --json
php artisan ai-kitchen:cost-report --json
php artisan ai-kitchen:morning-brief --json
```

The scheduled morning brief is read-only and never sends a provider request or modifies content.
