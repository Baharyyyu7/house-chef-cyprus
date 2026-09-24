# Product level-up implementation

Goal: Make the public ingredient-to-recipe journey the primary product, with a trustworthy showcase.
Architecture: Keep the existing ingredient matching and media eligibility services authoritative. Cache public lookup data, never authenticated HTML or approval decisions. Compose the homepage separately from the archive.
Tech stack: Laravel, Blade, Alpine, Vite, PHPUnit.
Spec: docs/superpowers/specs/2026-09-05-product-level-up-design.md

Constraints: No locale activation, publishing, bulk media approval, unrelated AI/deployment edits or dirty-build bypass.

1. Add feature coverage for homepage media exclusion, public ingredient submission and six-section composition. Run tests before implementation.
2. Replace the homepage branch with a dedicated partial. Preserve archive routes and internal collection links. Put a real ingredient selector in the hero, followed by eligible recipes, regional discovery, three steps, verifiable editorial evidence and membership.
3. Use PublicMediaEligibilityService for all featured recipes. Preserve immediate revocation; cache only ingredient lookup data with a bounded TTL.
4. Inspect layout/CSS responsibilities and browser behavior; make scoped improvements backed by regressions. Preserve consent and payment gates.
5. Run targeted and full tests, Vite build, desktop/mobile browser checks and performance measurements. Release only through scripts/release.sh from the branch tip. Verify live HTML, images and public matching. Report unachieved performance targets explicitly.
