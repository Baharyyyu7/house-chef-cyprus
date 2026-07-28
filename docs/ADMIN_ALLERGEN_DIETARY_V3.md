# Admin Allergen & Dietary V3

Yönetim paneli `contains`, `may_contain`, `cross_contamination_risk` ve `derived_from` ilişkilerini ayrı anlamlarla gösterir. Tarif override kaydı türetilmiş alerjen sonucuna göre önceliklidir; kaynağı, güveni ve etkinlik durumu izlenir.

Diyet eşikleri merkezi nutrition ayarlarından okunur. Her eşik, hesaplanan dietary compatibility sonuçlarını etkiler; değişiklik sonrasında ilgili tarifleri yeniden hesaplama işlemi uygulanmalıdır. Negatif eşik değerleri doğrulama katmanında reddedilir.
