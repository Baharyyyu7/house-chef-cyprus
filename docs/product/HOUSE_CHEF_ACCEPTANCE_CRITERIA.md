# House Chef Cyprus Acceptance Criteria

## Phase B release criteria

- [ ] Homepage recommendations have a real source rule; unsupported "popular"/"trending" sections are absent.
- [ ] Search resolves supported Turkish normalization/aliases and preserves the common filter contract.
- [ ] Filters expose clear labels, keyboard operation, active state, reset, result count, and mobile drawer focus handling.
- [ ] Filter URL policy has canonical/noindex coverage and test evidence.
- [ ] Homepage, list, and discovery pages pass responsive QA at the defined widths and both themes.

## Phase C release criteria

- [ ] Card images are approved real media or intentional non-food/text-only presentation; no branded fallback is represented as food.
- [ ] Card/detailed metadata comes from real recipe fields only.
- [ ] Detail sections render only with available approved content.
- [ ] Serving scaling and measurements retain current precision and validation.
- [ ] Recipe schema is valid and equals visible published facts.

## Cross-phase gates

- [ ] No auto-publish/auto-verify path exists.
- [ ] Draft/review/evidence metadata is absent from public, API, sitemap, and schema output.
- [ ] PHPUnit, Pint, PHPStan, production build, docs links, security scans, and relevant browser QA pass.
- [ ] Rollback plan and administrative ownership are documented for each migration/change.
