---
name: page-brief
description: Bir hedef anahtar kelime için SEO brief oluştur. Bir kelime için sıraya girmek, sayfayı optimize etmek veya yeni sayfa oluşturmadan önce kullan.
---

# Sayfa Brief'i

Kaynak: Nicholas Dulait / ChatSEO yöntemi

## Adımlar

1. ChatSEO MCP bağlıysa: `send_message` ile siteId, `"I want to rank first on <keyword>. Tell me if I should optimise an existing page or create a new one, then give me the full brief."` gönder. `get_conversation_messages` ile yanıt bekle.
   ChatSEO yoksa: `php artisan coni:sorgular '<adres>'` ile mevcut sayfanın gerçek sorgu listesini al; Google'da keyword ara, ilk 3 sayfanın türünü not et.

2. Yanıttan çıkar:
   - Yeni sayfa mı, mevcut sayfa mı optimize edilecek, hangi URL
   - Ana keyword ve tüm ikincil keywordler
   - İlk 3'ün ortak bölümleri (yapı, sayfa türü)
   - Önerilen başlık ve meta description

3. Mevcut URL zaten tıklama/gösterim alıyorsa → **yeni sayfa planını durdur. Optimize et.**

4. Başlık kontrolü:
   - Ana keyword var mı
   - SERP önizlemesinde truncate olmadan pixel sınırına uyuyor mu (tr: ~55 karakter, en: ~60)
   - İkincil modifier'lar sığıyor mu

5. `briefs/<keyword-slug>.md` olarak kaydet. **Siteye henüz dokunma.**

## Kanibalizasyon kontrolü
Aynı keyword için birden fazla sayfan varsa ve ikisi de 7-8. sıradaysa → tek sayfada birleştir.
Biri tıklama alıyorsa onu güçlendir, diğerini 301 ile yönlendir.
