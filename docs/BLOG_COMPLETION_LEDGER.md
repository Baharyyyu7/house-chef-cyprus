# Published Blog Completion Ledger

Generate live evidence with the read-only command below after the immutable
release is active:

```bash
php artisan blog-media:completion-audit --probe-public --json
```

The command produces exactly one row for each publicly visible blog. It reports
the existing media record, provenance, responsive variants, public URL probe,
placeholder state, duplicate indication and metadata readiness. It never
publishes, approves, attaches or overwrites content or media.

## Truthfulness gates

- A branded gradient fallback is `GRADIENT_PLACEHOLDER`, not completed media.
- A synthetic illustration must retain `generated_by_ai=true` and
  `SYNTHETIC_APPROVED` provenance; it is never represented as authentic
  photography.
- Published/editorial fields do not replace a human topic-to-image decision.
- `READY` is a record-level state only. `PUBLIC_VERIFIED` requires live page,
  image URL, schema, sitemap and browser evidence on the active release.

## Required responsive profiles

`hero`, `card`, `square`, `portrait`, `open_graph`, and `thumbnail` each require
JPEG and WebP fallbacks. AVIF is generated where the active PHP image runtime
supports it.
