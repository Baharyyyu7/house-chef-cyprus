# Published Blog Completion QA

## Scope

The 20 currently public blog posts were audited without changing their public bodies, publication state, fact-check state, or media records. The completion command prepares private `ContentRevision` records only.

## Catalogue findings

- 20/20 topics are identifiable.
- 20/20 public bodies require editorial expansion.
- 20/20 articles do not yet have approved, topic-specific real media.
- High-risk food-safety, allergen, nutrition, storage, reheating, and temperature claims remain subject to human source review.
- No revision is automatically applied, approved, verified, scheduled, or published.

## Browser QA

The public blog list was checked in a real Chromium session at 320, 375, 768, 1024, 1440, and 1920 pixels. A high-risk blog detail was checked at 320 and 1920 pixels in light and dark themes. The private admin comparison page was checked at 375 and 1440 pixels with an authenticated local admin fixture.

Observed results:

- no browser console errors or warnings;
- no visible horizontal overflow in the checked viewports;
- the responsive navigation remained usable;
- articles without approved media rendered the explicit no-photo state;
- the admin comparison showed current and proposed content, fact-check sources, media brief, and all blocking states;
- no public content or media changed during QA.

Screenshots are local QA artifacts under `output/playwright/blog-completion/` and are intentionally not committed as production assets.

## Publication gate

Publication remains blocked until a named human editor approves the proposed article, an authorized reviewer completes any required source-backed fact-check, and a topic-specific image with verified licence/provenance passes media review. These are independent gates.
