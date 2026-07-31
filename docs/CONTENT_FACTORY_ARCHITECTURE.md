# Controlled Content Factory

Content Factory mevcut doğrulanmış kataloğu genişletmek için **draft-only** bir üretim ve insan inceleme katmanıdır. `content_factory_batches` 18×25 tarif ve 18×10 blog planını; `content_factory_reviews` teknik, özgünlük ve fact-check kapılarını; `content_topic_clusters` ise 25 konu kümesini saklar.

`ContentFactoryService` deterministik, idempotent seed üretir. Yeni kayıtların `status=draft`, tarif doğrulamasının `needs_review` ve `fact_check_required=true` olması zorunludur. Otomatik üretim hiçbir zaman `verified`, `human_approved`, `scheduled` veya `published` yazmaz.

Public web, API ve sitemap mevcut yayın sorgularını kullanır; dolayısıyla taslaklar görünmez. İnsan onayı admin arayüzündeki açık aksiyonla kayda geçer, kullanıcı kimliği audit log'a yazılır ve içerik yine taslak kalır. Ayrı yayın akışı gereklidir.

## Operasyon

`php artisan content:factory-seed --type=recipe|blog|all`, `content:factory-audit --json` ve `content:factory-morning-brief --json` ağ çağrısı yapmaz. `CONTENT_AUTO_PUBLISH` yapılandırması sabit biçimde `false` kabul edilir.
