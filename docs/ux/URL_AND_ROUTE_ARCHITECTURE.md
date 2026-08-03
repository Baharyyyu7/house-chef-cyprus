# URL and Route Architecture

## Stable public routes

| Resource | Canonical pattern | Indexing rule |
|---|---|---|
| Recipe list | `/tarifler` | index canonical root/list page |
| Recipe detail | `/tarifler/{slug}` | index only published, verified public recipe |
| Blog list | `/blog` | index |
| Blog detail | `/blog/{slug-or-public-slug}` | index only published public post |
| Blog taxonomy | `/blog/kategori/{slug}`, `/blog/etiket/{slug}` | index only maintained taxonomy pages |
| Legal/contact | `/yasal-bilgilendirme#…` | index legal center; anchors are not individual documents |
| Search/filter | `/tarifler?...` | canonical and `noindex,follow` for thin/arbitrary combinations |

## Proposed routes, subject to Phase B approval

- `/mutfaklar/{slug}`
- `/malzemeler/{slug}`
- `/mevsim/{slug}`
- `/rehberler/{type?}`
- `/koleksiyonlar/{slug}` only for human-curated collections

Do not create routes until content ownership, canonical behavior, sitemap eligibility, and 404/redirect rules are defined.
