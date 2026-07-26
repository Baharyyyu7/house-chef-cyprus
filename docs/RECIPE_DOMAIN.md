# Recipe Domain

Tarif adımları `recipe_steps` tablosunda sıralı ve kaynak tariften bağımsız kullanılabilir
bir çalışma birimi olarak tutulur. Her adımda public ULID, tarif içi benzersiz `position`,
talimat, süre, isteğe bağlı sıcaklık, ekipman ve kullanıcıya gösterilen rehber metinleri vardır.

`RecipeStepService` bütün adım yazmalarını transaction içinde yürütür. Oluşturma sıraya son
pozisyonda ekler; silme sonraki pozisyonları kapatır; yeniden sıralama önce geçici pozisyonlara
taşıyarak benzersiz indeks çakışmasını önler.

Süre saniye bazında saklanır. `duration_seconds` ve `temperature_value` negatif olamaz;
sıcaklık birimi yalnızca `celsius` veya `fahrenheit` olabilir. `timer_required` ve
`is_optional` ayrı davranış alanlarıdır.
