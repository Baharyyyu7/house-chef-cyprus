# Mobile, Accessibility, and Performance Benchmark

## Benchmark principles

- Search, filters, ingredient checklists, and save actions must remain usable with a thumb and keyboard.
- Cards need explicit image dimensions, stable height, alt text, and non-image fallbacks that are honestly labelled.
- The mobile filter path needs clear state, reset, focus management, and a result count.
- Third-party scripts should be consent-aware and budgeted.

## House Chef audit

Current public pages use responsive grids, dark mode, skip links, focus-visible styles, lazy-loaded approved media where present, and mobile-friendly recipe filters. The current test program covers public routes and core interaction contracts; it needs a repeatable browser accessibility pass at 320/375/768/1024/1440/1920 for the Phase B scope.

## Measurable Phase B targets

- No horizontal scrolling at target widths.
- No layout-shifting recipe-card image when dimensions are known.
- Keyboard reachable search, filters, cards, save action, and form errors.
- No heavyweight third-party script without consent and a clear user benefit.
- Image derivative policy: hero, desktop card, mobile card, square/social, thumbnail; AVIF/WebP plus a safe fallback where supported.
