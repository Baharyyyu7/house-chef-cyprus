# Measurement QA

Run `php artisan measurement:report` (or `--json`) after seeding. It reports missing display/canonical values, low confidence rows, conversion count and missing conversion sources. Data quality scanning also reports household measurement gaps.

The final Phase 2 checks cover canonical nutrition invalidation, pantry conversion fallback, quantity sufficiency, expired pantry exclusion, API compatibility and member/admin authorization.
