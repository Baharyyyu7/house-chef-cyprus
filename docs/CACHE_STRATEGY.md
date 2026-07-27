# Cache Strategy

Cache anahtarları `CacheKeyFactory` üzerinden üretilir; domain değişiklikleri
`CacheInvalidationService` ve commit-sonrası observer akışıyla geçersiz kılınır.
Tarif/nutrition, lookup ve warmup alanları controller veya view içinde dağınık
anahtar üretmez.

`php artisan cache:warm` tarif, nutrition ve lookup alanlarını ısıtır;
`--section=recipes|nutrition|lookups` güvenli dar kapsam sağlar. Lokal database/
file cache ile çalışan sözleşme, Redis'e geçişte domain kodunu değiştirmez.

Kullanıcıya özel cache anahtarları public kullanıcı bağlamını kullanır; pantry,
favori ve cooking session verisi kullanıcılar arasında paylaşılmaz.
