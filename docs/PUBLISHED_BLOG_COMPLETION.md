# Published Blog Completion

Last audit: 2026-08-03. Rule: `published-blog-completion-v1`.

The twenty public catalogue articles currently need expansion and article-specific approved media. The public views deliberately show an honest no-photo state instead of presenting branded SVG fallbacks as approved photography.

Run the read-only audit:

```bash
php artisan content:published-blog-completion --json
```

Prepare or refresh private review revisions:

```bash
php artisan content:published-blog-completion --prepare --json
```

Preparation is idempotent. It creates one private `ContentRevision` per published article, preserving the public body. Every review pack includes proposed structured copy, SEO metadata, related published recipe public IDs, source requirements and a topic-specific media brief. It never applies, approves, schedules or publishes a proposal.

The following topics require authoritative-source review before an editor may apply a revision:

- allergen labelling;
- nutrition estimates;
- food storage and refrigeration;
- leftovers and reheating;
- oven temperature and doneness.

Initial authoritative references are restricted to official sources including the UK Food Standards Agency, EFSA, FoodSafety.gov and USDA FSIS. A URL in a review pack is evidence for a human reviewer to inspect; it is not an automatic fact-check approval.

The admin blog list exposes a “Karşılaştır” link after preparation. The comparison page is admin-only and `noindex,nofollow`. It shows current public copy beside the private proposal, source requirements and the media brief. It intentionally provides no automation-owned approval action.

## Publication gate

An article remains incomplete until a real authorized editor records content approval, high-risk claims are fact-checked where required, and a topic-specific image has passed license, attribution, fidelity and media approval checks. Automation cannot approve its own revision or media.
