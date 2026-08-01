# SEO Release Checklist

- Confirm the public domain and `SEO_INDEXING_ENABLED` before permitting indexing.
- Run sitemap, robots, public recipe/blog and canonical smoke checks.
- Run `seo:audit --json` and resolve or document findings.
- Run `seo:health --json`; review counts as facts, not performance estimates.
- Validate JSON-LD against the rendered public page and keep unpublished routes noindex.
- Review media rights, public URL and alt text before a media record appears in the image sitemap.
- Require human editorial approval for topics, content, factual claims and changes to canonical metadata.
