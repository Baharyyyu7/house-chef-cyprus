# Production Catch-up Ledger

Snapshot: 2026-08-03. Source of comparison: production release
`27e1f6910c8206e3983c45f5ee92f770da0addc3` and
`origin/visual-redesign-v2` at `638a55777e11b6cc2e0e1e75da23dd1b611cc2cd`.

Production is **28 commits behind** this source snapshot. The local and origin
branch heads match. This is not a deployment authorization: every row remains
blocked from deployment until the current quality gates, artifact validation,
fresh backup and authorized Hostinger dry-run are evidenced.

| SHA | Title | Affected module | Migrations | Runtime effect / risk | Current test evidence | Deployment eligibility |
| --- | --- | --- | --- | --- | --- |
| `f81b0ba80228d7ba40dc693a5d4ade8250924857` | queue published recipe editorial reviews | Editorial review | Inspect during release plan | Queues review proposals; must not publish automatically. | Not re-run in current environment. | Blocked by PHP 8.4 quality gate. |
| `4e101014d8d4ae5c8b9c1f8711b0860cc4112f53` | batch published recipe review queue | Editorial review | Inspect during release plan | Bounded review processing. | Not re-run. | Blocked by quality gate. |
| `e4d3ce585b60abb70bcf7f1b4d12c032cfc466bc` | queue draft recipe editorial reviews | Editorial review | Inspect during release plan | Queues draft-only review work. | Not re-run. | Blocked by quality gate. |
| `19431d16b1ef30effef6376a1a5eb5263542e743` | disclose unapproved recipe media | Public recipe media | None expected; inspect artifact migration plan. | Removes false implication that fallback media is approved. | Not re-run. | Blocked by quality gate. |
| `5af61d601f2b16d1c9f444a296204ca22a0ab092` | report catalogue review readiness | Editorial operations | Inspect during release plan | Reports review readiness only. | Not re-run. | Blocked by quality gate. |
| `82a9c176ab2978b8f479b8414ba38b545ad781c9` | add production SEO indexability guards | SEO | Inspect during release plan | Guards canonical/indexability output. | Not re-run. | Blocked by quality gate and live crawl. |
| `60a876194fb1b525fd6722c6e0e6a2cef3eee024` | add sitemap audit automation | SEO | Inspect during release plan | Adds audit path; does not prove live sitemap. | Not re-run. | Blocked by quality gate. |
| `81310abd2cb98cf3dec79074be2a41c59d8c2c0a` | simplify legal information center | Public/legal | Inspect during release plan | Changes public legal routing and presentation. | Not re-run. | Blocked by quality gate and owner/legal text review. |
| `0f200b7e6621c04648f0003492428c9de3146984` | harden authentication and recipe query performance | Auth/performance | Inspect during release plan | Authentication and query behavior require regression verification. | Not re-run. | Blocked by PHP 8.4 suite and browser QA. |
| `a070c21a2cb4e48ac350f8828520e18c182f01d6` | make public content media states truthful | Public media | Inspect during release plan | Prevents unapproved media from being represented as complete. | Not re-run. | Blocked by quality gate. |
| `8e6863c67d56fdc46ddfb39daf2af75dc1b54e1b` | add secure new member registration notifications | Mail/auth | Inspect during release plan | Adds queued notification; SMTP must be verified live. | Not re-run. | Blocked by SMTP/browser verification. |
| `78dcd37e0393325ada779f19d2c9562f17ee4c8c` | prepare published recipe repair batch one | Recipe content | Inspect during release plan | Creates review proposals; no unsafe automatic publish. | Not re-run. | Blocked by editorial review. |
| `8332d47681cc70bca240931e3a1b78fc39e4283c` | queue remaining published recipe reviews | Editorial review | Inspect during release plan | Bounded review queue. | Not re-run. | Blocked by quality gate. |
| `c63fd3c80469a3951bf87d3b222232522057ef3a` | audit draft recipe catalogue in controlled batches | Recipe content | Inspect during release plan | Draft audit only; no public change intended. | Not re-run. | Blocked by quality gate. |
| `f74f35667b25e0ee0888df7da880f5f5b183aadf` | establish v1 completion evidence ledger | Operations docs | None expected. | Evidence documentation. | Static review only. | Safe after quality/artifact verification. |
| `e95b7a27d414de461ee7f4791f4dd02750c5d622` | add private member recipe collections | Member platform | Inspect during release plan | Private member data and authorization require feature/browser regression checks. | Not re-run. | Blocked by quality gate. |
| `b53bd5533ad708ec43974e12a470f499e86b57ef` | add privacy-safe google integration readiness | Integrations | None expected; inspect release plan. | Readiness only; no analytics provider is asserted active. | Not re-run. | Blocked by configuration review. |
| `d5ab8e6571d544bc41fa28b881888673d6c28f94` | integrate hostinger mail operations visibility | Mail/operations | Inspect during release plan | Operational display; no SMTP claim. | Not re-run. | Blocked by quality gate and live mail evidence. |
| `58a194281976c898c5f900611b320f0770987bed` | expand hostinger mail and performance operations | Mail/performance | Inspect during release plan | Requires safe runtime verification. | Not re-run. | Blocked by Hostinger access. |
| `5fc9e22e2b97be8811134f627616322dab64481a` | prepare published blog completion reviews | Blog content | Inspect during release plan | Review proposals only. | Not re-run. | Blocked by editorial review. |
| `afca4b6e5505bc3edddd42854c064025ac100042` | complete public recipe media safety pipeline | Recipe media | Inspect during release plan | Enforces truthful media gate; requires editorial identity confirmation. | Not re-run. | Blocked by editorial review. |
| `f055eb0e723b54c19679bf5e6a3fd0da5c99b83a` | provision public storage link in strict profile | Storage/deploy | Inspect during release plan | Storage behavior must be verified against physical `public_html`. | Prior CI evidence exists but is not current proof. | Blocked by authorized dry-run. |
| `97a30d028df16e3001b47cde6f52023165224f89` | add approved synthetic editorial blog media | Blog media | Inspect during release plan | Imports twenty user-approved synthetic assets with provenance. | Not re-run. | Blocked by artifact/import/live URL verification. |
| `2b81a86b323f7e90f7f3f37ed03cff37f3186eff` | import approved blog media during deployment | Deploy/media | Inspect during release plan | Deployment invokes bounded blog-media import. | Not re-run. | Blocked by Hostinger dry-run. |
| `ab4145405a40ba2b3298e7f7dfb22563b8d2fcfa` | require complete blog media variants | Blog media | None expected; inspect release plan. | Rejects incomplete variant sets. | Not re-run. | Blocked by production codec capability check. |
| `405802946ffc18ed29b864d5c34f1ce776b2aa6d` | record approved blog media provider policy | Documentation | None expected. | Documents approved synthetic provenance policy. | Static review only. | Safe after release selection. |
| `1fa8cbe2c0cf6f3e8ff386f5fd0635aa0f536e7f` | add final product completion ledger | Documentation | None expected. | Evidence-led completion ledger. | Static review only. | Safe after release selection. |
| `638a55777e11b6cc2e0e1e75da23dd1b611cc2cd` | process approved blog media deterministically | Blog media | None expected. | Limits optimization to imported records and makes AVIF conditional on supported codec. | PHP syntax and `git diff --check` passed; full suite not re-run. | Blocked by PHP 8.4 quality gate, clean artifact and live verification. |

## Release selection

No commit in this table is currently marked deployment-safe because the required
quality suite cannot run in this local environment: the project dependencies
require PHP 8.4 while the available local XAMPP PHP is 8.2. Production access is
also unavailable to this execution environment, so a fresh backup, server
checksum, dry-run, active-release proof and live HTTP/media acceptance cannot be
performed here.

The next safe command once a PHP 8.4 runtime is available is the project’s full
quality suite from `638a55777e11b6cc2e0e1e75da23dd1b611cc2cd`; do not build or
upload a release artifact before it passes.
