# Nutrition Admin Polish Backlog

Nutrition Sprint 3A'nın backend yönetim altyapısı, liste/oluşturma ekranları, toggle endpoint'leri, snapshot ve recalculation ekranları tamamlanmıştır. Aşağıdaki işler kullanıcı değerini artırır ancak çekirdek doğruluk veya güvenlik için bloklayıcı değildir.

| İş | Kullanıcı etkisi | Öncelik | Tetikleyici | Teknik risk | Mevcut temel |
| --- | --- | --- | --- | --- | --- |
| Ingredient–allergen ayrı edit ekranı | Daha ayrıntılı ilişki düzenleme | Orta | Premium Admin UX veya community öncesi polish | Düşük | `NutritionController::saveRelation`, `IngredientAllergenDetail` |
| Recipe override ayrı edit ekranı | Kaynak/doğrulama ayrıntısını rahat güncelleme | Orta | Community öncesi admin polish | Orta; alerjen öncelik kuralı korunmalı | `saveOverride`, `AllergenAnalysisService` |
| Liste toggle butonları | Pasif/aktif işlemlerinin görünür erişimi | Orta | Premium Admin UX | Düşük | `toggleRelation`, `toggleOverride` endpoint'leri |
| Desktop/mobile alt menü polish | Daha hızlı yönetim navigasyonu | Düşük | Production öncesi final admin QA | Düşük | `admin.nutrition.*` route'ları |
| Kapsamlı admin tarayıcı regresyonu | Kullanılabilirlik güveni | Yüksek | Production öncesi final admin QA | Orta; stabil demo veri gerektirir | Mevcut admin sayfaları ve CI kalite kapısı |

Bu işler yalnızca Premium Admin UX fazı, Community öncesi admin polish veya Production öncesi final admin QA aşamalarından birinde ele alınacaktır. Mevcut endpoint ve servisler yeniden tasarlanmayacak, kullanıcı arayüzü bunların üzerine kurulacaktır.
