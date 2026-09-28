---
name: weekly-seo
description: Search Console'dan haftalık SEO incelemesi. Her Pazartesi veya "SEO nasıl gidiyor" sorusunda kullan.
---

# Haftalık SEO İncelemesi

Kaynak: Nicholas Dulait / ChatSEO yöntemi

## Adımlar

1. ChatSEO MCP bağlıysa: `send_message` → `"Compare my last 28 days to the previous 28. Which pages gained impressions, which lost clicks, and which keywords entered the top 10?"`
   `get_conversation_messages` ile yanıt bekle.
   
   ChatSEO yoksa: `php artisan coni:durum --json` → `olcum.delta` alanından dünle farkı al.

2. Gösterim zıplayan sayfaları işaretle → ilk 10'a yeni girmiş. `page-brief` kuyruğuna ekle.
   İlk 10'dan ilk 3'e itmek en ucuz kazanım.

3. Son 60 günde düzenlenen sayfaları "bekle" olarak işaretle. Dokunma.

4. 90+ gün önce düzenlenmiş, iyi brief'e rağmen hâlâ ilk 3 dışında olan sayfaları işaretle → link adayı.

5. `reviews/<YYYY-MM-DD>.md` dosyasına yaz:
   - Toplam tıklama/gösterim, dünkü fark
   - İş değerine göre sıralı 5 aksiyon (trafik değil, iş değeri)

## Kural
Son 60 günde düzenlenen sayfaya dokunma — Google gecikmeyle tepki veriyor, panikle geri almak öğretici.
90 gün iyi bir sayfada sonuç yoksa → link bak.
Tüm hacim ve tahmin sayılarını ölçümden al. Uydurma.
