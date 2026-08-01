# SEO Architecture — Milestone C

House Chef Cyprus uses server-rendered, canonical public pages as the SEO source of truth. Published and verified recipes, and published blog posts whose publication time has arrived, are the only catalogue records eligible for public sitemaps.

Metadata is owned by `seo_metadata`; fallback titles and descriptions are generated only from the same visible record. Canonical overrides are accepted only for the application host. Staging or an explicitly disabled indexing environment emits `noindex,nofollow`.

The `seo:audit` command records reproducible issues (not guessed rankings), and `seo:health --json` reports only catalogue, relationship and open-issue counts. Neither command contacts providers by default or invents traffic, authority, query volume, reviews or links.

## Indexing boundary

Public: published/verified recipes, publicly visible blog posts and reviewed informational pages. Private: drafts, review queues, accounts, admin, content-factory, media review and operational routes. `robots.txt` and sitemap controllers enforce the same public boundary.
