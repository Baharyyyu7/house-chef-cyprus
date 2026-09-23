# Full Site Functionality Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Inventory all 423 routes, repair shared causes first, then verify every public, authenticated, administrative, API and machine-readable workflow through tested production deployments.

**Architecture:** A reusable route and browser audit harness creates the coverage ledger. Repairs proceed in seven independently deployable batches: shared shell, public discovery, authenticated tools, commercial/contact, administration, APIs/machine surfaces, and final accessibility/performance verification. Each batch turns its ledger rows from failing to verified and adds regression tests before deployment.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Livewire, PHPUnit, Playwright CLI, Vite, MySQL, Hostinger release workflow.

**Spec:** `docs/superpowers/specs/2026-09-23-full-site-functionality-design.md`

## Global Constraints

- Preserve unrelated user changes in the primary checkout.
- Do not enable disabled locales or publish content.
- Do not bulk-approve media or bypass human approval gates.
- Do not make paid provider calls.
- Back up production before material data changes.
- Use TDD and deploy only after affected tests pass.
- Every page must be judged by its primary task, not HTTP 200 alone.

---

### Task 1: Route Coverage Ledger and Audit Harness

**Files:**
- Create: `app/Support/RouteAudit/RouteSurfaceClassifier.php`
- Create: `app/Console/Commands/RouteFunctionalityAudit.php`
- Create: `tests/Feature/RouteFunctionalityAuditTest.php`
- Create: `tests/Unit/RouteSurfaceClassifierTest.php`
- Create at runtime: `storage/app/reports/route-functionality.json`

**Interfaces:**
- Produces: `RouteSurfaceClassifier::classify(Route $route): string`
- Produces: `route:functionality-audit {--json} {--output=} {--include-admin}`
- Produces ledger rows containing `method`, `uri`, `name`, `surface`, `access`, `expected_status`, `observed_status`, `primary_action`, `result`, and `issues`.

- [ ] Write tests proving representative routes classify as `public`, `authenticated`, `admin`, `api`, or `machine` and that dynamic/destructive routes are never invoked by the audit.
- [ ] Run `php artisan test tests/Unit/RouteSurfaceClassifierTest.php tests/Feature/RouteFunctionalityAuditTest.php` and verify failure because the classifier and command do not exist.
- [ ] Implement the classifier using route prefixes, middleware and HTTP methods; implement safe GET/HEAD probes with representative bindings from existing records and explicit skips when no safe binding exists.
- [ ] Run the focused tests and verify they pass.
- [ ] Run `php artisan route:functionality-audit --json --include-admin --output=storage/app/reports/route-functionality.json` and verify all 423 routes have ledger rows.
- [ ] Commit with `git commit -m "feat(quality): add full route functionality audit"`.

### Task 2: Shared Shell and Locale-Safe Navigation

**Files:**
- Modify: `resources/views/layouts/partials/public-header.blade.php`
- Modify: `resources/views/layouts/partials/public-footer.blade.php`
- Modify: `resources/views/home/index.blade.php`
- Modify: `app/Services/Seo/LocaleUrlService.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/PublicNavigationIntegrityTest.php`
- Test: `tests/Feature/MobileHeaderFitsTheScreenTest.php`
- Test: `tests/Feature/ThemeSwitchTest.php`
- Test: `tests/Feature/AlpineExtensionsTest.php`

**Interfaces:**
- Consumes: configured enabled locales from `config/locales.php`.
- Produces: locale-safe public URLs and shared navigation with no disabled locale prefixes.

- [ ] Add failing tests that crawl shared header/footer/home links and reject `/de/`, `/pl/`, `/fa/` or another disabled locale prefix.
- [ ] Add browser assertions for menu open/close, theme persistence, skip link, focus return and 320px horizontal overflow.
- [ ] Run the focused tests and capture the expected failures, including the known `/de/sure/*` and `/de/zorluk/*` links.
- [ ] Replace locale-dependent hardcoded links with named routes or `LocaleUrlService`; repair shared Alpine behavior without changing unrelated page content.
- [ ] Run focused PHPUnit tests, `npm run build`, and Playwright checks at 320, 375 and 390 pixels.
- [ ] Commit with `git commit -m "fix(ui): make shared navigation locale-safe and usable"`.
- [ ] Deploy through `scripts/deploy-hostinger-release.sh`, then verify `/`, `/tarifler`, `/blog`, menu, theme and all shared links in production.

### Task 3: Public Discovery Journeys

**Files:**
- Modify as failures require: `app/Http/Controllers/RecipeController.php`, `BlogController.php`, `IngredientExplorerController.php`, hub controllers and their Blade views.
- Create: `tests/Feature/PublicDiscoveryJourneyTest.php`
- Create: `tests/Browser/public-discovery.md` as a Playwright CLI scenario manifest.

**Interfaces:**
- Consumes: ledger rows with `surface=public`.
- Produces: verified home -> search/filter -> detail -> related-content journeys.

- [ ] Add failing feature tests for search, filter persistence, pagination, empty states, unavailable records, canonical links and related content.
- [ ] Run tests and record each failure in the ledger.
- [ ] Repair one controller/view pair at a time, keeping existing query parameters stable and giving every empty result a useful next action.
- [ ] Run tests after each pair; crawl all discoverable public links and capture console/network failures.
- [ ] Verify representative Turkish and fully translated English routes; confirm untranslated locale content returns the intended result.
- [ ] Commit public repairs in coherent controller/view groups and deploy after the entire public batch is green.

### Task 4: Authenticated Cooking and Account Journeys

**Files:**
- Modify as failures require: auth, profile, favorites, pantry, collection, personalization, AI kitchen and cooking-session controllers/views.
- Create: `tests/Feature/AuthenticatedJourneyTest.php`
- Create: `tests/Feature/CookingSessionJourneyTest.php`

**Interfaces:**
- Consumes: seeded user, verified recipe and empty-account fixtures.
- Produces: tested account lifecycle and persistent cooking-tool outcomes.

- [ ] Add failing tests for guest redirects, registration/login, profile update, favorites, pantry, collections, personalization onboarding, cooking timers and AI-provider unavailable behavior.
- [ ] Verify validation failures preserve safe input and success responses show confirmed outcomes.
- [ ] Implement minimal fixes per journey; do not fake provider output when a provider is unavailable.
- [ ] Run focused tests and browser scenarios for empty, success, failure and return-visit states.
- [ ] Commit by user journey and deploy after authorization and persistence checks pass.

### Task 5: Commercial, Contact and Service Journeys

**Files:**
- Modify as failures require: private-chef, catering, classes, meal-plan, shop, subscription, quote and contact controllers/views.
- Create: `tests/Feature/CommercialJourneyTest.php`
- Create: `tests/Feature/ContactJourneyTest.php`

**Interfaces:**
- Produces: functional zero-cost inquiry fallback and explicit provider-unavailable states.

- [ ] Add failing tests for package selection, quote calculation, contact submission, duplicate submission protection, unavailable checkout and successful configured-provider redirects.
- [ ] Repair calls to action so every visible control reaches a real form, result or explicit unavailable state.
- [ ] Verify email is not sent in tests and no paid provider is invoked without configuration.
- [ ] Run focused tests and mobile browser scenarios, then commit and deploy the batch.

### Task 6: Administration Task Groups

**Files:**
- Modify as ledger failures require: `app/Http/Controllers/Admin/*`, `resources/views/admin/**/*`, and shared admin components.
- Create: `tests/Feature/Admin/AdminRouteCoverageTest.php`
- Create: `tests/Feature/Admin/AdminMutationSafetyTest.php`

**Interfaces:**
- Consumes: 202 administration ledger rows grouped by operator task.
- Produces: verified list/detail/filter/mutation flows with explicit authorization and confirmation.

- [ ] Add an admin coverage test requiring every GET route to return 200, intentional redirect, 403 or 404 for both administrator and non-administrator fixtures.
- [ ] Add mutation safety tests for validation, target identity, confirmation, authorization and post-action status.
- [ ] Repair shared admin navigation, tables, filters, pagination, empty states and flash messages before page-specific defects.
- [ ] Repair task groups in order: content, editorial, media, data quality, SEO, users/contact, system/backups.
- [ ] Preserve publication, media identity and backup safety gates in every change.
- [ ] Run the full admin suite, commit per task group and deploy each independently verified group.

### Task 7: API and Machine-Readable Contracts

**Files:**
- Modify as failures require: `app/Http/Controllers/Api/V1/*`, health, sitemap, feed, manifest, OpenSearch, agent-tool and webhook code.
- Create: `tests/Feature/ApiContractCoverageTest.php`
- Create: `tests/Feature/MachineReadableSurfaceTest.php`

**Interfaces:**
- Produces: stable JSON/XML/text contracts with correct validation, authorization and status codes.

- [ ] Add contract tests for every API route and machine-readable endpoint, including malformed input and unauthorized access.
- [ ] Verify sitemap/feed resources exclude draft, scheduled, redirected and noindex content.
- [ ] Repair response contracts and machine-readable output without inventing provider or content data.
- [ ] Run contract tests and validate production resources with real HTTP clients.
- [ ] Commit and deploy the batch.

### Task 8: Final Accessibility, Performance and Production Verification

**Files:**
- Modify only files implicated by verified final failures.
- Update: `storage/app/reports/route-functionality.json` at runtime.
- Create: `.claude/completions/2026-09-23-full-site-functionality.md`

**Interfaces:**
- Consumes: all prior batch tests and the route ledger.
- Produces: final verified ledger with no unresolved P0/P1 defects and documented external blockers.

- [ ] Run the full PHPUnit suite, production build, route audit and browser journeys.
- [ ] Test representative pages at 320, 375, 390, 768 and 1440 CSS pixels with keyboard-only navigation.
- [ ] Check console errors, failed assets, canonical metadata, sitemap membership and server response times.
- [ ] Create and verify a production backup, deploy the final batch and run post-deploy smoke tests.
- [ ] Re-run the 423-route ledger against production and confirm every row has an expected result.
- [ ] Record external-credential limitations without presenting unavailable controls as functional.
- [ ] Write the completion document, remove disposable artifacts, check disk growth and produce the final user report.
