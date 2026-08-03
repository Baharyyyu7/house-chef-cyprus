# SEO, Search and Public UX Completion Ledger

This ledger deliberately separates repository evidence from an active-release
browser result. A task is never `PUBLIC_VERIFIED` solely because a local test,
an artifact, or a database record passed.

| Task ID | Surface | Defect / objective | State | Test state | Deployment state | Public verification | Blocker / next action |
| --- | --- | --- | --- | --- | --- | --- | --- |
| SEO-001 | Public canonical and robots | Stable canonical URLs and query-driven noindex boundaries | CODE_COMPLETE | Existing feature coverage | ARTIFACT_READY | BLOCKED_EXTERNAL | Run smoke and browser checks on the active Hostinger release. |
| SEO-002 | Sitemap index, content and image sitemap | Public-only URLs and approved image entries | CODE_COMPLETE | Existing sitemap audit coverage | ARTIFACT_READY | BLOCKED_EXTERNAL | Request live sitemap endpoints and validate XML plus image HTTP status. |
| SEO-003 | Recipe and BlogPosting schema | Schema uses only public, approved image URLs | CODE_COMPLETE | Existing schema coverage | ARTIFACT_READY | BLOCKED_EXTERNAL | Validate rendered JSON-LD on the active release. |
| SEO-004 | Blog media | One record-level media audit row per public blog; placeholders cannot be reported as ready | TESTED_LOCALLY | `blog-media:completion-audit` regression added | ARTIFACT_READY | BLOCKED_EXTERNAL | Deploy the exact release, then run with `--probe-public` and review each row. |
| SEO-005 | Open Graph and X image metadata | Image dimensions and alternative text only for approved public article or recipe media | CODE_COMPLETE | Feature regression added | ARTIFACT_READY | BLOCKED_EXTERNAL | Inspect public HTML and image URLs after deployment. |
| SEO-006 | Search and filtered discovery | Query variants remain canonicalized and noindex | CODE_COMPLETE | Existing indexability coverage | ARTIFACT_READY | BLOCKED_EXTERNAL | Browser-test public recipe and blog search on production. |
| SEO-007 | Internal links and orphan prevention | Existing audit generates evidence; editorial repair remains human-reviewed | IN_PROGRESS | Existing SEO audit coverage | NOT_DEPLOYED | BLOCKED_EDITORIAL | Review genuine related-content proposals; do not manufacture links. |
| UX-001 | Public cards and heroes | Missing media renders an honest state, not an unrelated image | CODE_COMPLETE | Existing view/media coverage | ARTIFACT_READY | BLOCKED_EXTERNAL | Verify at 375/768/1024/1440 on production. |
| PERF-001 | Asset delivery | Vite manifest and immutable built assets included in full artifact | TESTED_LOCALLY | Build validated | ARTIFACT_READY | BLOCKED_EXTERNAL | Measure real production response and Core Web Vitals; no score is assumed. |

## Required active-release checks

```bash
php artisan seo:audit --json
php artisan seo:sitemap-audit --json
php artisan blog-media:completion-audit --probe-public --json
php artisan app:post-deploy-smoke --json
```

The final command must be run only after the release is active and maintenance
mode is disabled. High-risk editorial or source-backed content remains blocked
until a qualified human review records reliable evidence.
