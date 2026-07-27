# Database Schema

## Recipe ingredient groups

`recipe_ingredient_groups` stores a public ULID, recipe ownership, trimmed display name and stable sort order. `recipe_ingredients.recipe_ingredient_group_id` is nullable for backwards-compatible migration, references the group with `nullOnDelete`, and is indexed with ingredient sort order. Application deletion moves lines to the recipe-local `Malzemeler` fallback before deleting a non-default group.

## Nutrition and allergens

- `nutrients`: Public ULID ile besin tanımı, birim, hassasiyet, makro/mikro türü ve sıralama.
- `nutrition_facts`: Ingredient veya gelecekte recipe için basis, source, origin, confidence ve doğrulama zamanı taşıyan polymorphic fact kayıtları.
- `recipe_nutrition_snapshots`: Porsiyon bazlı hesaplanmış payload, tamamlanma/güven skoru ve invalidation zamanı.
- `ingredient_allergen_details`: Malzeme alerjen ilişkisi, risk türü, doğrulama ve kaynak bilgisi.
- `nutrition_settings`: Yönetilebilir dietary eşikleri için merkezi decimal değerler.
- `nutrition_recalculation_runs`: Snapshot yeniden hesaplama tetikleme, durum ve güvenli hata geçmişi.

## Recipe execution

- `recipe_steps`: Tarif adımları; `public_id`, `recipe_id`, benzersiz `position`, süre,
  sıcaklık, timer/optional işaretleri, ekipman ve rehber metinleri.
- `cooking_sessions`: Kullanıcının tarif oturumu, aktif adım, porsiyon anlık görüntüsü ve
  yaşam döngüsü zamanları.
- `cooking_session_steps`: Tamamlanan adım kayıtları; session/adım çifti benzersizdir.
- `cooking_timers`: Oturum içi çoklu zamanlayıcı, süre, kalan süre ve durum zamanları.

Tarif adım pozisyonu aynı tarifte benzersizdir. Tüm execution tabloları foreign key ve uygun
indexlerle kullanıcı/tarif oturumunu güvenli biçimde bağlar.

## Ingredient matching

- `ingredient_aliases`: Türkçe alias ve normalize eşleşme değeri.
- `ingredient_substitutions`: Yönlü alternatif, ratio, güven ve doğrulama bilgisi.
- `recipe_ingredients`: Zorunluluk, grup, hazırlık notu ve substitution uygunluğu.
- `pantry_items`: Birim, saklama yeri, açılma tarihi, not ve kullanılabilirlik.
# Queue operasyon tabloları

- `jobs`: database queue için bekleyen/reserved işler; queue adı, güvenli
  framework payload'ı, deneme ve reservation zamanlarını taşır.
- `failed_jobs`: Laravel'in başarısız iş kaydıdır. Yönetim arayüzü raw `payload`
  ve `exception` alanlarını göstermez; yalnızca güvenli türetilmiş özet kullanır.
- `audit_logs`: failed job retry ve forget aksiyonları dahil yönetim aksiyonlarının
  iz kaydını tutar.

# Performans indeksleri

`2026_07_27_080000_add_performance_indexes` migration'ı; published tarif
listeleme/filtreleme, ingredient matching, pantry uygunluk, nutrition snapshot,
Data Quality açık issue, database queue reservation, failed job geçmişi ve audit
geçmişindeki gerçek sorgu kalıpları için bileşik veya hedefli indeksleri ekler.
Mevcut unique constraint'ler tekrarlanmaz.
# Media operations fields

`media` includes a public ULID, visibility, alt text, dimensions, processing/thumbnail statuses, thumbnail path JSON, safe processing error fields and processing timestamp. `mediable` remains a polymorphic relation to Recipe and Ingredient (and future content types).
# Foundation E notu

SQLite şeması Laravel migration’larıyla oluşturulur; temiz kurulum kalite kapısı migration/seed ve ikinci seed çalıştırır. Ayrıntılı taşınabilirlik denetimi için [database portability](docs/DATABASE_PORTABILITY.md) belgesine bakın.

Foundation A–E kapatıldı; production hedef veritabanı seçildiğinde MySQL/PostgreSQL üzerinde ayrı staging migration denemesi zorunludur.

## Editorial content trust

- `content_sources`: public ULID, entity public identity, source/lisans/dil/ülke, erişim-inceleme zamanı, confidence ve lifecycle metadata'sı.
- `recipe_provenances`: recipe ile kaynak arasındaki alan-bazlı ilişki, source hash, verification ve review metadata'sı.
- `recipes`: verification/editorial/completeness alanları ile equipment, tips, storage, serving, reheating, freezing, seasonality ve image trust alanları.
- `ingredients`: düşük-riskli pilot için `icon_key`, `icon_label` ve `icon_status` metadata'sı.
# Household measurement additions

`ingredient_unit_conversions` holds sourced ingredient-specific conversions. `recipe_ingredients` has additive display and canonical columns; legacy quantity, unit and grams remain supported.
