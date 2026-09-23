# Safe Bulk Blog Publication

- Fixed single-post publication failures returning a raw HTTP 422 page.
- Added a separate "Uygun olanların tümünü yayınla" section to the blog administration page.
- Bulk publication applies the existing quality, SEO, human approval and similarity gates to every unpublished post.
- Posts that fail any gate remain unpublished; the result reports published/skipped counts and grouped reasons.
- Added regression coverage for the single and bulk flows, authorization and page rendering.
