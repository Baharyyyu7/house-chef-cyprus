# Production Editorial Seed Policy

`DatabaseSeeder` establishes only versioned editorial catalogue data. Its
recipe source is `database/editorial/first_50.php`; its public blog source is
`ProductionBlogCatalogueSeeder`. Test factories and QA-only accounts are not
part of production data. The local development admin is created only in the
`local` and `testing` environments.

The seed is idempotent: recipe slugs, public IDs, blog slugs, category/tag
relations, media ownership and provenance are updated in place rather than
duplicated. Public recipes must remain `published` and `verified`; public blog
posts must have editorial, originality and fact-check gates recorded.

## Media truthfulness

The current seed creates one unique House Chef Cyprus branded editorial
fallback per recipe and blog post. These are licensed original vector assets,
but are **not food photography**. Their `branded_fallback` status and
`replacement_required` metadata must not be counted as verified food imagery.
An approved/licensed image is the only path to `verified` media status.

## Prohibited seed content

Public production records must not contain `demo`, `sample`, `placeholder` or
`lorem ipsum` wording as visible content; fictional authors, ratings and
reviews are also prohibited. External recipe text, images, and source URLs are
never imported into the seed without a recorded licence and provenance review.
