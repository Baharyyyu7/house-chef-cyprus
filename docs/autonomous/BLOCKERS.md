# Blockers

## External / human-controlled

- Production backup create, verify, and health commands need an authorized
  production SSH session and must be run only after production release gates
  pass. No result is inferred from local tests.
- SMTP acknowledgement and notification delivery need production mail settings
  and controlled mailbox verification.
- Editorial fact-check, recipe changes, and media approval require accountable
  human reviewers. Automation may prepare candidates but may not approve or
  publish them.
- Legal text changes require the supplied approved text and, where required,
  legal-owner review.
- Visual/browser QA requires an available interactive browser binding. The
  local HTTP smoke check is intentionally recorded separately and does not
  substitute for responsive, keyboard, or visual validation.

## Content and media audit (2026-08-03 local scan)

- The idempotent content scan found 500 recipe and 200 blog profiles, with 630
  profiles blocked for human source/fact-check or real-media approval.
- Media health reported zero approved-real-media items, 70 branded fallbacks,
  and 630 items without media. A fallback is not a dish image and is not
  eligible for a claim of complete visual coverage.
- These are human-review and licensed-media gates, not an authorization to
  change publication or verification state automatically.

## SEO audit snapshot (2026-08-03 local scan)

- Technical SEO score is 80 with no critical or error-level finding. Five
  published recipes need editorial description expansion.
- The public catalogue has 50 indexable recipes and 20 indexable articles, but
  zero approved public images. Image schema/sitemap coverage must remain absent
  until real media has a documented licence and human approval.

## Not blockers to local work

- 2,340 human review records remain operational backlog; they are not silently
  resolved or represented as automated approval.
