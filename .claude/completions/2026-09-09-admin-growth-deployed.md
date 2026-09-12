# Admin panel and organic growth deployment

## Release

- User explicitly requested planning, implementation and production deployment.
- Live admin: https://housechefcyprus.com/yonetim
- Growth operations: https://housechefcyprus.com/yonetim/seo/buyume
- Commit: `0b28e7508a2662b779f83f9f25dec50a0f8f46e9`
- Pushed branch: `codex/admin-growth-20260909`
- Isolated worktree: `/tmp/hc-admin-growth-20260909`
- Plan: `docs/superpowers/plans/2026-09-09-admin-growth.md` in that worktree.
- Main checkout contains pre-existing work by others; no merge, reset or broad staging was performed there.
- Production current release and immutable physical web bootstrap point to this commit.
- Retained rollback release: `f832e898787cfedfb896500f2fcdfd7ba86806ff`.

## Delivered

- Shared compact light/dark admin design, consistent headings, responsive tables and controls.
- Dashboard, accessible navigation/search, mobile drawer focus handling and sidebar collapse improvements.
- World map retained, including visible geography with empty data, accurate member/visitor labels and keyboard tooltips.
- Media previews distinguish missing files from real images; no media records were bulk approved.
- Organic growth operations: bounded daily opportunity discovery, source-backed backlink candidates, CSV import, editorial review and run history.
- Daily schedule: `49 1 * * *` UTC, `seo:growth-refresh --limit=1000`.
- Capacity is 1,000 discovery items per run and 10,000 CSV rows per import, not a claim of acquired backlinks.
- URL validation, deduplication, transaction rollback, cursor rotation, locks and failure reporting are covered by tests.
- No automated outreach, external HTTP requests, publishing or locale activation in the growth refresh.
- Deployment media import defaults to opt-in; this release used `IMPORT_APPROVED_MEDIA=0`.

## Verification

- Focused cross-module suite: 112 tests passed, 743 assertions. Full repository suite was not run.
- Pint, targeted PHPStan, shell syntax, production build and whitespace checks passed.
- Local responsive checks covered admin routes, 320/390px widths, desktop, dark mode and navigation interactions.
- Live browser checks completed for 25 admin destinations with new assets, correctly sized headings and no document overflow.
- Live world-map tooltips and real media previews were verified.
- Chrome subsequently returned `ERR_BLOCKED_BY_CLIENT`; remaining live destinations were not screen-verified. No browser protection was disabled or bypassed.
- Live smoke endpoints returned HTTP 200: `/`, `/tarifler`, `/blog`, `/login`, `/register`, `/robots.txt`, `/sitemap.xml`, `/health`, `/api/v1/health`.
- Database readiness and scheduler heartbeat reported healthy; migration completed and caches rebuilt.
- Production source and built CSS SHA-256 values matched local files.
- Source CSS SHA-256: `2c4fad52a8790e66df1205f1e02ca31497505eb3e943243642823b891bb44c3c`.
- Built `app-C4K05Weq.css` SHA-256: `0c85ea8eb580c01856884b0cc0eb550cdbfacad055e419b24829c3ae1b1fb698`.
- Deployment artifact SHA-256: `c45d5a9fedc9f23af69fe793f690afe42d38e49c0599f2028f40b368892e40d9`.

## Production backup and observed results

- Verified pre-deployment database backup: `01M21J39G7DFNPF8HBV2B4S8WY`, created `2026-09-08T22:26:55Z`.
- Database backup SHA-256: `ff8ab6e4db020de74f65857fb923214f7f416b3a57a54d80f4356e69c57a161c`.
- This was a database backup, not a full media backup; restore was not tested in this run.
- Initial production growth refresh succeeded: 1 review opportunity, 0 backlink candidates, 0 outreach messages, 0 external HTTP requests, no published content.
- Fresh application SEO audit: score 90, one remaining editorial-quality issue on the existing private-chef article. This is an internal audit score, not a Google ranking metric.
- The live article and its review revision were not published or rewritten by this release.
- Local seeded counts differ materially from production and must not be reported as production metrics.
- Persistent Google API authorization remains an external dependency. Third-party backlink placement remains subject to those publishers' decisions.

## Cleanup

- Uploaded release verified before deleting this session's local archive and checksum.
- `/tmp/hc-admin-growth-20260909/build-artifacts` was removed in the same session.
- This session's development server on port 8012 was stopped; the existing port 8011 server was not touched.
- Browser viewport override was reset. No new local content/image deliveries or backups were retained.
