# Admin Panel Audit

## Browser kanıtı

Admin dashboard ve Data Quality 1024–1440 viewport’ta açıldı. Sidebar grupları, breadcrumb, metric cards, filtreler, empty state, bulk action surface ve responsive table shell görünür durumda.

## P1

- **AUD-001:** Koyu temada dashboard contrast. Screenshot’ta başlık ve 50/100 gibi kritik metricler çok düşük kontrastlı görünmektedir. Renk token/opacity düzeltmesi ve dark-only visual regression gereklidir.

## P2

- **AUD-003/004:** Dashboard “Veri kalitesi 100%” verirken tüm recipe media placeholder, düşük güven conversion ve tahmini nutrition bulunuyor. Yönetici kararlarını yanlış yönlendirme riski.
- Scheduler “henüz veri yok” ve worker “unavailable” durumlarının operasyon önerisi zayıf.

## P3

- Uzun CRUD tablolarda sort state/sticky header standardı route bazında değişken.
- Formlarda destructive/mass action için consistent confirmation/undo/dirty-state matrisi yok.
- Grafik alternatif metin özeti iki kez duyuruluyor; tek erişilebilir özet yeterli.
