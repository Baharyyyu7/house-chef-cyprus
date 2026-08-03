# Mobile Navigation Map

## Core flow

1. Header: logo, theme control, menu control.
2. Drawer: Discover, Pantry/matching, Favourites, Guides, account/legal links.
3. Recipe results: search first, filter trigger with count, applied chips, sort, result grid.
4. Detail: compact summary, ingredient/steps tabs or anchor rail, sticky action only if it does not hide content.

## Accessibility contract

- 44px minimum interactive target where feasible.
- Open drawer moves focus to its heading; closing returns focus to trigger.
- Filter drawer preserves selected filters and announces result/state change.
- Controls work at 320px without clipped labels or horizontal page scrolling.
- `prefers-reduced-motion` removes non-essential motion.
