# Internal Linking Architecture

## Link relationships

- Recipe → same cuisine, compatible meal moment, shared primary ingredient, and supporting guide.
- Guide → relevant recipes only when editorially reviewed and genuinely supportive.
- Ingredient landing page → recipes using the ingredient plus a distinct technique/handling guide.
- Cuisine/category/season page → curated cards with one editorial introduction and clear pagination.

## Controls

- No automatically generated anchor-text farms.
- No linking to drafts, review queues, admin pages, or unapproved media.
- Links need a relationship signal stored or deterministically explainable (cuisine, ingredient, category, editorial mapping).
- Every new public taxonomy page needs its own internal-link source and exit paths before indexing.
