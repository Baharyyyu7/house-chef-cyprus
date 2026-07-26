# Dietary Compatibility

`DietaryCompatibilityService` sonuçları `compatible`, `incompatible`, `uncertain`, `manually_verified` veya `ai_estimated` olabilir. Belirsiz bilgi uygun kabul edilmez. Protein ve kalori eşikleri `config/nutrition.php` içinde merkezidir. Gluten, süt ve helal gibi hassas iddialar için doğrulanmış etiket veya kaynak gerekir.

Yönetimsel eşik değişiklikleri `nutrition_settings` ile izlenir; varsayılanlar config dosyasında kalır ve geçersiz/negatif değer kabul edilmez.
