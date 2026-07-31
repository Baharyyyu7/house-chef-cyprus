# Final browser QA report (RC)

Bu kayıt, otomatik/HTTP doğrulamasını gerçek kullanıcı kabulünün yerine koymaz.

## Doğrulanan otomasyon yüzeyleri

- Guest public routes, recipe/blog/policy sayfaları, auth routes, sitemap ve API health uçları HTTP/feature testleri ile kapsanır.
- Member ownership, admin authorization, private media ve API public-ID sözleşmeleri feature testlerinde kapsanır.
- Local in-app browser smoke: ana sayfa, Türkçe navigasyon, footer, public recipe card links, branded-fallback alt metinleri ve tema erişilebilir adları incelendi.
- Member'ın `/yonetim` erişimi 403 ile engellendi; hata sayfası Türkçe/noindex ve teknik ayrıntı içermeyen görünür bir geri dönüş sağlar.

## Production öncesi manuel matris

Production URL'de guest/member/admin rolleriyle 320, 375, 390, 430, 768, 820, 1024, 1280, 1440, 1600 ve 1920 viewport; light/dark; keyboard/focus; 200% zoom; 403/404/419/500 güvenli hata sayfaları çalıştırılmalıdır. Gerçek kullanıcı verisi, token, private media veya mail reset URL'si screenshot'a alınmaz.
