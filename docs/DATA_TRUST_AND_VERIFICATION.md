# Data Trust and Verification

## Amaç

Tarif, malzeme, ölçü, besin, alerjen, görsel ve kaynak verisi için ne bilindiğini, neyin belirsiz olduğunu ve kim tarafından ne zaman gözden geçirildiğini görünür kılmak.

## Gelecek trust kaydı

Kaynaklı alanlar için: `source`, `source_url`, `source_type`, `collected_at`, `reviewed_at`, `reviewed_by`, `verification_status`, `confidence`, `license`, `notes` ve `last_checked_at` saklanır. Teknik kaynak URL'leri kullanıcıya güvenli özet olarak gösterilir; token, ham payload veya hassas not görünmez.

## Tarif yaşam döngüsü

`draft`, `researched`, `verified`, `published`, `needs_review`, `deprecated`. AI-assisted ya da imported köken açıkça işaretlenir; verified statüsü yalnız insan/editor kontrolüyle verilir.

## Doğrulama akışı

1. Kaynak ve lisans kaydedilir.
2. Başlık, ölçü, süre, porsiyon, malzeme, alerjen/diyet, besin ve görsel iddiaları kontrol edilir.
3. Belirsiz household dönüşümü canonical değer üretmez.
4. Reviewer karar, not ve yeniden kontrol zamanı ekler.
5. Data Quality açıklarını çözmeden yüksek güven iddiası yapılmaz.

## Genişletilecek kalite kontrolleri

Kaynağı/görsel lisansı/household canonical değeri olmayan kayıtlar, duplicate/tekrarlı açıklama, eksik adım-süre-porsiyon, stale verification, çelişkili besin-alerjen sonucu, broken source URL ve belirsiz mutfak/ikon eşlemesi taranacaktır. İlk uygulama, admin doğrulama kuyruğu ve bulk review ile birlikte gelir; bu belgede henüz migration veya ekran yoktur.
# Household conversion provenance

Household conversion records require a source type and source name. Demo seed conversions are marked `seed_demo` with low confidence; they are estimates, not verified facts.

## Phase 2 quality guardrails

Data Quality reports recipe lines that are missing a managed ingredient group and preserves an explicit legacy fallback. Canonical conversion completeness, confidence and source remain independently reportable; expiry prevents pantry stock from being treated as available by matching.
