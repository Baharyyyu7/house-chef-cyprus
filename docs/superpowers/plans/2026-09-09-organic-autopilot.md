# Organic operations follow-through

## Scope and authorization

The user requested implementation and deployment without repeated approval prompts, including background SEO and backlink work. Preserve identity, content quality and security checks. Do not treat a candidate, a sent pitch or an editor note as an acquired link. Do not mass-publish the existing draft pool or bulk-approve unmatched media.

## Evidence

- User screenshot: admin HTTP 429. The existing browser session loaded the dashboard after one ordinary reload; application admin routes have no throttle. Exact original response headers are unavailable. No permanent root cause is claimed.
- Existing link probe sends dozens of sequential requests without rate-limit backoff and turns failed sitemap fetches into zero orphan counts.
- Search Console UI is authorized, but server OAuth client and secret are absent. The existing import command is a placeholder.
- Existing daily server jobs and one Codex heartbeat already exist. Extend them, do not duplicate them.

## Implementation

1. Tests and fixes for polite link probes: one request cache, minimum interval, stop on 429/503, persist Retry-After, distinguish incomplete observations from broken links.
2. Implement validated, idempotent Search Console measurement import with exact property/date/source attribution, partial query samples and stale-data status. Add optional real OAuth-refresh API import without inventing credentials or using private browser tokens.
3. Feed measured query opportunities into the existing review queue without reopening reviewed items. Display actual measurements in growth operations and the morning brief.
4. Verify known backlink candidate pages with DNS/IP safeguards, bounded response size, no redirects, modest per-run limits, next-check dates and separate machine evidence. Never send messages from this job.
5. Research relevant real publisher/producer candidates, deliver useful source-backed SEO content to the server JSON inbox and advance only legitimate outreach. Existing account/consent boundaries remain applicable.
6. Update the existing heartbeat to perform daily measurement import, bounded editorial production and appropriate publisher follow-through, with quiet unchanged-state behavior.
7. Run focused and regression tests, static checks, production build, server backup, deploy and live smoke. Record exact outcomes and unresolved external dependencies. Remove local build artifacts after verification.

## Completion criteria

Report independently: current admin accessibility, deployed code, imported Google measurements, created content drafts, researched candidates, sent messages and actually detected links. Do not conflate capacity with completed output or UI import with persistent Google API authorization.
