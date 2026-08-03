# Task Ledger

| Priority | Work item | State | Evidence / next action |
| --- | --- | --- | --- |
| P0 | MySQL backup manifest workflow | locally validated | `3de77ca` removes backup commit metadata dependence on `shell_exec`; MySQL-focused tests, local create/verify/health, Pint, and PHPStan passed on 2026-08-03. |
| P0 | Production backup create/verify/health | blocked externally | Requires authorized production SSH and a functioning production release; do not run against live data from this workspace. |
| P0 | Production release safety | planned | Existing Hostinger release scripts and artifacts must be validated before any deploy. |
| P0 | Contact/SMTP production delivery | configuration-dependent | CRM is implemented; live delivery requires a configured controlled mailbox and SMTP credentials. |
| P1 | Approved brand migration | complete in code | Official assets and central usage were committed in `3c5267f`; later visual audit remains separate. |
| P1 | Unified legal/contact center | complete in code | Legal center and contact CRM were committed in `9b3862d` and `c62a8e1`. |
| P1 | Benchmark and product roadmap | complete in docs | Original, non-copied benchmark committed in `c3bb7f5`. |
| P1 | Editorial/media expansion | human-review blocked | Local quality scan found 630 blocked profiles; media health has 0 approved-real-media, 70 branded fallbacks, and 630 missing-media records. No recipe/blog publication or media approval may be automated. |
| Quality | Full local regression | passed | 338 tests: 337 passed, 1 safe skip, 1,823 assertions; Pint, PHPStan, build, secret scan, forbidden-file scan, docs link check, and diff check passed on 2026-08-03. |
| Quality | Browser validation | blocked by browser binding | Local HTTP smoke passed for `/`, `/tarifler`, and `/login`; no interactive browser tab was available, so this is not recorded as visual browser QA. |
| SEO | Technical audit | human editorial/media follow-up | Local audit score 80; 5 recipe-description warnings, no critical/error findings, and 0 approved public images. |
