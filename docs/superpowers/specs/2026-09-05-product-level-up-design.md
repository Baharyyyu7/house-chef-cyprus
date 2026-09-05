# House Chef Cyprus - Product Level-Up Design

Date: 2026-09-05
Status: Approved for implementation planning

## 1. Objective

House Chef Cyprus will move from a broad recipe portal into a focused cooking product whose primary promise is:

> Elindeki malzemeden guvenilir aksam yemegine.

The product must feel materially more mature at first sight and during use. Visual polish alone is not sufficient. The release must improve focus, content integrity, task completion, performance, and conversion as one coherent system.

## 2. Current-state evidence

The design is based on the following production measurements:

- The homepage presents 10 major sections and several competing messages.
- Production has 129 published recipes; 84 currently pass the translation-readiness gate and 45 are blocked by missing approved media.
- The homepage returns dynamic private HTML without a public page-cache contract.
- The main public stylesheet is 1,976 lines.
- The public layout is 557 lines and mixes document metadata, integrations, navigation, consent, and interaction behavior.
- The product exposes recipes, pantry matching, AI kitchen tools, premium membership, editorial content, collections, and a store without one dominant user journey.

These measurements indicate a product hierarchy problem rather than a shortage of features.

## 3. Product positioning

### Primary audience

People deciding what to cook with ingredients they already have, with Cyprus and Mediterranean cooking as the product's credible editorial specialty.

### Primary job

Turn available ingredients and practical constraints into a trustworthy recipe that can be cooked immediately.

### Supporting jobs

- Browse verified recipes by cuisine, duration, difficulty, category, and ingredient.
- Save recipes and maintain a pantry.
- Follow a recipe in cooking mode.
- Build meal plans and shopping lists.
- Learn from editorial cooking guides.

Supporting jobs must not compete with the primary job in the first viewport.

## 4. Experience architecture

The public acquisition journey is:

1. Enter or choose available ingredients.
2. Receive explainable recipe matches.
3. Open a verified recipe.
4. Start cooking mode.
5. Save the recipe, pantry, or plan when account value becomes relevant.

Registration must follow demonstrated value. Anonymous visitors can begin matching; authentication is requested only when an action requires persistence or personalization.

## 5. Homepage information architecture

The homepage will contain six major sections.

### 5.1 Hero and ingredient entry

- One headline expressing the primary job.
- One short supporting sentence.
- Ingredient entry is the dominant control.
- The primary action starts matching.
- A single secondary action opens the verified recipe archive.
- Cyprus/Mediterranean photography remains the visual signature.
- Premium, store, blog, and operational claims do not compete in the hero.

### 5.2 Today's cooking selection

- A small set of verified, approved-media recipes suitable for the current meal context.
- No placeholder, pending-review, generic, duplicate, or weakly matched image can appear.
- Selection logic is deterministic and testable.

### 5.3 Cyprus and Mediterranean authority

- A compact set of cuisine entry points backed by real recipe counts.
- Claims link to their underlying collection or policy evidence.
- Empty or threshold-failing collections are not linked.

### 5.4 Three-step product explanation

- Choose ingredients.
- Understand the match and alternatives.
- Cook step by step.

The section demonstrates the actual product flow instead of listing unrelated features.

### 5.5 Editorial trust

- Recipe verification, image integrity, nutrition uncertainty, allergen handling, and editorial review are summarized.
- Each claim links to its detailed evidence or policy page.
- Claims are not presented as testimonials.

### 5.6 Membership conversion

- Membership is framed around persistence and planning: pantry, favorites, collections, meal plans, and ad-free use where applicable.
- When payments are disabled, paid actions remain visibly unavailable and never simulate success.
- Store and donation calls remain outside the primary homepage journey unless their payment path is production-ready.

## 6. Content and media integrity

### 6.1 Showcase eligibility

A recipe can appear in homepage or marketing showcases only when all of the following are true:

- Published and editorially verified.
- Has approved public media.
- Media identity matches the recipe.
- Media is not a known duplicate assigned to unrelated recipes.
- Required public recipe fields are complete.

The existing public media eligibility service remains the source of truth and will be extended only where evidence shows a missing rule.

### 6.2 Automated reporting

Read-only quality reporting will expose:

- Published recipes without approved media.
- Media reused across unrelated recipes.
- Weak identity matches.
- Missing or generic titles and descriptions.
- Showcase records rejected by the eligibility gate.

The report does not bulk-approve or publish media. Human approval remains mandatory.

### 6.3 Fallback behavior

An ineligible image is omitted. The system does not substitute a generic food photograph that could misrepresent the recipe. Placeholder states may exist in management views but are excluded from premium public showcases.

## 7. Design-system architecture

### 7.1 Source boundaries

- Public document shell remains in `resources/views/layouts/site.blade.php` but presentation sections and interaction scripts move into focused components or modules.
- `resources/css/app.css` remains the single Vite CSS entry, with internal source files imported by responsibility if the build preserves one output asset.
- Tokens remain defined centrally.
- A component must not have competing definitions in multiple source files.

### 7.2 Visual hierarchy

- One display scale for acquisition headlines.
- One compact card system for recipe discovery.
- One primary orange action per decision area.
- Neutral supporting actions do not visually compete with the primary action.
- Section spacing and surface changes communicate hierarchy without adding decorative containers.

### 7.3 Motion

- Motion explains entry, state change, or progress.
- Decorative motion is removed when it delays comprehension or LCP.
- `prefers-reduced-motion` remains fully supported.

### 7.4 Responsive behavior

- Mobile is the primary decision layout.
- Ingredient entry, result explanation, and cooking-mode start remain reachable without horizontal scrolling.
- Desktop adds space and supporting context but does not introduce a different information hierarchy.

## 8. Matching and result explanation

Matching results must explain:

- Which available ingredients matched.
- Which ingredients are missing.
- Which substitutions are supported.
- Why a result was ranked above another.
- Any allergen or nutrition uncertainty.

The system must not claim suitability from missing dietary data. Existing safety and data-quality rules remain authoritative.

Zero-result states provide a useful next action: remove a constraint, use a supported substitute, browse the closest collection, or save demand for editorial review.

## 9. Membership and monetization

### Free value

- Browse verified recipes.
- Run a basic ingredient match.
- Read editorial guides.
- Use the core recipe instructions.

### Account value

- Save favorites and collections.
- Persist pantry state.
- Continue cooking sessions.
- Retain preferences and feedback.

### Premium value

- Advanced planning and personalization.
- Premium AI allowances where provider availability and cost controls permit.
- Ad-free experience when advertising is active.
- Additional persistent planning tools defined by the existing monetization contract.

Premium status can only originate from verified payment state. When payments are disabled, all checkout controls use the existing unavailable-state contract.

## 10. Performance architecture

### 10.1 Public HTML

Introduce a safe public-cache strategy only for anonymous, locale-stable, consent-neutral pages. Authenticated, personalized, CSRF-sensitive, or user-specific responses remain private.

### 10.2 Critical rendering

- Keep the hero image discoverable in HTML and high priority.
- Serve an appropriately sized modern image with intrinsic dimensions.
- Avoid hero animation that postpones the LCP paint.
- Keep below-the-fold images lazy.
- Defer noncritical homepage behavior until interaction or proximity.

### 10.3 Budgets

Initial release budgets:

- Mobile p75 LCP target: below 2.5 seconds.
- CLS target: below 0.1.
- INP target: below 200 milliseconds.
- No additional render-blocking stylesheet request.
- No new third-party script before consent unless essential to security or requested functionality.

Laboratory measurements are supporting evidence; production field data is the acceptance authority when sufficient samples exist.

## 11. SEO and discovery constraints

- Existing indexability thresholds remain authoritative.
- Every indexable landing page has an internal link and appears in the appropriate sitemap.
- Structured data describes only visible page content.
- No language is enabled until its content and legal surface pass the existing readiness gate.
- Generated blog or translation content remains draft until human publication review.

## 12. Error handling and safety

- A failed AI provider falls back to supported non-AI product paths without inventing results.
- Payment failures do not change premium, order, or download state.
- Missing media removes the item from showcases rather than displaying unrelated imagery.
- Cache failures fall back to uncached responses.
- Quality probes are observational unless an explicitly reviewed repair command exists.

## 13. Delivery phases

### Phase 1: Baseline and guardrails

- Record production UX, performance, content, and media baselines.
- Add tests for homepage hierarchy and showcase eligibility.
- Add observable media/content integrity reporting where absent.

### Phase 2: Primary journey

- Rebuild the homepage around the six-section architecture.
- Make ingredient matching the dominant entry.
- Improve explainable result and zero-result states.

### Phase 3: Design-system consolidation

- Split oversized layout responsibilities without changing public contracts.
- Organize the single CSS entry by tokens, primitives, components, and pages.
- Remove conflicting or unused definitions only after coverage measurement.

### Phase 4: Conversion and retention

- Align account and premium prompts with demonstrated value.
- Verify payment-disabled and verified-payment behavior.
- Connect pantry, favorites, collections, and cooking sessions into a coherent return journey.

### Phase 5: Performance and production verification

- Implement safe anonymous caching where eligible.
- Remove LCP delays and defer below-the-fold behavior.
- Run full tests, production build, accessibility checks, browser flows, structured-data checks, and live smoke tests.

Each phase must be independently releasable. No phase may require enabling an unfinished language or publishing unreviewed content.

## 14. Acceptance criteria

The project-level release is complete when:

- The first viewport communicates one product promise and one dominant action.
- The primary user journey reaches an explainable recipe result in no more than three decision steps.
- Public homepage showcases contain zero missing, pending, weakly matched, or known unrelated images.
- Mobile navigation and primary actions meet existing accessibility contracts.
- Mobile p75 LCP is below 2.5 seconds when production field data is available; until then repeatable throttled browser measurements must meet the same target.
- CLS is below 0.1 and INP is below 200 milliseconds in available field data.
- Payment-disabled flows grant no premium, completed order, or download token.
- No additional public language is enabled.
- Structured data, canonical links, hreflang output, sitemap entries, and visible content agree.
- Full automated tests, production asset build, focused browser flows, and live endpoint checks pass.
- Deployment uses the repository release script from the branch tip and cleans generated artifacts after verification.

## 15. Out of scope

- Bulk publication of recipes, blog posts, translations, or media.
- Bulk approval of weak media matches.
- Enabling English, German, Persian, Polish, or another locale.
- Replacing the existing payment provider without a demonstrated blocker.
- Building a native mobile application.
- Adding new social, marketplace, or community subsystems before the primary cooking journey is proven.

## 16. Ownership and coordination

- Application code, design, tests, and deployment follow the repository's current shared-agent ownership rules.
- Image generation and delivery remain in the designated remote media-import workflow.
- Translation delivery remains JSON-only in the designated remote translation-import workflow.
- Existing unrelated NVIDIA and Hostinger working-tree changes are preserved and excluded from this project's commits unless their owner integrates them.

