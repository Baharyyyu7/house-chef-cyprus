# Batch 1 approval matrix

| Area | Items | Current disposition | Required human role | Blocker |
| --- | ---: | --- | --- | --- |
| Recipe identity/formulation | 10 | `NEEDS_FACT_CHECK` or reconciliation | content editor + fact-check reviewer where safety claims exist | 8 recipes have title/ingredient identity mismatch; 2 require method/measurement validation. |
| Recipe media | 10 | No candidate approved | media reviewer | All 10 lack approved real media; 8 are `DO_NOT_SOURCE` until identity is reconciled. |
| Allergen/nutrition | 10 | No final result approved | fact-check reviewer | Final ingredients are prerequisite. |
| High-risk blogs | 4 | `NEEDS_FACT_CHECK` | fact-check reviewer | Authoritative/source-jurisdiction review required. |
| Cyprus guide | 1 | `READY_FOR_EDITOR_REVIEW` | content editor | Cite each historical/cultural claim before final approval. |

## State transition rule

`private packet → revision proposal → human review → authorized approval →
separate publication decision`

No transition in this packet changes a public record. `READY_FOR_EDITOR_REVIEW`
is deliberately narrower than `verified`, `published`, or `approved media`.
