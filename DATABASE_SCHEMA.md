# Database Schema

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
