# Autonomous Master Completion Plan

This ledger governs safe, production-oriented work on `visual-redesign-v2`.
It is a delivery plan, not a declaration that editorial, media, legal, or
production approvals have been completed.

## Priority order

1. MySQL backup reliability, verification, and deployment safety
2. Production release safety, contact delivery, scheduler, queue, security
3. Approved brand integration and public-product quality
4. Content, media, editorial, SEO, and benchmark follow-up work

## Invariants

- Production changes require the release gates documented in the deployment
  runbook; local validation alone is never treated as a live deployment.
- Published content is not automatically verified, approved, or expanded.
- Private media candidates remain private until a human approves them.
- Credentials, mail secrets, database passwords, and deploy keys are never
  committed or emitted in diagnostics.
- A blocked external dependency is recorded and independent local work may
  continue.

## Current execution focus

The MySQL backup hotfix at `3de77ca` is present in this branch. Its local
contract must be revalidated before any production continuation is proposed.
Production backup execution remains blocked until a separately authorized SSH
session and the production release gates are available.
