# Admin Nutrition Management

`/yonetim/beslenme` yalnızca admin kullanıcıları için besin öğeleri, alerjenler, diyet etiketleri, malzeme nutrition kayıtlarının özeti ve tarif snapshot işlemlerini sunar. Controller ayrıca admin yetkisini doğrular; route middleware tek güvenlik katmanı değildir.

Doğrulanmış nutrition fact için kaynak adı ve doğrulama tarihi gerekir. AI tahmini kökeni ayrı saklanır. Manuel invalidate ve yeniden hesaplama işlemleri audit log'a yazılır; yeniden hesaplama `RecalculateRecipeNutritionJob` ile kuyruğa uygundur.

Kapanış altyapısı `ingredient_allergen_details` için kaynak/doğrulama bilgisi, tarif override kayıtları ve `nutrition_settings` eşiklerini saklar. Recalculation run kayıtları güvenli hata kodu ile ayrı tutulur; başarısız job eski snapshot'ı değiştirmez.
