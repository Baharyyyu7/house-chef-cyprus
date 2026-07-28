# Admin Analytics Implementation

`app/Services/AdminDashboardService.php` dashboard veri sözleşmesinin tek girişidir. Servis period doğrular, aggregate sorguları yapar, `admin.dashboard.v3.{period}` anahtarıyla kısa TTL cache kullanır ve yalnız view için güvenli dizi/DTO-benzeri değerler döndürür.

Tarif trendi `recipes.created_at`, mutfak dağılımı recipe/cuisine aggregate'i, doğrulama dağılımı `verification_status` üzerinden gelir. Event geçmişi veya zaman serisi olmayan kullanıcı, cooking, matching ve request trendleri grafik olarak uydurulmaz; mevcut observability özeti sayısal empty-state davranışıyla sunulur.

Cache invalidation gerektiren dashboard değişiklikleri en fazla üç dakika içinde görünür; sonraki operasyonel cache invalidation katmanı bunu daha erken yenileyebilir.
