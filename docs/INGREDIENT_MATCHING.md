# Ingredient Matching

`IngredientMatchingService` zorunlu malzemeleri ana skor olarak kullanır; opsiyonel
malzemeler exact oranı düşürmez. Sonuç exact/effective oranları, eksik malzemeler,
alternatifler ve açıklanabilir durum bilgisi içerir.

Sıralama, tam yapılabilirliğe, substitution ihtiyacına, effective uyuma, eksik sayısına ve
toplam tarif süresine göre tek serviste hesaplanır. Hariç tutulan malzeme içeren tarifler
sonuçtan çıkarılır.
