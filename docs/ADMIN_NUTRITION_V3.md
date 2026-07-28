# Admin Nutrition V3

Nutrition, nutrient, alerjen, diyet eşiği, recipe snapshot ve recalculation geçmişi mevcut `NutritionController` üzerinden yönetilir. Doğrulanmış nutrition için kaynak ve doğrulama tarihi zorunludur; AI tahmini verified olarak gösterilmez.

Bu faz yeni hesaplama kuralı eklemez; mevcut doğrulama, audit ve queue sözleşmesini korur.

V3 görünümü; filtrelenebilir ingredient nutrition fact listesini, nutrient sözlüğünü, alerjenleri, ingredient–allergen ilişkilerini, recipe override kayıtlarını, merkezi diyet eşiklerini, snapshot detayını ve yeniden hesaplama geçmişini aynı güvenli yönetim yüzeyinde toplar. Raw job payload, internal yol ve hassas token gösterilmez.
