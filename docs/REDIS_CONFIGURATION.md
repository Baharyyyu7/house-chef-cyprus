# Redis yapılandırması

Production'da cache, queue, session, lock ve rate-limit için Redis kullanılır. `CACHE_STORE`, `QUEUE_CONNECTION` ve `SESSION_DRIVER` array/sync olmamalıdır.

- Redis'i private ağda veya localhost'ta dinletin; public porta açmayın.
- Parola/TLS bilgileri yalnız sunucu `.env` dosyasında tutulur.
- Ayrı prefix kullanın; staging ve production aynı Redis'i paylaşacaksa prefix zorunludur.
- Queue worker, scheduler overlap lock ve cache invalidation Redis ile çalışır.
- Redis kesintisinde uygulama health'i degraded kabul edilmeli; önce bağlantı, sonra worker/scheduler kontrol edilmelidir.

Lokal file/database fallback yalnız geliştirme içindir; production performans varsayımı değildir.
