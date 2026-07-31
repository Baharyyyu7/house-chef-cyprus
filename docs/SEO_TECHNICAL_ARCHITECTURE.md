# SEO Technical Architecture

- Sitemap index: pages, verified recipes, published blog ve public image sitemap.
- Private/auth/admin sayfaları `noindex,nofollow` alır.
- Canonical URL metadata servisi tarafından oluşturulur; override yalnız editoryal kayıtla yapılır.
- Recipe/Article JSON-LD gerçek içerik alanlarından türetilir. Yorum, puan veya görsel yoksa bu alanlar uydurulmaz.
- `seo:audit` fingerprint ile idempotent issue üretir; ilk tespit korunur, son tespit güncellenir ve artık bulunmayan sorun çözümlenir.

Kategori ve etiket sayfaları henüz stratejik indexable landing page değildir; bu nedenle ayrı category sitemap üretilmez. Gerçek indexable sayfalar tasarlandığında eklenir.
