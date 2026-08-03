# Search and Discovery Benchmark

## Observed industry patterns

- Search boxes accept recipe and ingredient intent, then hand off to scoped results.
- Strong sites combine browse taxonomies (cuisine, meal type, season, occasion) with filterable results.
- Ingredient-led suggestions reduce the "what should I make?" blank state.
- Mobile discovery works when filters are a focused drawer with persistent applied-filter feedback.

## Original House Chef proposal

1. Keep `/tarifler` as the canonical results page; do not create indexable URLs for every filter combination.
2. Add a single intent parser that resolves Turkish normalization, aliases, recipe title, and ingredient terms before applying the existing shared filter contract.
3. Organize browse pages around curated **cuisines, meal moments, methods, ingredients, and seasons**; each landing page needs editorial ownership and a canonical URL.
4. Add accessible applied-filter chips, clear-all, result count, and a mobile drawer that preserves state.
5. Only expose nutrition-range filters when the underlying recipe data has the required confidence. Allergen exclusion keeps the current exact-`contains` contract.

## Rejected patterns

- Search-result pages with empty keyword stuffing.
- Unbounded URL combinations in sitemaps.
- Claiming typo tolerance or synonym coverage before measurable tests exist.
- Popular/trending ordering without real, privacy-safe aggregate events.
