# SEO Monitoring

Run `php artisan seo:audit --json` after material catalogue or metadata changes and `php artisan seo:health --json` for a machine-readable health snapshot. The dashboard reports issue severity, indexable catalogue counts, media coverage, linked articles and approved topic counts. These are operational counts, not search-engine performance claims.

Search Console and IndexNow remain opt-in integrations. Provider health is safe by default and disabled providers make no network request.
