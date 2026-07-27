# Database Index Strategy

İndeksler gerçek erişim kalıplarına göre, SQLite/MySQL/PostgreSQL uyumlu Laravel
migration'ı ile eklenmiştir.

- Published tarif listeleme/featured sıralama ve cuisine/category/difficulty
  filtreleri
- Recipe ingredient ve aktif substitution lookup'ları
- Kullanıcı pantry uygunluk/süre sonu sorgusu
- Nutrition fact ve aktif snapshot lookup'ları
- Aktif ingredient allergen ilişkileri
- Açık Data Quality issue lookup'ları
- Database queue reservation, failed job tarihi ve kullanıcı audit geçmişi

Slug, public ID ve iş doğruluğu gerektiren ilişkilerdeki mevcut unique
constraint'ler korunur; aynı amacı taşıyan yeni unique/index tekrarları
oluşturulmaz. Büyük kataloglarda EXPLAIN/ANALYZE ile gözlem yapmadan ek indeks
eklenmez.
