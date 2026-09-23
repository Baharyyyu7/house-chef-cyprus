# Blog Draft Readiness Pipeline Design

## Goal

Turn the 1,277 unpublished blog records into an operable preparation queue without bypassing publication, factual accuracy, originality, media identity, or human approval gates. Remove the misleading behavior where the bulk publication action finishes with zero publications but looks like a system failure.

## Observed production state

- 1,277 unpublished records were scanned in 1.35 seconds from the production database.
- Zero records currently pass the complete publication readiness gate before similarity inspection.
- The dominant blockers are missing content purpose, human approval, minimum useful depth, search intent, source notes, FAQ blocks, originality state, verified recipe links, fact-check state and editorial approval.
- There is no Laravel exception for the bulk route. The current result is a valid zero-published outcome presented with insufficient guidance.

## User experience

The blog administration page will separate preparation from publication:

1. `Taslakları denetle` performs a read-only readiness scan and stores a bounded summary.
2. `Güvenli alanları hazırla` dispatches idempotent preparation jobs in small batches.
3. The page shows queued, processing, ready, blocked and failed counts plus grouped blocking reasons.
4. `Hazır olanları yayınla` remains a separate, explicit action and only processes records that pass every gate.
5. A blocked record links to its editor and displays the fields requiring human work.

No action will label all drafts approved, fabricate sources, invent fact-check evidence, infer originality approval, bulk-approve media, or publish records that fail readiness.

## Preparation rules

Safe deterministic repairs may be applied only when the underlying content already supports them:

- Normalize slug from the title when the slug is missing.
- Derive a length-bounded meta title from the existing title.
- Derive a length-bounded meta description from the existing excerpt/body without adding claims.
- Infer a primary keyword from an existing explicit title/topic phrase.
- Normalize existing secondary keyword values and remove duplicates.
- Repair Markdown heading levels when usable headings already exist.
- Link an already verified published recipe only when an existing explicit relation or exact canonical reference proves the relationship.

The pipeline must not generate or mark as complete:

- source/fact-check notes;
- FAQ answers containing new factual claims;
- fact-check, originality or editorial approval states;
- human approval timestamps or users;
- media approval or identity matches.

## Architecture

### Readiness snapshot

`BlogDraftReadinessSnapshotService` evaluates all unpublished posts in chunks and returns totals, grouped blockers and a per-post readiness state. The scan is read-only and can run synchronously because the production measurement is bounded, but its output is capped before being stored in the session or audit log.

### Preparation batch

`PrepareBlogDraftBatch` creates a batch identifier and dispatches `PrepareBlogDraft` jobs in groups. Each job locks one post, applies only deterministic repairs, re-evaluates readiness and records a safe result. Jobs are idempotent and never set `status=published`.

Existing queue and scheduler infrastructure will be reused. No external or paid provider is required.

### Publication batch

The existing `BlogPublicationService` remains the only publication boundary. Bulk publication will query only records marked ready by a fresh evaluation, process them in bounded chunks and report published/skipped counts. A stale readiness snapshot can never override a fresh gate result.

### Persistence

Batch progress will use the existing cache/audit infrastructure where possible. If durable per-post progress is required after implementation discovery, one small migration may add a JSON metadata field or dedicated batch/result tables. No content columns will be repurposed for operational state.

## Error handling

- Starting a duplicate active preparation batch returns the existing batch status.
- A failed item is recorded without aborting unrelated items.
- Queue/provider unavailability is shown as an explicit blocked state.
- Publication failures return to the administration page with actionable reasons.
- The UI never reports a zero-published result as success without also showing why.

## Tests

- Readiness scan is read-only and reports grouped blockers.
- Preparation changes only deterministic fields and never publishes.
- Re-running preparation is idempotent.
- Human/fact-check/originality/media approvals are never fabricated.
- Queue batches are bounded and duplicate-safe.
- Bulk publication rechecks readiness and publishes only passing records.
- Members cannot start scans, preparation or publication.
- The administration page renders progress, blockers and next actions.

## Deployment

Run focused tests and the production build, create a verified production backup, deploy through the immutable release workflow, and verify the UI plus queue/scheduler health. Do not trigger publication during deployment verification.
