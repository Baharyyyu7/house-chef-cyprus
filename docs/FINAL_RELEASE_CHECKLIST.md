# V1.0 Release Candidate iç kontrol listesi

## Stok medya uyumluluğu

- [ ] Unsplash medyası yalnız insan onayından sonra, izinli CDN hotlink’i ve görünür fotoğrafçı/Unsplash attribution ile kullanılmalıdır.
- [ ] Download tracking yalnız final bağlama anında bir kez kaydedilmelidir; arama ve render bunu tetiklemez.

Bu kontrol listesi yalnız `visual-redesign-v2` dalındaki kod hazırlığını tanımlar; production yayını, DNS veya ücretli görsel üretimi başlatmaz.

## Kod ve katalog

- [x] 50 tarif `published` ve `verified` olarak idempotent seed edilir.
- [x] Her tarifte provenance, household/canonical ölçü, nutrition, alerjen ve diyet verisi bulunur.
- [x] Public liste ve API yalnız yayınlanmış/doğrulanmış tarifleri verir.
- [x] Branded fallback zinciri boş veya kırık görsel bırakmaz.
- [x] Private-before-public medya inceleme, attribution, allowlist, MIME ve checksum kontrolleri uygulanır.

## Provider durumu

- [x] Pexels sunucu tarafı provider, güvenli hata kodları, rate-limit haritalaması ve private indirme akışı hazırdır.
- [ ] Canlı Pexels anahtarı doğrulandı. Yerel güvenli deneme `PEXELS_INVALID_KEY` döndürdü; aday taraması başlatılmadı.
- [x] Eden AI adapter/bütçe koruması hazırdır; ücretli üretim kapalıdır.

## Public yüzey ve SEO

- [x] Blog, bilgi, gizlilik ve politika sayfaları çalışır.
- [x] Dynamic metadata, canonical, Open Graph/X Card, robots, sitemap index ve page/recipe/blog/image sitemap’leri vardır.
- [x] Recipe, Article, FAQ, Organization ve WebSite JSON-LD sağlanır; sahte rating eklenmez.
- [x] Yönetim, kimlik doğrulama ve üye alanları noindex’tir.

## Production geçişi (dış erişim gerektirir)

- [ ] Hosting, production database/cache/queue, domain-DNS ve TLS.
- [ ] Production mail sağlayıcı credential ve doğrulanmış gönderici.
- [ ] Production `.env`, off-server backup destination ve gözlemleme DSN’leri.

Bu maddeler sağlanmadan production deploy yapılmaz. Main merge ve release tag bu RC kapanışında yapılmaz.
