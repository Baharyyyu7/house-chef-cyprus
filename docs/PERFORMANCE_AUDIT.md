# Performance Audit

## Kanıt

- Production build klasörü yaklaşık 316 KiB.
- Recipe list/detail controller’ları listelerde dar `select` ve ilgili eager-loading kullanıyor.
- Pagination sınırları public/API/admin yüzeylerinde mevcut.
- Cache/queue/scheduler altyapısı ve performance profile komutu mevcut.

## Riskler

- `DatabaseRecipeSearch` SQL sonuçlarını alıp nutrition/dietary/allergen filtreleri için PHP collection üzerinde filtreliyor. 50 tarifte kabul edilebilir, yüzlerce/binin üzerinde CPU/memory ve N+1-benzeri domain hesap maliyeti yaratabilir.
- Ingredient explorer, filtreli uygun recipe listesini bellekten `keyBy` ile karşılaştırıyor.
- 48 recipe snapshot eksikliği nutrition hesaplama/cache fallback’ini daha sık kullanabilir.
- Placeholder SVG küçük olsa da gerçek medya geldiğinde LCP/image dimensions/lazy loading yeniden ölçülmelidir.

## Öneri

Profil ölçümüyle başlayan ve davranışı değiştirmeyen database-side prefilter/batch hydration fazı; içerik hacmi büyümeden önce.
