# Unsplash API uyumluluğu

House Chef Cyprus, Unsplash'ı yalnız ikincil editoryal aday kaynağı olarak kullanır. Arama sonuçları otomatik yayınlanmaz; adaylar yönetici incelemesinden geçer.

- Arama, resmî REST `GET /search/photos` endpointine `Authorization: Client-ID` ile gider.
- Yalnız `urls.regular`, `urls.small` ve `urls.thumb` hotlink olarak kullanılır; görseller local/private dosyaya indirilmez.
- Kalıcı insan onayında `links.download_location` yalnız bir kez çağrılır. Arama ve render bu endpointi çağırmaz.
- Fotoğrafçı ve Unsplash bağlantıları `utm_source=house-chef-cyprus&utm_medium=referral` ile gösterilir.
- `X-Ratelimit-Limit`, `X-Ratelimit-Remaining` ve `Retry-After` güvenli biçimde ayrıştırılır. 401, 403, 404, 429 ve 5xx yanıtları kullanıcıya ham gövde olmadan aktarılır.
- Access/secret anahtarları, Authorization header ve ham provider cevabı loglanmaz veya veritabanına kaydedilmez.

Bu uyarlayıcı Laravel HTTP Client kullanır; `unsplash/unsplash` Composer paketi domain koduna yayılmaz. Paket değiştirildiğinde yalnız adaptör etkilenir.

Resmî kurallar: [Unsplash API documentation](https://unsplash.com/documentation), [Unsplash PHP SDK](https://github.com/unsplash/unsplash-php).
