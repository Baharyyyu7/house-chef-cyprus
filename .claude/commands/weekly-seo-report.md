---
description: Generate weekly SEO report for HouseChef Cyprus
argument-hint: (optional date range)
allowed-tools: Bash, Read, Glob, Grep, WebFetch, WebSearch
---

Generate a weekly SEO report for HouseChef Cyprus.

1. Run `/tmp/housechef-seo-monitor.sh` to get baseline data
2. Check Search Console data for: queries, clicks, impressions, CTR, positions
3. Compare with previous week's data
4. Identify:
   - Top gaining pages
   - Top declining pages  
   - New keywords appearing
   - Keywords lost
   - Rich result eligibility changes
5. Report in plain English: "The page gained X impressions, Y clicks, CTR changed from Z% to W%"
6. End with: next actions, owners, and missing evidence
7. Save report to `/tmp/housechef-weekly-seo-report-YYYY-MM-DD.md`
