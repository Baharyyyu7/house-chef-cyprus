# HouseChef Cyprus — 90-Day Distribb SEO Sprint

**Status:** ACTIVE — Ready for deployment
**Distribb Integration:** Configured (awaiting API key)
**Date:** 2026-09-17
**Domain:** https://housechefcyprus.com

---

## Prerequisites (Before Sprint Starts)

- [ ] DISTRIBB_API_KEY added to `.env` and `.env.production.template`
- [ ] `DISTRIBB_API_ENABLED=true` set in production
- [ ] `/distribb-setup` executed to verify API connection
- [ ] Onboarding completed at distribb.io
- [ ] CMS connected via Distribb
- [ ] Google Search Console connected via Distribb
- [ ] 3-7 competitors added in business context
- [ ] Content pillars defined
- [ ] AI instructions set (tone, profile, positioning)

---

## Week 1-2: Foundation & Measurement

### Day 1-3: Distribb Setup
1. Execute `/distribb-setup` — verify all connections
2. Complete onboarding: business description, competitors, pillars
3. Connect CMS (Laravel API Webhook)
4. Connect Google Search Console
5. Verify GSC data flowing

### Day 4-7: Baseline Audit
1. Execute `/gsc-audit housechefcyprus.com`
2. Review the 9 analyses:
   - CTR optimization
   - Content decay
   - Striking distance / page-2 keywords
   - Cannibalization
   - Dead pages
   - Brand vs non-brand health
   - Topical clusters (topic cocoons)
   - Competitor gaps
   - On-page checks
3. Review `/ai-visibility` for current AI citation status
4. Establish baseline metrics

### Week 2: Content Audit
1. `/keyword-research` for top 20 seed keywords
2. Verify 59 missing recipe images (critical for Rich Results)
3. Audit existing 50 published recipes for schema completeness
4. Identify cannibalization in Turkish content
5. Check `/optimize` recommendations

---

## Week 3-4: English Infrastructure

### Week 3: English Architecture
1. Verify `/en/` routes are live (hreflang confirmed working)
2. Audit English recipe pages for:
   - Complete English metadata
   - Proper Recipe schema in English
   - English alt text on images
   - English internal links
3. Create English hub pages:
   - `/en/cyprus-cuisine`
   - `/en/traditional-cypriot-recipes`
   - `/en/mediterranean-recipes`
   - `/en/halloumi-recipes`
4. Set up English content pillars in Distribb

### Week 4: English Content Creation
1. Execute `/write-article` for top English keywords:
   - "Cypriot recipes"
   - "Traditional Cyprus food"
   - "Halloumi recipes"
   - "Cyprus meze"
   - "Sheftalia recipe"
2. Each article must include:
   - Backlink exchange link
   - Proper Recipe schema
   - English metadata
   - Internal links to Turkish originals
3. Publish 5-10 English articles

---

## Week 5-8: Content Clusters & Optimization

### Week 5-6: Turkish Content Refresh
1. Execute `/optimize` on pages 4-20 positions
2. Refresh 20 blog posts with updated SEO
3. Add missing Recipe schema to any pages without it
4. Fix any remaining image issues
5. Execute `/content-calendar` to schedule future content

### Week 6-8: English Content Scaling
1. Write 15-20 English articles (target: 3-4/week)
2. Build topic clusters:
   - Halloumi cluster: 5-8 articles
   - Cyprus cuisine cluster: 5-8 articles
   - Mediterranean cluster: 5-8 articles
3. Execute `/backlinks` to start link exchange
4. Build internal linking between clusters
5. Execute `/gsc-audit` every 2 weeks to track progress

---

## Week 9-12: Authority & Scaling

### Week 9-10: Link Building
1. Execute `/link-outreach` for digital PR
2. Create link-worthy assets:
   - Cyprus food glossary
   - Seasonal produce calendar
   - Ingredient equivalence database
   - Traditional dish comparison guide
3. Target Cyprus publications, Mediterranean food sites, travel publications
4. Execute `/content-calendar` for consistent publishing

### Week 11-12: Measurement & Iteration
1. Execute `/gsc-audit` final review
2. Execute `/statistics-page-writer` for link-worthy data
3. Execute `/weekly-seo-report` (automated)
4. Compare week 1-4 vs week 9-12 metrics
5. Plan next 90-day cycle

---

## Automation Commands

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `/gsc-audit` | Weekly | Track SEO performance |
| `/optimize` | Weekly | Fix declining pages |
| `/content-calendar` | Weekly | Plan next week's content |
| `/keyword-research` | Monthly | Discover new opportunities |
| `/backlinks` | Weekly | Manage link exchange |
| `/ai-visibility` | Weekly | Track AI citations |
| `/statistics-page-writer` | Monthly | Create link-worthy data |
| `/weekly-seo-report` | Weekly | Automated reporting |

---

## Success Metrics (90-Day Targets)

- Top 10 keywords: +50% growth
- English impressions: +200% growth
- Top 3 keywords: +25 growth
- Referring domains: +20
- Backlinks: +10 (exchange)
- Recipe rich results: 100% eligible
- Core Web Vitals: All pass
- Crawl budget: Optimized
- AI citations: Measurable presence
- Organic sessions: +100%
