# Pantry Engine

Pantry kayıtları kullanıcıya özeldir. Eşleştirme yalnızca `is_available=true` olan ve son
kullanma tarihi geçmemiş kayıtları kullanır. Aynı malzeme için mevcut `updateOrCreate` akışı
güvenli toplulaştırma davranışını korur; ileride lot bazlı stok takibi gerektiğinde ayrı kayıt
stratejisine geçilir.
