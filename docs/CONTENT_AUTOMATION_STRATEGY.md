# Content Automation Strategy

## İlke

Otomasyon araştırmayı hızlandırır; yayın kararını vermez. İçerik, kaynak veya görsel otomatik yayınlanmaz. CAPTCHA aşma, erişim kısıtı atlama, telifli metni yeniden üretme ve izinsiz scraping kapsam dışıdır.

## İçerik durumları

`idea` → `research` → `draft` → `review` → `approved` → `scheduled` → `published`; gerektiğinde `needs_update` veya `archived`.

Her geçiş actor, zaman, kaynak özeti ve güvenli audit kaydı taşır. Yalnız editor/uygun yetki `approved` ve `published` geçişini yapabilir.

## Gelecek iş akışı

1. Niyet ve konu brief'i oluşturulur.
2. İzinli kaynaklar, lisanslar ve güncellik değerlendirilir.
3. Özgün taslak veya yeniden yazım üretilir.
4. Telif/benzerlik ve olgu doğrulaması yapılır.
5. İlgili tarif, FAQ, internal link, meta ve görsel önerileri hazırlanır.
6. Editor onayı sonrası zamanlanmış yayımlama; performans ve güncellik takibi yapılır.

## X/Twitter ve dış kaynaklar

Gelecekte yalnız resmi API, kullanım şartları, maliyet, gizlilik ve saklama değerlendirmesi tamamlandığında; opt-in listeler ve editor onayıyla kullanılabilir. Otomatik paylaşım, kullanıcı verisi toplama veya API anahtarının kodda saklanması yasaktır.

