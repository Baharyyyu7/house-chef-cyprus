# Responsive Audit

Temsilî browser ölçümleri:

| Sayfa | Viewport | Yatay taşma | Sonuç |
|---|---:|---:|---|
| Home | 375 | Yok | Mobil header/drawer kontrolü görünür. |
| Recipe detail | 375 | Yok | Hero, badge, household panel taşmıyor. |
| Pantry | 1024 | Yok | Kart/empty state uygun. |
| Admin dashboard | 1440 | Yok | Shell çalışıyor; contrast sorunu ayrı. |
| Data Quality | 1024 | Yok | Filter/table shell mevcut. |

Eksik kanıt: 320, 390, 430, 768, 820, 1280, 1600, 1920, 200% zoom, açık tablolarda yatay scroll keyboard erişimi. Bunlar P3 görsel regression matrisi olarak planlanmalıdır.
