# Editoryal Operasyonlar

Phase 4, mevcut Content Factory taslaklarını yayın motoruna dönüştürmez. `editorial_review_batches`, `source_research_queue_items` ve `editorial_review_packs` yalnız insan incelemesi için operasyonel kayıtlar üretir.

- Batch en fazla 50 fact-check konusu taşır; critical/high konular önce gelir.
- Her taslak için paket, bloklayıcı nedenleri ve read-only snapshot saklar.
- Bölüm kararları audit edilir; `published`, `verified` veya `scheduled` alanlarını değiştiremez.
- Final insan onayı ayrı bir yetki kapısıdır; yayın ayrı bir açık admin aksiyonudur.

Yenileme: `php artisan content:editorial-operations-plan --json`.
