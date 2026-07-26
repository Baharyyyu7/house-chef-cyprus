# Allergen Engine

`ingredient_allergen_details`, malzeme-alerjen ilişkisini `contains`, `may_contain`, `cross_contamination_risk` veya `derived_from` türleriyle saklar. `AllergenAnalysisService` bu kayıtları tarifteki zorunlu ve opsiyonel malzemelerden türetir; tarif seviyesindeki override'lar `recipe_allergens` ile eklenir.

Ekran ve API, kesin içerir ile içerebilir riskini ayırır. Sonuç tıbbi garanti değildir; kullanıcıya bu uyarı her zaman gösterilir.
