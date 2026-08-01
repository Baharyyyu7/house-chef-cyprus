# Production Security

Launch only with debug disabled, HTTPS, canonical host, secure/HTTP-only/SameSite cookies, CSRF and rate limits, private media authorization, upload limits, trusted proxies and server-level denial of `.env`, vendor, database, config and private storage. Start CSP in report-only mode if third-party behavior has not been verified; enable HSTS only after TLS verification.
