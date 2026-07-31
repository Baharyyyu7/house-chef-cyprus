# SEO Queue and Scheduler

Günlük görevler `withoutOverlapping()` ile kayıtlıdır: audit, fırsat yenileme, refresh taslağı, internal link önerileri, sosyal taslaklar ve sabah özeti. Scheduler health kaydı mevcut sistem sağlık görünümünde izlenir.

Görevler otomatik public yayın, X paylaşımı, Search Console içe aktarımı veya IndexNow gönderimi yapmaz. Bu providerlar ayrı explicit yapılandırma, yetkilendirme, kota ve production review gerektirir.
