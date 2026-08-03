# Google Ecosystem Readiness

House Chef Cyprus harici Google kimlik bilgileri olmadan çalışır. Tüm Google feature flag'leri varsayılan olarak kapalıdır.

## Hazır olanlar

- Production-only Search Console HTML meta doğrulaması
- Ayrı recipe, blog, page ve image sitemap'leri
- Consent-default-denied GA4 veya GTM render katmanı
- GTM etkinse direct GA4 çift ateşleme koruması
- Secret göstermeyen admin Integration Center
- Merkezi owner-action kartları
- Hostinger SMTP, scheduler, bounded queue ve first-party SEO sağlık özeti

## Owner action gerekenler

1. Search Console domain/URL property sahipliğini Google hesabında doğrulama ve sitemap index gönderme.
2. GA4 property/web stream oluşturup `G-…` Measurement ID'yi production secret olarak kaydetme.
3. Opsiyonel GTM container oluşturup `GTM-…` ID kaydetme ve container'ı Google arayüzünde yayınlama.
4. Google ile giriş adaptörü bu teslimde uygulanmadı; bu nedenle henüz OAuth credential oluşturulmamalı ve durum `NOT_AVAILABLE` kalır.
5. reCAPTCHA adaptörü bu teslimde uygulanmadı. Mevcut Turnstile kaldırılmaz ve reCAPTCHA durumu `NOT_AVAILABLE` kalır.

Search Console, Analytics veya OAuth bağlantısı kanıtlanmadan metrik, trafik veya bağlantı başarısı raporlanmaz.
