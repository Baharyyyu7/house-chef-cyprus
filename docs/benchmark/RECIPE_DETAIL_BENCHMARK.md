# Recipe Detail Benchmark

## High-value patterns

- A scannable hero establishes the recipe identity, key times, servings, and save/print actions.
- Ingredients, steps, and practical storage information are the working core.
- Cooking guidance has more value when it is specific, sourced where safety-relevant, and not overloaded with generic prose.
- Related content should be demonstrably relevant by cuisine, ingredient, technique, or editorial relationship.

## House Chef audit

The current detail page already offers a responsive hero, servings scaling, checkable ingredients, canonical measurement context, numbered steps, timing, nutrition confidence, allergen/dietary notices, storage/reheating, provenance, and related recipes. It correctly keeps disclaimer language visible.

## Original next improvements

- Render sections only when editorial data exists; remove generic empty-state prose from published detail pages.
- Add a compact print-specific stylesheet and a transparent "last reviewed" block when a review timestamp is real.
- Add a facts-only FAQ only where source-backed questions exist; no synthetic FAQ generation.
- Introduce an approved-media gate: a branded graphic must never impersonate a food photograph.
