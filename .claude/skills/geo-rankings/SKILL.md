---
name: geo-rankings
description: Bu site için GEO planla. ChatGPT, AI Overviews, Perplexity, GEO veya yapay zeka tarafından kaynak gösterilme hakkında sorulduğunda kullan.
---

# GEO (Generative Engine Optimization)

Kaynak: Nicholas Dulait / ChatSEO yöntemi

## Temel fikir
GEO aslında SEO'dur: yapay zeka "best <keyword> <yıl>" biçiminde Google'ı arar.
Listelerde yer al, başlıklara "best" ve yılı ekle.

## Adımlar

1. `keywords.md` dosyasından en iyi 5 ticari keyword'ü al (yoksa `php artisan coni:durum --json` → `adaylar`).

2. Her keyword için yapay zeka sorgusu oluştur: `"best <keyword> <mevcut yıl>"`.
   Örnekler: "best adana kebab recipe 2026", "best baklava recipe 2026".

3. ChatSEO MCP bağlıysa: `send_message` → `"Who ranks top 10 on <best keyword year>? Which of those pages are rankings or listicles, and do they mention my site?"`
   ChatSEO yoksa: WebSearch ile `best <keyword> <yıl>` ara, listicle/ranking sayfalarını bul.

4. `geo.md` dosyasına yaz: sorgu, sıralanan listicle'lar, bizim sayfamız var mı, arkasındaki site, iletişim.

5. Eşleşen sayfalarımıza başlık düzenlemesi öner: "best" ve yılı ekle.
   → `page-brief` + `ship-page` üzerinden geçir. Doğrudan düzenleme yok.

6. Listede olmadığımız her listicle için kısa ve kişisel bir İngilizce/Almanca outreach taslağı yaz.
   Dosya: `docs/claude-coni/outreach/<YYYY-MM-DD>-geo-<dil>.md`
   **Göndermiyorum — taslak kullanıcıya.**

## Kural
Üçüncü taraf listesinde yer almak kendi sitemizde kendimizi 1. sıraya koymaktan çok daha etkili.
Ücret isteyen, alakasız veya link ağı niteliğindeki site aday değil.
