# External Service Decision Matrix

| Servis | Alan | Auth / ücretsiz durum | Ana risk | Cache/Queue | Karar |
|---|---|---|---|---|---|
| Pexels | Görsel adayları | API key; 200/saat, 20k/ay | Attribution/lisans ve yanlış tarif eşleşmesi | 24s cache; aday indirme queue | Admin review ile fallback adayı |
| Eden AI | Görsel üretimi | API key; model/kredi doğrulanmadı | Ücret, model erişimi, moderation | Budget guard + queue | Capability ve bütçe onayı sonrası |
| Open Food Facts | Barkod/nutrition | Read auth’suz; 15/dk ürün, 10/dk arama | User-generated/veri lisansı | Uzun cache + opt-in queue | Production sonrası, unverified import |
| Cloudflare headers | Yaklaşık geo | Proxied domain; country free | Spoofed header/trusted proxy, privacy | Request scope; retention cleanup | Domain aşamasında |
| Resend | Transactional mail | API key; free 3k/ay | Domain/DPA/deliverability | Queue + bounce/error log | Domain aşamasında |
| Better Stack | Uptime | Plan doğrulaması gerekir | Ek vendor/privacy | Dış HTTP monitor | Staging sonrası |
| DeepL | Çeviri | Key/plan gerekir | Tek aktif dil kararı | Queue + human review | Çoklu dil fazı |

## En fazla üç yeni servis önerisi

1. **Resend** — domain doğrulandıktan sonra transactional e-posta.
2. **Cloudflare** — DNS/proxy kurulduğunda güvenli country header ve edge koruması.
3. **Open Food Facts** — yalnız kullanıcı başlatmalı barcode enrichment için.

Pexels/Eden AI zaten mevcut provider hazırlığıyla çakışmadan değerlendirilir; bu belge entegrasyon veya satın alma onayı değildir.
