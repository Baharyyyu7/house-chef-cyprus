# Admin Ingredient V3

Malzeme listesi kategori ve durum filtreleri ile alias, alternatif, dönüşüm, nutrition ve alerjen kapsamını özetler. 360° detail alanı yeni veri uydurmaz; mevcut ilişkileri ve ilgili yönetim ekranlarına güvenli bağları görünür kılar.

Alias normalizasyonu `IngredientAliasService` içinde kalır. Alternatif ilişkiler yönlüdür; aynı malzemeye self-reference engellenir.
