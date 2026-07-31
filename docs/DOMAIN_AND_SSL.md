# Domain and SSL checklist

Before launch, point the chosen canonical domain to the hosting provider using the provider's documented A/AAAA or CNAME records. Redirect `www` to the canonical host (or the reverse) consistently, set `APP_URL` to that HTTPS URL, and configure `SESSION_DOMAIN` only when required.

- Issue and renew TLS certificates automatically; redirect all HTTP traffic to HTTPS.
- Configure trusted proxies before accepting forwarding or Cloudflare headers.
- Set secure cookies and a restrictive CORS allowlist.
- Publish `robots.txt` and the XML sitemap only after the canonical domain is live.
- Configure SPF, DKIM, and DMARC with the chosen mail provider.
- Search Console and analytics are follow-up account tasks; they are not silently enabled by the application.
- İlk yayın öncesi `APP_URL`, canonical redirect, `robots.txt`, sitemap index ve certificate chain [post-deploy smoke testindeki](POST_DEPLOY_SMOKE_TEST.md) sırayla doğrulanır.
