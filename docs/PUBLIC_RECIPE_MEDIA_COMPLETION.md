# Public Recipe Media Completion

## Production rule

Every public recipe may display only dish-specific media that has passed fidelity, licence, attribution and human approval gates. Placeholder, branded fallback, unrelated, unlicensed and unreviewed candidate records are never presented as completed food photography.

## Audit

Run the read-only inventory:

```bash
php artisan recipe-media:completion-audit --json
```

Use `--probe-public` only in an authorized environment when every eligible public URL should receive a bounded HTTP HEAD check. The output contains a row for every published and verified recipe, including media public ID, path, disk, file existence, variants, URL, HTTP status, MIME type, dimensions, alt text, duplicate status and one explicit failure classification.

Current catalogue finding: all 50 published recipes have branded placeholder records and zero approved real media. Thirty recipes require explicit dish identity confirmation; twenty additionally have a title–ingredient mismatch. Therefore candidate search and approval remain blocked rather than risking a wrong-dish image.

## Private review preparation

The idempotent command below creates private identity, content, media review and media brief records without changing a recipe or public media:

```bash
php artisan content:published-recipe-review-batch --all --scope=published --write --json
```

After a real editor confirms dish identity, the configured Pexels adapter may create no more than three private metadata candidates per recipe:

```bash
php artisan media:pexels-scan --batch=1 --limit=3 --contact-sheets --json
```

Scanning is not approval. Candidate selection, private import, fidelity review, licence review, attribution review and final approval remain separate authorized actions.

## Variants

Approved local recipe media is cropped from its recorded focal point into deterministic profiles:

- thumbnail: 400×300;
- square: 640×640;
- card: 800×600;
- Open Graph: 1200×630;
- hero: 1600×1000.

JPEG is mandatory; WebP and AVIF are produced when supported by the PHP image runtime. Public recipe cards and detail heroes use `<picture>`, modern-format sources, JPEG fallback, explicit dimensions and responsive source sets.

## Production apply manifest

Generate a manifest only after approved media and all variants exist:

```bash
php artisan recipe-media:completion-audit \
  --manifest=storage/app/private/media-apply/recipe-media.json \
  --json
```

Each eligible entry records recipe and media public IDs, expected prior media, reviewer/timestamp, checksum, variant state, alt text and expected recipe version. Production apply must reject stale recipe or prior-media state. It must never restore a database automatically.

## Current blocker

The technical pipeline is ready, but public completion is not. A real editor must confirm 50 dish identities, resolve 20 title–ingredient mismatches, choose matching licensed candidates and record independent human approval. Until then the intentional branded no-photo state remains the only truthful public presentation.
