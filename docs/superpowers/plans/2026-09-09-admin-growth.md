# Admin and Organic Growth Implementation Plan

> **For agentic workers:** Use subagent-driven-development for the bounded SEO implementation and review; the coordinating agent implements the shared admin UI and deployment. Steps use checkbox syntax for tracking.

**Goal:** Deliver a consistent entire admin area, persistent interactive world map, and evidence-based automated SEO/backlink operations to production.
**Architecture:** Extend the existing Laravel Blade admin shell and existing SEO/IntegrityReport workflows. Keep queues paginated and bounded. Real task evidence, candidates and observed backlinks remain distinct.
**Tech Stack:** Laravel 13, PHP 8.4, Blade, Alpine, Tailwind, Chart.js, existing country SVG.
**Spec:** User request of 2026-09-09 including the four screenshots: all admin headings/pages improved, world map preserved, autonomous growth scaled to thousands of manageable tasks, plan then implement then deploy.

## Global Constraints
- Preserve role checks, CSRF, public identifiers, drafts, media review and disabled locales.
- No fabricated SEO traffic, backlinks, authority metrics, contacts or content.
- Do not automatically send outreach, publish articles, approve media or create purchased/spam links.
- No new paid provider calls or credentials. Search Console authorization is an external dependency and must stay visible.
- Existing unrelated AI and deployment working-tree changes are not part of this release.
- Deploy is explicitly authorized by the user. Build a clean commit, verify checksums and retain a rollback release.
- Admin headings use fixed font sizes; no negative letter spacing or card-heavy page composition.
- Share no local or production private credentials in outputs.

### Task 1: Admin Shell, Media and World Map
**Files:** resources/css/admin-dashboard.css; resources/views/components/admin-sidebar.blade.php; resources/views/components/admin/page-header.blade.php; resources/views/components/admin/world-activity-map.blade.php; resources/views/components/layouts/site.blade.php; resources/views/admin/media/index.blade.php; focused admin/map feature tests.
**Interfaces:** Existing map array and admin route names remain compatible; the CSS covers all admin descendants.
- [ ] Use a 26px desktop / 22px mobile H1, compact section headings, flat sections, bounded table overflow and 8px controls/cards across all admin pages.
- [ ] Keep navigation groups and search usable when collapsed, on mobile and with keyboard; replace logout cross with a recognizable exit icon.
- [ ] Keep world-map-countries visible with empty data and an explicit unavailable measurement state. Correct tooltip unit for visitors vs members, position keyboard tooltip within the map, remove clipped tooltips at narrow widths.
- [ ] In media UI make missing images and missing titles explicit, preserve per-image review controls. Never show generated placeholder art as the actual preview.
- [ ] Verify admin access, map empty/populated states, shell navigation, representative list/form pages and 320/390/1470px viewport overflow.

### Task 2: SEO and Backlink Work Queue
**Files:** app/Models/SeoBacklinkCandidate.php; migration for seo_backlink_candidates; app/Services/Seo/OrganicGrowthService.php; app/Console/Commands/SeoGrowthRefresh.php; app/Http/Controllers/Admin/SeoOperationsController.php; resources/views/admin/seo/{index,growth,opportunities,issues}.blade.php as needed; routes/web.php; routes/console.php; tests/Feature/OrganicGrowthOperationsTest.php.
**Interfaces:** OrganicGrowthService::refresh(int $limit = 1000): array produces bounded local work and an IntegrityReport under guard seo:growth-refresh; snapshot(): array supplies counts and latest run for the SEO UI.
- [ ] Extend existing SEO center with compact source-backed metrics, useful action links, paginated technical/content queue, latest automation run and provider dependency states.
- [ ] Add a backlink candidate register with source_url, source_host, target_url, status (candidate/reviewing/contacted/rejected/observed), evidence/provenance, reviewed_at and unique deduplication hash. Only observed links backed by a source URL may be counted as observed, not independently verified.
- [ ] Derive relevant backlink research candidates from real published recipe source/provenance URLs. They are research candidates, never asserted backlinks. Bound scanning, deduplicate, and preserve manual decisions. Reject private/unsafe URLs and same-site candidates; do not fetch untrusted URLs.
- [ ] Accept a validated admin CSV upload up to 10,000 rows for externally researched candidates, with source_url and target_url required, bounded file size, transaction-safe validation and no false verified status from imports. All URLs must be public HTTP(S); target must match this app's own origin. Paginate and allow status filtering and explicit review transitions.
- [ ] Derive up to 1,000 prioritized actionable review items from existing open SEO issues and relevant content gaps without fake count padding, duplicates or overwriting reviewed decisions. Reuse ContentOpportunity and store source/issue evidence; do not publish.
- [ ] Add a daily bounded seo:growth-refresh job at an unused schedule time with withoutOverlapping; write success/failure history and counts. Add a manual admin refresh with role/CSRF checks.
- [ ] Test: anonymous/member access; unsafe and oversized import rejection; duplicate imports; review preservation; repeat refresh idempotence; limit bounds; source/target mapping; accurate candidate vs observed counts; scheduler registration; no external HTTP/publishing.
- [ ] Run related SEO suites, Pint, report exact changes and evidence. No edits to shared CSS/sidebar/map or deployment scripts.

### Task 3: Integration and Production Delivery
**Files:** deployment receipt and completion doc; deployment script only if a blocking defect is demonstrated.
**Interfaces:** Tasks 1 and 2 combined in codex/admin-growth-20260909, clean source artifact.
- [ ] Review the integrated diff and fix blocking issues; run focused combined admin/SEO tests and npm build.
- [ ] Preview real local data and inspect representative sidebar destinations, forms, SEO workflows and map. Check public homepage for style regressions.
- [ ] Check active release and only task-related changed paths, create new immutable commit, build a clean release artifact with no runtime state/AppleDouble/secrets.
- [ ] Upload checksum-verified artifact, back up production, run forward migrations, switch release using documented Hostinger physical webroot procedure.
- [ ] Run production growth refresh once, verify scheduled entry and actual persisted report; confirm authenticated dashboard/SEO/media and public smoke routes with real deployed CSS/JS.
- [ ] Report actual generated task and candidate counts, deployment commit and remaining external authorization limitations. Remove only task-created build intermediates after verification.

## Decisions and Progress
- Plan recorded before new implementation; earlier dashboard changes transferred to an isolated worktree.
- User already authorized execution and deployment; no second approval gate.
- Thousands is a supported queue/import capacity; actual evidence determines record count. External placements are not controllable by this code.
- Deployment exception: the documented 2026-08-27 PHP-FPM realpath-cache defect still exists in the base deployer. Carry the existing narrowly scoped immutable bootstrap fix and tests into this release. Media imports become explicit opt-in so this UI deployment cannot change reviewed media.
