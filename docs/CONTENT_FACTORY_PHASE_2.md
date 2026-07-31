# Content Factory Phase 2

Phase 2, mevcut 450 tarif ve 180 blog taslağını yayın durumuna dokunmadan teknik ve editoryal incelemeye hazırlar. Mevcut 50 doğrulanmış/yayınlanmış tarif ile 20 yayınlanmış blog korunur.

- Otomatik süreç yalnızca `draft`, `needs_review` ve `ready_for_human_review` durumlarını yazar.
- `verified`, `human_approved`, `scheduled` ve `published` durumları otomasyon tarafından atanmaz.
- Her taslak için okunabilirlik, teknik kontrol, SEO ve özgünlük sinyalleri kaydedilir.
- Besin, alerjen, ölçü dönüşümü, saklama, kaynak ve medya konuları `content_factory_issues` içinde insan incelemesine bırakılır.

Komut: `php artisan content:factory-enrich --type=all --json`.

