# Admin Dashboard V3

Dashboard, `AdminDashboardService` üzerinden period parametresiyle (`1`, `7`, `30`, `90`) çalışır. Sonuç üç dakika cache'lenir ve raw modeller view'a geçirilmez.

## Gerçek veri ilkesi

Kartlar tarif, kullanıcı, malzeme, nutrition snapshot, Data Quality, queue, scheduler, backup, media ve observability kaynaklarını kullanır. Mevcut veri yoksa “Veri henüz oluşmadı” görünür; demo grafik veya sabit trend üretilmez.

## Dashboard alanları

- Katalog, yayın, doğrulama, inceleme, kullanıcı ve malzeme metrikleri.
- Data Quality, nutrition ve alerjen kapsamı.
- Tarif oluşturma trendi, mutfak dağılımı ve doğrulama dağılımı.
- Sistem, queue, scheduler, backup, medya ve istek özeti.
- Yetkili gerçek route'lu hızlı aksiyonlar, güvenli audit etkinlikleri ve mevcut dikkat kayıtları.

Aktivite alanı metadata, internal ID, token veya raw payload göstermez. Member kullanıcı admin grubuna erişemez.
