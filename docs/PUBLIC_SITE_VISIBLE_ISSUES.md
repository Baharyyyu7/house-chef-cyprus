# Public Site Visible Issues Audit

Audit date: 2026-08-02. Environment: local public site at `http://127.0.0.1:8010`.

## Confirmed baseline

- The public home page rendered successfully with published recipe cards, search, navigation, footer and responsive theme control.
- Recipe cards use approved public media when available and deterministic recipe-specific branded artwork otherwise; no private media is selected by public recipe components.
- The initial home page had only a small featured subset and no database-backed editorial section, which left the product feeling too sparse despite the verified catalogue.
- Blog cards could render an empty media band when no approved hero medium existed.

## Fixes started in this sprint

- Home now receives verified-published catalogue selections for quick recipes, Cyprus/Mediterranean recipes, breakfast, main dishes and desserts. A section is suppressed when its real query has no results.
- Home now renders the latest published blog posts from the database and never creates hard-coded posts.
- Blog cards use a labelled branded editorial fallback when an approved public hero image is unavailable.
- Public media remains private-by-default: only approved public media is rendered as a real image.

## Remaining human-gated work

- Selecting or approving external media candidates requires human editorial confirmation and licence/attribution verification.
- Recipe fields with absent verified information must remain absent in public presentation; they are not fabricated for layout completeness.
