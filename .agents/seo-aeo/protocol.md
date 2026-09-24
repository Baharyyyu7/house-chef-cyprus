# House Chef Cyprus — SEO & AEO Çalışma Protokolü

Bu protokol, **Haftada 1 Sayfa** prensibiyle hem Google'ın geleneksel mavi linklerini hem de yapay zeka arama motorlarının (Google AI Overviews, Perplexity, ChatGPT Search, Bing Copilot) sıfır tıklamalı doğrudan yanıtlarını kazanmak için tasarlanmıştır.

---

## 01. Haftanın Sayfasını Seçme (Find The One Page Worth Your Week)

1. **GSC ile Dönüşümleri Eşle:** Search Console'daki gösterim ve tıklama sayılarını, sayfa bazındaki dönüşümlerle (`lead_chef_inquiry`, `recipe_cook_mode_start`, `ad_impression`) yan yana koy.
2. **Potansiyeli Yüksek Sayfayı Bul:** Zaten sıralama alan (örn: 6–15. sıra arası), dönüşüm sağlama potansiyeli olan ama görünürlüğü bir tık yetersiz kalan sayfayı seç.
3. **Tuzakları Ayıkla:** Yüksek gösterim alan ama sıfır dönüşüm getiren sayfaları "tuzak" (trap) olarak işaretle; bunlara körü körüne trafik basma.
4. **Niyet Uyuşmazlığını (Intent Mismatch) Kontrol Et:** Canlı SERP'e bak. İlk 3 sırada tarif rehberi varken sen rezervasyon formu koyduysan niyet uyuşmazlığı vardır; düzelt.
5. **Aday Kararı:** Her sayfaya gerekçesiyle birlikte karar ver: **TUT (Keep)**, **DÜZELT VE TUT (Keep with a fix)** veya **BIRAK (Drop)**.

---

## 02. Dört Geçişli Sayfa İncelemesi (Take The Page Apart In Four Passes)

### Geçiş 1: Erişim ve Hız (Access + Speed)
- Taranabilir mi, indekslenebilir mi, gerçek metin mi (JS bağımlı mı)?
- PageSpeed Insights & Core Web Vitals (LCP < 2.5s, CLS < 0.1, INP < 200ms). Mobil öncelikli.

### Geçiş 2: Rekabet Analizi (Competition)
- Canlıda ilk 10 rakip sayfanın tam içeriğini tara ve yan yana oku.
- Eksik alt başlıklar, kullanıcıya verilmeyen detaylar neler?
- Yapılan her iddia bir URL kaynağıyla desteklenmeli.

### Geçiş 3: Yapay Zeka Motorları (Answer Engines - AEO)
- **İlk Cümle Kuralı:** Her H2/H3 başlığının altındaki İLK CÜMLE doğrudan sorunun yanıtı olmalıdır.
- Başlıkları insanların sorduğu gerçek sorularla ("Kolokas kaç dakikada pişer?", "Kıbrıs'ta şef nasıl tutulur?") eşle.
- Bölümler tek başına alıntılanabilir (self-contained) olsun.
- Google AI Overviews ve Perplexity kaynak gösterimlerine optimize et.
- **Kestirmeleri Atla:** `llms.txt` eklemek Google Search için anlamsızdır, atla. Kanıtlanmamış aşırı şema zorlamalarından kaçın, sadece doğrulanmış `Recipe`, `FAQPage`, `LocalBusiness` şemalarını kullan.

### Geçiş 4: Dönüşüm Yolu (Conversion Path)
- **Tek ve Net Bir Sonraki Adım:** İlk ekranda açıkça görünen, izlenen (tracked) ve iç linklerle beslenen bir CTA:
  - Rezervasyon için ➔ "Özel Şef Menüsünü İncele / Teklif Al"
  - Tarif için ➔ "Pişir Modunu Başlat" veya "Malzemeleri Dolabıma Kaydet"

---

## 03. Haftalık Döngü (Run The Loop Weekly)

1. Taze arama + dönüşüm verilerini `state.md` içine çek.
2. Son değişiklikten önceki baz çizgiyle karşılaştır.
3. Sayfadaki hiçbir şeyin bozulmadığını test et.
4. Kanıt ve kaynak linkleriyle birlikte **TEK BİR DEĞİŞİKLİK** öner.
5. **İnsandan (Kullanıcıdan) "Evet" onayı gelmeden yayına alma.**
6. Yaşananları, tarih ve ölçümlerle `log.md` içine yaz.

> **Ölçüm İlkesi:** 2 haftadan kısa hareketleri göz ardı et. Sıralama yükseldi ama dönüşüm gelmediyse bu bir başarısızlıktır. Sıralama sabit kaldı ama dönüşüm arttıysa bu büyük bir ZAFERDİR.
