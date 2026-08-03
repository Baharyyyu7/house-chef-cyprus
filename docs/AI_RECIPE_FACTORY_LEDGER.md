# AI Recipe Factory ledger

## Güvenlik durumu

Bu ledger, Recipe Factory'nin yalnız kontrollü taslak, denetim ve insan onayı akışında ilerlemesini kaydeder. Hiçbir reconciliation işlemi tarifleri silmez, birleştirmez, doğrulamaz veya yayınlamaz.

| Task | Kapsam | Durum | Üretim etkisi | Sonraki güvenli eylem |
| --- | --- | --- | --- | --- |
| RF-001 | Katalog reconciliation | `AUDITED` altyapısı eklendi | Yok; varsayılan salt-okunur | `recipe-factory:reconcile --json` ile kanıt üret |
| RF-002 | Reversible review evidence | `IDENTITY_REVIEW` hazırlığı | Yalnız `--write` ile private review bulgusu | Editör duplicate/identity bulgularını inceler |
| RF-003 | AI provider discovery | `BLOCKED_PROVIDER` | Yok | Bağlı/sağlıklı sağlayıcı ve bütçe sahibi tarafından doğrulansın |
| RF-004 | Yeni fikir/draft üretimi | `BLOCKED_EDITORIAL` | Yok | Gap + duplicate kararından sonra en fazla 25 fikir seç |
| RF-005 | Publication | `BLOCKED_MEDIA` | Yok | Onaylı medya, safety, SEO ve insan onayı olmadan yayın yok |

## Reconciliation sözleşmesi

`recipe-factory:reconcile` şunları üretir:

- title, ingredient ve instruction fingerprint'leri;
- exact duplicate ve possible near-duplicate family kanıtı;
- başlık–malzeme identity mismatch işareti;
- canonical ölçü kapsamı;
- food-safety/fact-check gereksinimi;
- kesin açıklanabilir sınıflandırma (`KEEP`, `INCOMPLETE`, `IDENTITY_MISMATCH`, `DUPLICATE`, `NEAR_DUPLICATE`).

Varsayılan komut salt-okunurdur. `--write` yalnız `content_factory_reviews` içinde `recipe_factory_reconciliation` aşamasına idempotent bulgu kaydı yazar. Tarif statüsü, verification statüsü, medya, SEO metadata veya public görünürlük değişmez.

## Meşru sayı kuralı

`LEGITIMATE_RECIPE_COUNT` yalnız published + verified, complete, identity mismatch olmayan ve duplicate bulgusu olmayan tarifleri sayar. Bu teknik sayı insan editoryal/safety kararının yerine geçmez. `NEW_RECIPES_REQUIRED = max(0, 1000 - LEGITIMATE_RECIPE_COUNT)` yalnız planlama değeridir; otomatik üretim emri değildir.

## Production blocker'ları

- Gerçek production SHA/release, disk, inode, queue, backup ve canlı provider sağlığı için SSH/production erişimi gerekir.
- AI provider, bütçe ve model doğrulanmadan hiçbir AI draft çağrısı yapılmaz.
- High-risk güvenlik, alerjen, kültürel kimlik ve duplicate merge kararları insan onayı bekler.
- Onaylı medya olmadan media-required tarifler yayınlanmaz.
