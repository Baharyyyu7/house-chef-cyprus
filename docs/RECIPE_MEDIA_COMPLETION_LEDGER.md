# Published Recipe Media Completion Ledger

This ledger is a production-verification checklist, not proof that a recipe
image is live. Generate the current, per-recipe evidence with:

```bash
php artisan recipe-media:completion-audit --probe-public --json
```

The audit emits exactly one row for every `published` and `verified` recipe.
It records the active media state, source provenance, responsive-variant state,
public URL result (when probing is requested), duplicate indication, and a
separate identity-confirmation state.

## Non-negotiable publication gate

`published` and `verified` recipe content does **not** constitute visual
identity confirmation. Until an editor records an identity decision, a recipe
is `IDENTITY_CONFIRMATION_REQUIRED` for image generation. The audit deliberately
sets `generation_eligible` to `false`; it never generates, attaches, approves,
or publishes media.

## Deterministic media states

| State | Meaning | Safe next action |
| --- | --- | --- |
| `READY` | Approved media and required responsive variants are recorded. | Verify live URLs, rendering, schema and image sitemap before public acceptance. |
| `NO_MEDIA_RECORD` | No media is associated. | Record a human identity decision, then prepare a private brief. |
| `PLACEHOLDER_ONLY` | Only a placeholder or branded fallback exists. | Preserve it as a truthful fallback; do not represent it as dish photography. |
| `FILE_MISSING` | A media record exists but its local source is absent. | Repair the deterministic storage/path issue before sourcing a replacement. |
| `STORAGE_PATH_BROKEN` | Approved media cannot yield a public URL. | Repair disk/path/symlink or URL configuration. |
| `VARIANTS_MISSING` | An approved source lacks a required responsive representation. | Regenerate variants from the existing approved source. |
| `DUPLICATE_MEDIA` | A source checksum/path is reused in this published scope. | Require an editor to confirm or replace the association. |
| `PUBLIC_URL_BROKEN` | A requested safe HTTP probe failed. | Repair URL/storage/deployment before new generation. |
| `UNAPPROVED_MEDIA` | A source exists but lacks the complete approval chain. | Keep private or non-prominent until reviewed. |
| `WRONG_DISH_MEDIA` | Fidelity state is not a strong match. | Reject or supersede only through media review. |

## Production acceptance

Mark a recipe `PUBLIC_VERIFIED` only after its public card, recipe hero, image
variants, Open Graph, Recipe schema and image-sitemap URL have been checked on
the active production release. No local record, artifact, or candidate alone is
evidence of public completion.
