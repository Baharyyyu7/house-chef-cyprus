# Measurement QA

Run `php artisan measurement:report` (or `--json`) after seeding. It reports missing display/canonical values, low confidence rows, conversion count and missing conversion sources. Data quality scanning also reports household measurement gaps.

Pantry input accepts only active measurement codes (with documented legacy codes retained for existing records). Ingredient-specific conversions create canonical values; when no safe conversion exists, the record remains available as `quantity_unknown` rather than receiving a fabricated value. Expired pantry records are visibly warned about and excluded from matching.

The final Phase 2 checks cover canonical nutrition invalidation, pantry conversion fallback, quantity sufficiency, expired pantry exclusion, API compatibility and member/admin authorization.
