# Admin Nutrition Management

`/yonetim/beslenme` yalnızca admin kullanıcıları için besin öğeleri, alerjenler, diyet etiketleri, malzeme nutrition kayıtlarının özeti ve tarif snapshot işlemlerini sunar. Controller ayrıca admin yetkisini doğrular; route middleware tek güvenlik katmanı değildir.

Doğrulanmış nutrition fact için kaynak adı ve doğrulama tarihi gerekir. AI tahmini kökeni ayrı saklanır. Manuel invalidate ve yeniden hesaplama işlemleri audit log'a yazılır; yeniden hesaplama `RecalculateRecipeNutritionJob` ile kuyruğa uygundur.
