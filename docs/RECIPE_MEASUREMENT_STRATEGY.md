# Recipe Measurement & Household Unit Strategy

## Mevcut durum ve açıklar

`units` tablosu public ID, `code`, `unit_type`, metric flag, precision ve `conversion_factor` taşıyor. `recipe_ingredients` miktar, serbest metin `unit`, `unit_id` ve `grams` alanlarına; pantry ise `unit_id` alanına sahip. `UnitConversionService` yalnız aynı tipte, güvenilir factor bulunan birimleri decimal/BCMath ile dönüştürüyor. Bu, `mg/g/kg` ve `ml/l` için doğru çekirdektir.

Mevcut seed yalnız `g`, `kg`, `ml`, `l`, `adet`, `yk`, `ck` içerir. Household birimlerin factor'ı bilinçli biçimde `null`dır. Ingredient bazlı density/conversion tablosu, display preference, confidence/source/verification ve belirsizlik görünümü yoktur. Bu nedenle household → gram dönüşümünü bugün otomatikleştirmek güvenli değildir.

## Hedef sözleşme

Kullanıcı arayüzü aşağıdaki Türkçe household kodlarını öncelikle gösterir:

`su_bardagi`, `cay_bardagi`, `yemek_kasigi`, `tatli_kasigi`, `cay_kasigi`, `adet`, `paket`, `tutam`, `demet`, `dis`, `kase`, `dilim`, `avuc`, `fiske`, `dal`, `yaprak`.

Her `RecipeIngredient` için iki ayrı kavram saklanır:

1. **Display measurement:** quantity + household unit + Türkçe display text + fraction (`1/2`, `1/4`) + optional/preparation.
2. **Canonical measurement:** grams, millilitres veya count equivalent; conversion confidence, source, verified state ve calculation note.

Nutrition, matching, allergen ve porsiyon ölçekleme yalnız canonical değeri kullanır. Canonical değer yoksa sistem sahte kesinlik üretmez; display miktar korunur, ilgili hesap confidence'i düşer ve kullanıcıya belirsizlik açıklanır.

## Faz 2 veri modeli

Geriye uyumlu migration ile önerilen yapılar:

- `units`: mevcut kodlar; household display metadata ve fraction precision eklenebilir.
- `ingredient_unit_conversions`: `ingredient_id`, `from_unit_id`, `canonical_unit_id`, `canonical_amount`, `density_g_per_ml` (uygunsa), `confidence_score`, `source_name`, `source_url`, `verified_at`, `is_active`, unique ingredient+unit+canonical bağlamı.
- `recipe_ingredients`: `display_quantity`, `display_unit_id`, `display_fraction`, `canonical_quantity`, `canonical_unit_id`, `conversion_confidence`, `conversion_source`, `conversion_status` alanları. Eski `quantity/unit/grams` alanları migration döneminde korunur/backfill edilir.
- `ingredient_portions` yalnız paket/dilim/adet gibi ingredient-specific count eşdeğerleri gerçekten doğrulanmışsa eklenir; evrensel "bir bardak = X gram" varsayımı yapılmaz.

Şema MySQL/PostgreSQL/SQLite uyumlu decimal alanlar ve foreign key'lerle kurulmalıdır. Float kullanılmaz.

## Conversion kuralları

| Dönüşüm | Durum | Kural |
| --- | --- | --- |
| mg ↔ g ↔ kg | Kesin | Merkezi factor ile decimal dönüşüm |
| ml ↔ l | Kesin | Merkezi factor ile decimal dönüşüm |
| adet ↔ adet | Kesin | Aynı count birimi |
| yemek/çay/tatlı kaşığı ↔ ml | Ölçüsel | Sıvı bağlamında unit tanımı ve source gerektirir |
| bardak/kase ↔ ml | Bağlama bağlı | Kap standardı/ülke tanımı ve source gerektirir |
| household ↔ gram | Ingredient bazlı | Sadece doğrulanmış density/portion kaydıyla |
| tutam/demet/dal/yaprak/avuc/fiske ↔ gram | Belirsiz | Otomatik canonical hesap yapılmaz; açık uyarı |

`IngredientDensityResolverInterface` veya eşdeğer resolver, önce exact verified conversion, sonra kullanıcı tanımlı güvenilir conversion, en son "unresolved" sonucunu döndürür. Fallback gram tahmini üretmez.

## Trust ve verification sözleşmesi

Her ingredient-specific dönüşümün kaynak adı/URL'si, confidence, `verified_at`, reviewer ve aktiflik durumu vardır. `verified` dönüşüm ile AI/import tahmini ayrı tutulur. Density resolver yalnız aktif ve doğrulanmış kaydı otomatik hesapta kullanır; açık/ambiguous kayıt kullanıcı arayüzünde belirsizlik olarak görünür.

## UI, admin ve test gereksinimleri

- Tarif detay, pantry ve recipe admin household display'i önceliklendirir; canonical değeri yalnız açıklama/hesap gerektiğinde gösterir.
- Admin ingredient detail: conversion kaynağı, confidence, verified date, active state ve edit history gösterir.
- Porsiyon ölçekleme fraction/precision'ı kontrollü artırır; `0.333333` gibi kullanıcı düşmanı biçimler yerine doğru yuvarlama stratejisi uygular.
- Test: exact conversion, reverse conversion, negative/zero, incompatible types, unresolved household unit, verified density, confidence propagation, scale regression, canonical nutrition/matching regression.

## Kapanış kriteri

Faz 2, tüm household kodları seed edilip görünür olduğunda değil; herhangi bir belirsiz household dönüşümü yanlış gram üretmeden, kaynak/confidence ile doğru yönetildiğinde kapanır.
