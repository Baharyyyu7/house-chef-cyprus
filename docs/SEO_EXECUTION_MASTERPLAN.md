# SEO Master Execution Plan - housechefcyprus.com

**Status**: ACTIVE DEPLOYMENT
**Authority**: Full autonomy - execute, deploy, iterate until #1
**Target**: #1 ranking across all target keywords in TR, EN, and all supported locales
**Start Date**: 2026-09-14
**Never Stop**: Until target reached

---

## Architecture Overview

The site already has a complete SEO infrastructure:
- **AutonomousGrowthEngine**: Daily SEO/GEO/backlink cycle (100/100/100 targets)
- **GrowthSystem**: 7-stage orchestration (measurement → SEO → opportunities → backlinks → languages → geo → indexnow)
- **SeoSchemaService**: Full Recipe/Article/FAQ/BreadcrumbList schema with Heritage Entities
- **SeoAuditService**: Fingerprinted, idempotent audit with severity scoring
- **BacklinkGrowthService**: 100+ prospects across universities, tourism boards, food sites
- **SearchConsoleMeasurementService**: Full Google API integration ready
- **SeoMetadataService**: Localized metadata management
- **SeoIndexabilityService**, **SeoAuthorityService**, **ContentGapService**, etc.

**What's missing**: Activation and deployment.

---

## Wave 1: Critical Blockers (IMMEDIATE)

### 1.1 Fix 59 Missing Recipe Images
**Impact**: All recipes without images cannot get Google Rich Results (Recipe schema requires `image`)
**Blocker**: 59 recipes → 59 missing rich result cards → 59 invisible pages

**Action**:
1. Query `RecipeMediaCandidate` for all recipes without public images
2. Trigger image generation pipeline for all candidates
3. Deploy images to server (`shared/storage/app/media-import/recipes/`)
4. Update media records with public URLs
5. Verify `image` field is populated in Recipe schema

**Target**: 0 recipes without images

### 1.2 Connect Google Search Console
**Impact**: Flying blind - no data on impressions, clicks, positions, queries
**Blocker**: Cannot measure, cannot optimize, cannot prove ROI

**Action**:
1. Verify Search Console property in `config/seo.php`
2. Run `SearchConsoleMeasurementService::importApi()`
3. Verify `integrity_report` records are created
4. Set `seo.search_console.enabled = true` in `.env`
5. Schedule daily API import via scheduler

**Target**: Live Search Console data flowing

### 1.3 Activate Schema Markup
**Impact**: Without structured data, no rich results, no AI citation
**Blocker**: Schema exists in code but may not be rendering on pages

**Action**:
1. Verify `SeoSchemaService::recipe()` and `::article()` are called in controllers
2. Confirm JSON-LD is in `<head>` of all recipe and blog pages
3. Test with Rich Results Test
4. Verify FAQ schema is generating for recipes with storage/reheating notes
5. Verify Organization, WebSite, BreadcrumbList schemas present

**Target**: All recipe and blog pages have valid JSON-LD

---

## Wave 2: Geo-SEO & International

### 2.1 Hreflang Implementation
**Impact**: Multi-locale site needs proper language/region targeting
**Action**:
1. Implement hreflang tags in `<head>` for all locale variants
2. Add `x-default` pointing to fallback page
3. Verify all locale pages have self-referencing hreflang
4. Add hreflang to XML sitemaps
5. Verify no cross-locale canonicals

### 2.2 Local Business Schema
**Impact**: Cyprus-targeted queries need LocalBusiness markup
**Action**:
1. Add LocalBusiness schema with Cyprus address, phone, hours
2. Add sameAs links to Google Business Profile, social profiles
3. Add GeographicRegion metadata

### 2.3 LLMS.txt & AI Presence
**Impact**: AI engines need machine-readable context
**Action**:
1. Verify `LlmsTxtController` is operational
2. Deploy `/llms.txt` and `/llms-full.txt`
3. Deploy `/pricing.md` if applicable
4. `/okf/` bundle — DÜŞÜRÜLDÜ (2026-09-16 kararı): doğrulanmış AI-arama sinyali yok;
   `/llms.txt` + `/llms-full.txt` zaten canlı ve aynı ihtiyacı karşılıyor. Maliyet/fayda negatif.

---

## Wave 3: Backlink Blitz

### 3.1 Activate Backlink Outreach
**Impact**: 100+ prospects identified but outreach not started
**Action**:
1. Run `BacklinkGrowthService::discoverAndEnrich()`
2. Process top 20 prospects (authority_score > 85) first
3. Send personalized outreach emails
4. Track in `SeoBacklinkCandidate` table
5. Follow through on all contacted prospects

### 3.2 Content-Driven Link Building
**Impact**: Link assets need to exist before outreach
**Action**:
1. Create "Ultimate Guide to Cypriot Cuisine" (pillar page)
2. Create "Cyprus Food Heritage Map" (interactive)
3. Create "Cyprus Traditional Recipes Encyclopedia"
4. Each asset designed to earn natural links

---

## Wave 4: Content Scale

### 4.1 English Translation Push
**Impact**: 129 recipes, 70 have rich results; English gap is the growth engine
**Action**:
1. Translate top 40 recipes to English
2. Ensure all translations have images
3. Verify English metadata is SEO-optimized
4. Target English queries: "Cypriot recipes", "traditional Cyprus food"

### 4.2 Topic Cluster Expansion
**Impact**: Content needs thematic authority
**Action**:
1. Build pillar pages for: Kibris Mutfagi, Geleneksel Tarifler, ada Gulaslari
2. Create supporting cluster pages for each
3. Internal link strategy: hub → spokes → related

### 4.3 Blog & Editorial Scale
**Impact**: Blog drives topical authority and backlinks
**Action**:
1. Publish 5+ SEO-optimized blog posts per week
2. Target zero-result searches from ContentOpportunity table
3. Each blog post links to relevant recipes
4. Each blog post has full schema markup

---

## Wave 5: AI SEO (AEO/GEO)

### 5.1 LLM Citation Optimization
**Impact**: 45% of Google searches show AI Overviews
**Action**:
1. Optimize all content for extractability (40-60 word answer blocks)
2. Add FAQ sections to all recipe pages
3. Add comparison tables for "X vs Y" queries
4. Add statistics and cited sources
5. Deploy structured pricing/information pages

### 5.2 Third-Party Presence
**Impact**: 7.8% of ChatGPT citations come from Wikipedia
**Action**:
1. Create Wikipedia page for "Cypriot cuisine" or ensure accuracy
2. Establish presence on Reddit, Quora
3. Get featured on industry sites
4. Maintain updated review platform profiles

---

## Wave 6: Monitoring & Autonomy

### 6.1 Autonomous Growth Engine
**Impact**: Already built, needs activation
**Action**:
1. Enable `AutonomousGrowthEngine` as scheduled job
2. Configure daily cron: `php artisan seo:autonomous-growth` (02:45 scheduler'da aktif; dokümandaki eski `seo:autonomous-cycle` adı kodda yok — 2026-09-16 düzeltmesi)
3. Monitor daily KPI dashboard
4. 100 SEO + 100 GEO + 100 Backlink daily targets

### 6.2 Continuous Audit
**Impact**: SEO issues must be caught and resolved continuously
**Action**:
1. Run `SeoAuditService::run('all')` on schedule
2. Monitor `SeoIssue` table for new findings
3. Auto-resolve when evidence disappears
4. Weekly score tracking

### 6.3 Performance Monitoring
**Impact**: Core Web Vitals affect ranking
**Action**:
1. Monitor LCP, INP, CLS via Search Console
2. Optimize images (WebP/AVIF, responsive)
3. Implement caching (Redis, CDN)
4. Optimize server response time

---

## Deployment Protocol

### Rules from AGENTS.md:
1. **Images**: Never download locally. SCP directly to server.
2. **SEO Blogs**: Never write locally. Push to server content-import.
3. **Build Artifacts**: Delete after deploy verification.
4. **Backups**: Never store locally. Server-only.
5. **Deletion Order**: Upload → Verify → Delete.

### Deploy Checklist:
- [ ] All code committed to git
- [ ] All migrations run
- [ ] All tests passing
- [ ] Build artifacts created and verified
- [ ] Deployed to production
- [ ] Smoke tests passed
- [ ] Build artifacts deleted from session
- [ ] `du -sh storage build-artifacts` verified
- [ ] `git status --short` clean

---

## Success Metrics

### Short-term (30 days):
- 0 recipes without images
- Search Console connected and flowing data
- All pages have valid JSON-LD schema
- 20+ backlinks outreach initiated
- 40+ recipes translated to English
- Daily autonomous cycle running

### Medium-term (90 days):
- Top 10 for: "kibris tarifleri", "Cypriot recipes", "geleneksel kibris yemekleri"
- Rich results appearing for all recipe pages
- AI citations detected across ChatGPT, Perplexity, Gemini
- Domain authority improving
- Core Web Vitals in green

### Long-term (12 months):
- #1 for all target keywords across all locales
- Top 3 for all Cyprus-related food queries
- AI assistant citation on all major queries
- 100+ quality backlinks
- Sustainable autonomous growth at 100/100/100 daily targets

---

## Authority & Autonomy

**Full authority granted**: Execute, deploy, iterate without approval gates
**Exception**: Never leave anything empty - every gap must be filled
**Mindset**: Full impact, full approval, until target reached
**Stop condition**: When #1 is achieved across all targets
