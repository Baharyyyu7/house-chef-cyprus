# House Chef Cyprus Product Gap Analysis

## Scope and evidence

This is a product gap inventory based on current routes/views, existing documented platform capabilities, and public benchmark patterns. It does not assert unverified production metrics and does not authorise content publication.

| Area | Current state | Benchmark insight | Original solution | Priority | Acceptance criteria |
|---|---|---|---|---|---|
| Public discovery | Search, cuisine, category, time, dietary/nutrition, allergens and sort exist; UI is route-centric | Intent must be discoverable without an expert query | Unified intent/search UX, chips, mobile filter drawer, curated entry pages | P1 | Keyboard/mobile flow; shared filter contract; noindex thin combinations |
| Recipe cards | Reusable V3 card exists; real-media check exists | Cards work when facts are concise and honest | One card contract with approved-media/text-only state and explicit dimensions | P0 | No fake media, score, or rating; accessible action targets |
| Recipe detail | Strong measurement, safety and provenance base | Working cooking information needs prioritisation | Section-visibility rules, print, reviewed facts, related-content logic | P1 | No empty/fabricated sections; schema matches visible fields |
| Blog | Search/cards/detail/related content exist | Guides require a clear editorial purpose and trust context | Type taxonomy, source/review block, ToC, intentional text-only layout | P1 | Three meaningful headings; source needs; no unsupported health claim |
| Media | Candidate/review operations exist; public real-media guard exists | Media must preserve provenance and responsive performance | Enforce media lifecycle and responsive derivative contract | P0 | Candidate cannot appear public; attribution/alt/review data present |
| Quality | Data Quality and Fact Check infrastructure exists | Publication cannot bypass quality | Single publish-readiness summary for recipe/blog | P0 | Auditable blocker list; human approval never replaced |
| Retention | Favourites, pantry, cooking, plans/list foundations exist | Utility drives return visits | Collections/saved filters/recent views with privacy controls | P1 | User export/reset/delete and retention documented/tested |
| Community | No safe public launch needed | Community needs moderation before scale | Keep disabled until full moderation architecture | P3 | No public enablement without policy/report/rate-limit/review |
| SEO | Canonicals, sitemaps, metadata foundations exist | Schema must reflect visible truth | Audit-first schema/content gate and curated route map | P0 | Zero draft leakage; schema validation and canonical tests |
| Analytics | Privacy-safe foundation exists | Aggregate signals should explain value, not surveil | Consent-aware minimal events and admin aggregate view | P2 | No raw personal data in event payloads |
| Operations | Queue, scheduler, health, deployment workflow exist | Reliability is product quality | Continue MySQL backup closure and publish-safe runbooks | P0 | Restore tested on non-production target; health/publish gates pass |

## Risk register

| Risk | Mitigation |
|---|---|
| Publishing incomplete or unverified content | Strict workflow, Human approval, public query scopes, leakage tests |
| Mistaking placeholders for food media | Explicit media state and UI treatment |
| Health/allergen overclaim | Source gate, uncertainty language, editorial reviewer and disclaimer |
| SEO index bloat | Canonical/noindex policy plus curated landing-page ownership |
| Personal data over-collection | Consent, minimization, retention, export/delete controls |
