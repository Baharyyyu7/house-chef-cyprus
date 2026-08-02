# Release Candidate 1 doğrulama sözleşmesi

RC1, yeni özellik açmak için değil mevcut ürünün yayın öncesi kanıtlarını toplamak içindir. `release-candidate:rc1-audit --json` yalnız veritabanı, route ve mevcut servislerden hesaplanabilen sinyalleri raporlar.

`BLOCKER` ve `CRITICAL` sıfır değilse RC1 hazır değildir. Bu komut insan onayı, gerçek cihaz testi, hosting sağlığı veya Core Web Vitals sonucu üretmez.

## Gerçek dış bağımlılıklar

Production domain/DNS/TLS, SMTP gönderici doğrulaması, dış yedek hedefi, monitoring/analytics, gerçek cihaz ve ekran okuyucu kabul testi yetkili insan tarafından kanıtlanmalıdır.
