# Ingredient conversion model

`ingredient_unit_conversions` stores ingredient-specific household to canonical mappings. A conversion has a public ULID, source, method, verification status, confidence and validity period. Seed data uses `seed_demo` / low confidence and is deliberately not presented as verified production data.

## Phase 2 closure

Recipe ingredient lines and pantry items retain both the household display value and any resolved canonical value. Missing mappings remain explicitly unknown; no generic cup/spoon-to-gram assumption is stored. Inactive units are rejected for new input while legacy records remain compatible.
