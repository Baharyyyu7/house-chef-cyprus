# Household measurement reference

The active Turkish taxonomy lives in `units`. User-facing codes include `su_bardagi`, `cay_bardagi`, `yemek_kasigi`, `cay_kasigi`, `adet`, `dis`, `tutam`, `paket` and the complete Phase 2 seed list. Metric legacy codes remain non-visible for compatibility. Fractions are formatted in Turkish; canonical values are never rounded for calculation.

## Phase 2 closure

Only active, user-visible household units are selectable in new pantry and recipe ingredient forms. Existing legacy metric codes remain readable so historical pantry records do not become invalid. Ingredient-specific conversion is resolved only on submission; the stored canonical result includes its confidence and is never presented as verified unless its source supports that claim.
