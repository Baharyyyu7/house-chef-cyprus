# House Chef Cyprus Product Requirements

## Product invariants

1. Public recipe and blog pages only expose approved publication states.
2. A verified label, editor pick, rating, popularity claim, nutrition fact, source, or media status must have a real supporting record.
3. Health, storage, allergy, and nutrition guidance uses uncertainty language and requires source/reviewer workflow where risk warrants it.
4. Automation may suggest, classify, draft, and prepare; it cannot approve, verify, or publish.
5. All public identifiers use public IDs or slugs; internal database IDs are not serialized in public interfaces.
6. Personalization and analytics are consent-aware, minimized, and controllable by the user.
7. Community features remain private/disabled until moderation, reporting, audit, spam, and blocking controls are complete.

## Non-functional requirements

- Keyboard, screen-reader, reduced-motion, light/dark, and responsive support at 320/375/768/1024/1440/1920.
- Explicit image dimensions and approved responsive variants.
- Stable canonical URL, structured data matching visible page content, and noindex handling for low-value combinations.
- Tests, static analysis, formatting, documentation links, secrets/forbidden files, and deployment checks are release gates.
