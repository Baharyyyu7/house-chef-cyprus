# Blog Draft Readiness Pipeline Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the blog administration bulk workflow accurately audit and safely prepare draft posts without fabricating approvals or publishing blocked content.

**Architecture:** A read-only snapshot service evaluates unpublished posts in chunks and reports grouped blockers. An idempotent preparation service fills only deterministic metadata from existing text; the admin controller exposes bounded scan/prepare actions and the existing publication service remains the only publishing boundary.

**Tech Stack:** Laravel 13, Eloquent, Blade, PHPUnit, existing queue/cache/audit infrastructure, Hostinger immutable release workflow.

**Spec:** `docs/superpowers/specs/2026-09-23-blog-draft-readiness-pipeline-design.md`

## Global Constraints

- Never fabricate source notes, fact-check, originality, editorial or human approval.
- Never bulk-approve media or enable locales.
- Preparation must never set `status=published` or `published_at`.
- Publication must always re-run the existing readiness and similarity gates.
- No external or paid provider calls.
- Preserve unrelated dirty worktree changes.

---

### Task 1: Read-only readiness snapshot

**Files:**
- Create: `app/Services/Seo/BlogDraftReadinessSnapshotService.php`
- Create: `tests/Feature/BlogDraftReadinessPipelineTest.php`

**Interfaces:**
- Produces: `BlogDraftReadinessSnapshotService::snapshot(int $sampleLimit = 20): array{total:int,ready:int,blocked:int,reasons:array<string,int>,samples:list<array{public_id:string,title:string,issues:list<string>}>}`

- [ ] Write a failing test with one ready and one blocked draft; assert totals and grouped reasons, and assert neither record changes.
- [ ] Run `php artisan test tests/Feature/BlogDraftReadinessPipelineTest.php --filter=snapshot` and verify failure because the service does not exist.
- [ ] Implement chunked evaluation through `BlogPublicationReadinessService`, cap samples, sort reasons descending and make no writes.
- [ ] Re-run the focused test and verify it passes.
- [ ] Commit with `git commit -m "feat(blog): add draft readiness snapshot"`.

### Task 2: Deterministic draft preparation

**Files:**
- Create: `app/Services/Seo/BlogDraftPreparationService.php`
- Modify: `tests/Feature/BlogDraftReadinessPipelineTest.php`

**Interfaces:**
- Produces: `BlogDraftPreparationService::prepare(BlogPost $post): array{changed:list<string>,issues:list<string>,ready:bool}`
- Produces: `BlogDraftPreparationService::prepareBatch(int $limit = 100): array{processed:int,changed:int,ready:int,blocked:int}`

- [ ] Add failing tests proving missing meta title, description and primary keyword are derived only from existing title/excerpt, while status and all approval fields remain unchanged.
- [ ] Add a failing idempotency test proving a second run makes no additional changes.
- [ ] Run the two tests and verify failure because the preparation service does not exist.
- [ ] Implement bounded string derivation, normalization and fresh readiness evaluation; do not generate facts, sources, FAQs or approvals.
- [ ] Re-run the preparation tests and the existing publication readiness tests.
- [ ] Commit with `git commit -m "feat(blog): safely prepare deterministic draft metadata"`.

### Task 3: Admin scan and preparation actions

**Files:**
- Modify: `app/Http/Controllers/Admin/BlogPostController.php`
- Modify: `resources/views/admin/blog/index.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/BlogDraftReadinessPipelineTest.php`
- Test: `tests/Feature/SeoContentAutomationTest.php`

**Interfaces:**
- Produces routes `admin.blog.readiness-scan` and `admin.blog.prepare`.
- Consumes the snapshot and preparation service result arrays from Tasks 1-2.

- [ ] Add failing admin tests for scan/prepare authorization, visible progress summary, explicit zero-ready explanation and POST/CSRF route rendering.
- [ ] Run the tests and confirm the new routes/UI are missing.
- [ ] Add controller actions: scan redirects with bounded snapshot data; prepare processes at most 100 records per request and redirects with processed/changed/ready/blocked counts.
- [ ] Replace the misleading bulk card with separate `Taslakları denetle`, `Güvenli alanları hazırla` and `Hazır olanları yayınla` controls plus grouped blocker details.
- [ ] Re-run the new tests, `SeoContentAutomationTest` and `AdminRouteCoverageTest`.
- [ ] Commit with `git commit -m "fix(admin): make blog draft workflow actionable"`.

### Task 4: Verification and production deployment

**Files:**
- Create: `.claude/completions/2026-09-26-blog-draft-readiness-pipeline.md`

**Interfaces:**
- Consumes all prior focused tests and the guarded release script.
- Produces a deployed immutable release and verified pre-deploy backup.

- [ ] Run PHP syntax checks, `git diff --check`, all blog publication/readiness/admin authorization tests, and `npm run build`.
- [ ] Run a production read-only readiness snapshot before deployment and record total/ready/blocker counts.
- [ ] Commit the completion record without staging unrelated user files.
- [ ] Deploy with `scripts/release.sh --skip-tests`; rely on its verified backup, migration and smoke gates.
- [ ] Verify active SHA, backup health, admin page controls, queue/scheduler health and absence of browser console errors.
- [ ] Do not click or invoke publication during deployment verification.
