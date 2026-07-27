# Portion scaling policy

Recipe ingredient rows declare `scalable`, `limited_scaling`, `non_scalable`, `taste_adjusted`, or `manual_review`. Canonical decimals are scaled precisely; presentation values are localized independently. Salt, spice and taste-sensitive items should use `taste_adjusted` with a visible note.

## Phase 2 closure

Ingredient groups are ordered separately from their lines. Public recipe presentation uses the group order and the line sort order, while legacy rows without a group safely fall back to **Malzemeler**. Empty groups do not produce public ingredient output.
