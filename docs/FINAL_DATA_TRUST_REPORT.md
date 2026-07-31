# Final data trust report (RC)

## Katalog durumu

Seed sözleşmesi 50 `published` ve `verified` tarifi, household/canonical ölçüleri, malzeme grupları, adımlar, kaynak/provenance ve branded fallback ilişkileriyle idempotent olarak kurar. 20 public blog yazısı ayrı production catalogue seeder ile kurulur. Production seed'e QA kullanıcıları, demo parolaları veya credentiallar girmez.

## Dürüstlük sınırları

- Branded fallback, özgün lisanslı marka varlığıdır; **gerçek yemek fotoğrafı değildir**.
- Ingredient nutrition ve bazı household dönüşüm kayıtlarının provenance/confidence seviyesi Data Quality tarafından açık warning olarak tutulur; bunlar doğrulanmış sağlık iddiası değildir.
- Dış recipe/blog metni, görsel veya kaynak lisans/provenance incelemesi olmadan public catalogue'a eklenmez.
- İnsan editoryal karar gerektiren candidate media, düşük güvenli nutrition veya conversion kayıtları otomatik `verified` yapılmaz.

## RC kararı

Katalog yapısal olarak tekrar kurulabilir ve public liste/API yalnız published+verified tarifleri döndürür. Gerçek yemek fotoğrafı seçimi ve bağımsız nutrition/conversion source doğrulaması, production launch öncesi editoryal operasyonların açık işidir.
