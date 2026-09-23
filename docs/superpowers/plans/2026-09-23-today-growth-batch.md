# Today Growth Batch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the highest-value zero-cost growth work for 23 September 2026 without bulk publishing or unsolicited email.

**Architecture:** Use current Search Console measurements to select three Turkish striking-distance pages, create source-backed review revisions, inspect Hostinger Mail for inbound opportunities, and refresh the existing backlink candidate queue in read-only/draft mode. Every external-facing change remains either an editorial revision or an unsent message draft.

**Tech Stack:** Laravel Artisan, production MySQL, Hostinger Mail API, Search Console measurements, JSON editorial delivery.

**Spec:** `docs/superpowers/specs/2026-09-23-autonomous-zero-cost-growth-design.md`

## Global Constraints

- Added tool cost is 0 TL.
- No bulk publication.
- No unsolicited email is sent.
- Only Turkish work runs today; other locale lines remain closed until indexable locale URLs exist.
- Health and food-safety claims require official sources.
- Existing production content is changed only through a review revision.

---

### Task 1: Complete three Turkish striking-distance revisions

**Files:**
- Production delivery: `shared/storage/app/content-import/blog/{slug}.json`
- Production records: `content_revisions`

**Interfaces:**
- Consumes: `SearchConsoleMeasurementService::snapshot()` striking-distance rows.
- Produces: three unreviewed `ContentRevision` records with source-backed bodies and SEO metadata.

- [ ] Select the first three queries with impressions, zero/low clicks and positions 4-20.
- [ ] Map each query to one existing public canonical page; reject duplicate targets.
- [ ] For each target, prepare 900+ meaningful Turkish words, 3-6 FAQs, 3-6 secondary keywords, valid metadata, official sources where claims require them, and relevant recipe links.
- [ ] Upload each JSON file to the production editorial delivery directory.
- [ ] Run `php artisan blog:apply-editorial-content --slug=<slug> --json` and verify one review revision would be created.
- [ ] Run the same command with `--apply`; verify live body and status remain unchanged while an unreviewed revision exists.
- [ ] Record proposed word count, meta lengths and source URLs.

### Task 2: Inspect Hostinger Mail and prepare safe work

**Files:**
- No repository or production content files.

**Interfaces:**
- Consumes: Hostinger Mail current account, folders and recent inbound messages.
- Produces: a classified list of genuine collaboration, press, backlink and customer requests; drafts are permitted, sending is not.

- [ ] Discover the connected mailbox with `GET /api/v1/me`.
- [ ] List folders and inspect recent unread inbox messages.
- [ ] Classify each relevant message by language, intent, sender legitimacy and required action.
- [ ] Prepare an unsent reply draft only for genuine inbound collaboration or backlink messages.
- [ ] Do not send, delete, move or mark messages without a separate explicit message-level decision.

### Task 3: Refresh the zero-cost backlink queue

**Files:**
- Production application records managed by existing SEO commands.

**Interfaces:**
- Consumes: public pages that pass quality checks and existing backlink prospect records.
- Produces: deduplicated research candidates and unsent outreach drafts.

- [ ] Run the existing backlink verification command in read-only mode.
- [ ] Run the outreach queue in report/draft mode only.
- [ ] Exclude draft, redirected, 404, health-risk and failing-quality targets.
- [ ] Deduplicate by root domain and preserve the 14-day follow-up interval.
- [ ] Report verified links separately from candidates; never count a candidate as a backlink.

### Task 4: Verify today’s batch

**Files:**
- No new files.

**Interfaces:**
- Consumes: outputs from Tasks 1-3.
- Produces: a concise completion report with evidence and blockers.

- [ ] Confirm no blog status or `published_at` value changed.
- [ ] Confirm exactly three review revisions exist for today’s selected pages.
- [ ] Confirm no outbound email was sent.
- [ ] Confirm no paid provider was called.
- [ ] Confirm `/`, `/blog`, `/tarifler` and `/health` return HTTP 200.
