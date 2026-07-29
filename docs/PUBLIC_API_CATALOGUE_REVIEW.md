# Public APIs Catalogue Review — 29 Temmuz 2026

Bu inceleme `public-apis` kataloğunu yalnız aday keşfi için kullandı. Katalog uygulamaya eklenmedi, API anahtarı oluşturulmadı ve hiçbir entegrasyon etkinleştirilmedi. Bulgular yalnız resmî sağlayıcı belgeleriyle doğrulandı.

## A — Şimdi entegre edilmemeli, ancak production hazırlığında seçilebilir

### Pexels API — görsel aday/fallback

- Resmî dokümantasyon: <https://www.pexels.com/api/documentation/>
- Senaryo: doğrulanmış tarif için private review’a alınacak fotoğraf adayları; asla otomatik tarif doğrulaması veya public hotlink değil.
- Auth: API key. Ücretsiz kota: 200 istek/saat ve 20.000 istek/ay; 24 saat cache, quota header takibi ve 429 backoff zorunlu.
- Lisans/attribution: belirgin Pexels bağlantısı, mümkünse fotoğrafçı kredisi; stock kütüphanesi işlevi kopyalanamaz. Görsel kaynak/attribution metadata’sı saklanmalı.
- Karar: **production sonrasında, admin review modülü ile**. Mevcut Eden AI/Pexels yapılandırmasıyla çakışmaz; Pexels yalnız fallback/candidate rolündedir.

### Cloudflare origin headers — yaklaşık ülke, isteğe bağlı şehir

- Resmî dokümantasyon: <https://developers.cloudflare.com/network/ip-geolocation/>
- Senaryo: yalnız trusted Cloudflare proxy arkasında ülke tabanlı analitik, timezone ve şüpheli giriş bağlamı.
- Auth/kota: ayrı bir API çağrısı değil; proxied domain’e gelen header. Free planda ülke header’ı mevcut.
- Gizlilik: varsayılan yalnız ülke; şehir/timezone opt-in analytics bağlamında, raw IP gösterilmeden; VPN/proxy belirsizliği belirtilmeli.
- Karar: **domain + Cloudflare kurulunca**. Uygulamada trusted proxy tanımlanmadan header kabul edilmez.

### Resend — transactional e-posta

- Resmî dokümantasyon: <https://resend.com/docs/knowledge-base/what-is-resend-pricing>
- Senaryo: doğrulama, parola sıfırlama ve güvenlik bildirimi e-postaları.
- Auth: API key; ücretsiz planın güncel limiti aylık 3.000 transactional e-postadır. Domain doğrulaması ve DPA/KVKK-GDPR değerlendirmesi gerekir.
- Karar: **production domain aşamasında**; mevcut log mailer’ın yerine ancak SPF/DKIM ve gerçek gönderim testinden sonra geçer.

## B — Production sonrasına bırakılmalı

### Open Food Facts — barcode/product enrichment

- Resmî dokümantasyon: <https://openfoodfacts.github.io/documentation/docs/Product-Opener/api/>
- Senaryo: kullanıcı barkoduyla paketli ürün için ürün etiketi, besin ve alerjen verisini aday olarak getirmek.
- Auth/kota: read çağrıları custom User-Agent ile auth’suz; ürün read 15/dk/IP, search 10/dk/IP. Cache ve queue şarttır.
- Lisans/risk: ODbL database, DBCL içerik, CC BY-SA görseller; veri gönüllü katkılıdır ve doğruluk/eksiksizlik garantisi verilmez.
- Karar: **yalnız opt-in barcode özelliği**. Veri `imported/unverified` olarak kalmalı; sağlık iddiası, otomatik verified nutrition veya toplu import yapılmamalı.

### DeepL API — gelecek yerelleştirme

- Resmî dokümantasyon: <https://developers.deepl.com/docs/getting-started/your-first-api-request>
- Senaryo: ileride admin destekli taslak çeviri; public içerikte otomatik yayın yok.
- Karar: Çoklu dil roadmap’ine ertelendi; mevcut aktif içerik dili Türkçe.

### Better Stack Uptime — dış uptime monitorü

- Resmî site: <https://betterstack.com/uptime>
- Senaryo: `/health` ve `/api/v1/health` dışarıdan izleme.
- Karar: domain/staging açıldığında değerlendirilir; bağımsız monitoring, mevcut uygulama health endpoint’ini tamamlar.

## C — Yalnız araştırma için

- Eden AI: mevcut config adayıdır fakat bu incelemede resmi image-model capability, kredi ve güncel fiyat satırı doğrulanmadı. API key/configured olması görsel üretim yetkisi değildir.
- Gemini, BFL, Stability: mevcut abstraction altında kalır; ücret, lisans, model erişimi ve bütçe onayı olmadan üretim veya fallback seçilmez.

## D — Kullanılmamalı

- Tarayıcı otomasyonu/izinsiz scraping ile tarif veya görsel toplama.
- Belirsiz lisanslı model/checkpoint, başka tarif sitesinden metin ya da görsel kopyalama.
- Open Food Facts görüntülerini House Chef medya kütüphanesine toplu kopyalama.
- Public API kataloglarını runtime bağımlılığı veya paket olarak ekleme.

## Ortak güvenlik sözleşmesi

Server-side HTTP client, allowlist, timeout, boyut/MIME doğrulama, rate-limit/backoff, cache ve private review zorunludur. Secret yalnız environment’ta tutulur. Sağlık/beslenme kaynakları provenance, confidence ve origin ile ayrılır; kullanıcı katkılı veri verified olarak gösterilmez.
