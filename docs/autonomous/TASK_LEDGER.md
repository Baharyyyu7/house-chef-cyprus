# Task Ledger

| Priority | Work item | State | Evidence / next action |
| --- | --- | --- | --- |
| P0 | MySQL backup manifest workflow | validating | `3de77ca` removes backup commit metadata dependence on `shell_exec`; run focused tests and static checks. |
| P0 | Production backup create/verify/health | blocked externally | Requires authorized production SSH and a functioning production release; do not run against live data from this workspace. |
| P0 | Production release safety | planned | Existing Hostinger release scripts and artifacts must be validated before any deploy. |
| P0 | Contact/SMTP production delivery | configuration-dependent | CRM is implemented; live delivery requires a configured controlled mailbox and SMTP credentials. |
| P1 | Approved brand migration | complete in code | Official assets and central usage were committed in `3c5267f`; later visual audit remains separate. |
| P1 | Unified legal/contact center | complete in code | Legal center and contact CRM were committed in `9b3862d` and `c62a8e1`. |
| P1 | Benchmark and product roadmap | complete in docs | Original, non-copied benchmark committed in `c3bb7f5`. |
| P1 | Editorial/media expansion | human-review blocked | No recipe/blog publication or media approval may be automated. |
