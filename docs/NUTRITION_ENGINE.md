# Nutrition Engine

Besin hesapları `NutritionService` içinde, malzeme bazlı `nutrition_facts` kayıtlarından yapılır. Değerler decimal string ve BCMath ile işlenir; float kullanılmaz. Güvenli metrik birimler g/kg ve ml/l'dir. Bardak, kaşık ve adet birimleri, malzemeye özel doğrulanmış dönüşüm olmadan hesaplamaya katılmaz.

Sonuç; toplam, porsiyon, veri tamamlanma yüzdesi, güven skoru, kaynak kökeni ve eksik malzemeleri içerir. `verified`, `editorial`, `calculated`, `imported` ve `ai_estimated` kökenleri ayrı tutulur. AI tahminleri doğrulanmış veri değildir.

`recipe_nutrition_snapshots` ve merkezi cache anahtarları tekrar hesaplamayı azaltır. `NutritionService::invalidate()` yönetimsel yeniden hesaplama veya malzeme değişikliği sonrasında çağrılacak sınırdır.

Recipe ingredient, ingredient nutrition fact ve servings değişimleri observer'larla transaction commit sonrasında snapshot/cache invalidation tetikler. `RecalculateRecipeNutritionJob`, aynı tarif için unique lock ve kontrollü retry/backoff ile yeniden hesaplamaya hazırdır.
