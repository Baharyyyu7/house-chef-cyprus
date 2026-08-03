# SEO and Schema Benchmark

## Pattern synthesis

Recipe platforms gain durable search value from clear information hierarchy, crawlable category/collection pages, canonical detail URLs, structured data matching visible content, and internal links that help readers—not arbitrary keyword pages.

## House Chef audit

The application already exposes page, recipe, blog, and image sitemaps; canonical metadata routes; recipe/blog schema foundations; and public-only publishing rules. It must preserve the invariant that draft/review content never leaks into public lists, APIs, sitemaps, or structured data.

## Original SEO plan

- Canonicalize recipe and blog detail pages by stable public slug.
- Create curated pages only for maintained taxonomies with enough approved content.
- Use `noindex,follow` for thin, uncurated, or arbitrary filter combinations.
- Emit Recipe and BlogPosting fields only when visible and verified; omit ratings, review counts, nutrition, and author attributes when unsupported.
- Add a content-quality SEO gate for title, description, canonical, schema validity, internal links, source needs, and update context.
