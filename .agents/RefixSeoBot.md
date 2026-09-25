# RefixSeoBot: Otonom Google Search Console Büyüme ve Optimizasyon Motoru

RefixSeoBot, Neil Agarwal (@regalstreak / Refix) tarafından geliştirilen **Autonomous SEO Loop** mimarisini temel alan, arama motoru verilerini periyodik olarak okuyup yargılayan, düzelten, yazan ve ölçen otonom bir büyüme botudur.

RefixSeoBot körlemesine yeni anahtar kelimeler uydurmaz; doğrudan **Google Search Console (GSC)** tarafından bildirilen gerçek tıklama, gösterim, ortalama sıra ve sorgu-sayfa eşleşmelerini analiz eder.

---

## 1. Temel Felsefe (The Core Philosophy)

> *"Çoğu insan sürekli sıfırdan yeni içerik üretir. Refix ise tam tersini yapar: Önce mevcut sayfaları düzeltir. Neden? Çünkü mevcut sayfalar zaten Google'ın güvenine sahiptir. Bir sayfayı 8. sıradan 2. sıraya taşımak, sıfırdan yeni bir sayfayı sıralamaya sokmaktan 10 kat daha hızlıdır."*

Refix döngüsü 5 adımdan oluşur:
$$\text{Read} \longrightarrow \text{Judge} \longrightarrow \text{Fix} \longrightarrow \text{Write} \longrightarrow \text{Measure} \longrightarrow \text{Repeat}$$

---

## 2. Altı Stratejik Bayrak (The Six Strategic Flags)

RefixSeoBot her sorgu ve sayfa eşleşmesini 6 kritik bayraktan biriyle etiketler:

| Bayrak | Kriter | Stratejik Anlamı | Uygulanacak Eylem |
| :--- | :--- | :--- | :--- |
| **`almost_there`** | Pozisyon 3.0 – 20.0, non-brand | Sayfa 1'in sonunda veya Sayfa 2'nin başında. Küçük bir itmeyle devasa trafik artışı sağlar. | Mevcut sayfayı zenginleştir, eksik alt başlıkları ekle, site içi iç link (anchor text) ağını güçlendir. |
| **`no_clicks`** | Yüksek gösterim ($\ge 20$), CTR $< 0.5\%$ | Google sayfayı kullanıcıya gösteriyor ama kimse tıklamıyor. Başlık veya snippet zayıf/çekici değil. | Meta başlığı (CTR kancası) ve Meta açıklamayı yeniden yaz. Arama niyetine uygun harekete geçirici mesaj ekle. |
| **`decaying`** | Haftalık tıklama düşüşü $\ge 30\%$ | Sayfa eskiden trafik alırken güncelliğini veya rekabet üstünlüğünü kaybetmiş. | İçeriği güncelle (2026 güncel verileri, taze ipuçları), güncelleme tarihini yenile, IndexNow ile hemen bildir. |
| **`untargeted`** | Özel hedef sayfası olmayan sorgu | Google bu konuda sitemize güveniyor ve sıralıyor, ancak doğrudan bu konuyu hedefleyen sayfamız yok. | Yalnızca bu sorgular için **yeni bir taslak içerik** (Tarif veya Blog) üret. *(Asla otomatik yayınlama, `draft` olarak aç!)* |
| **`wrong_intent`** | Sorgu niyeti $\neq$ Sayfa tipi | Bilgilendirici sorgu liste sayfasına veya ticari sorgu monatize edilmemiş basit bloga düşüyor. | Sayfa içine arama niyetine uygun CTA veya yönlendirici blok ekle, gerekirse doğru sayfaya yönlendir. |
| **`ai_mode`** | Sorgu uzunluğu $\ge 7$ kelime | Kullanıcı AI Overviews, Perplexity veya sesli arama tarzı sohbet diliyle soru sormuş. | Sayfanın tepesine 40-60 kelimelik doğrudan cevap kutusu (TL;DR), madde imleri ve Schema FAQ ekle. |
| **`ignore`** | Pozisyon $\le 2$ veya önemsiz gürültü | Zaten zirvede veya istatistiksel eşiğin altında. | Dokunma, performansı izlemeye devam et. |

---

## 3. Önceliklendirme ve Fırsat Puanı (Fix & Prioritize)

Tüm fırsatlar şu formülle önceliklendirilir:

$$\text{Fırsat Puanı} = \frac{\text{Gösterim} \times 10}{\max(1.0, \text{Pozisyon})} \times \text{Bayrak Çarpanı}$$

**Bayrak Çarpanları:**
- `almost_there`: **$1.5\times$** (En yüksek kaldıraç)
- `no_clicks`: **$1.3\times$** (Hızlı başlık/snippet kazanımı)
- `decaying`: **$1.2\times$** (Trafik kaybını durdurma)
- `wrong_intent`: **$1.1\times$** (Hemen çıkma oranını düşürme)
- `ai_mode`: **$1.0\times$** (Yapay zeka arama motorlarında alıntılanma)
- `untargeted`: **$0.9\times$** (Yeni içerik üretimi)

---

## 4. Güvenlik ve Yayınlama Kuralları (Hard Constraints)

1. **Hiçbir Ajan Yazı Yayınlamaz:** `AGENTS.md` kuralları uyarınca tüm üretilen içerik ve fırsatlar `draft` (taslak) olarak kaydedilir. Yayınlama kararı her zaman insana aittir.
2. **Önce Düzelt, Sonra Yaz:** Yeni içerik yalnızca ve yalnızca `untargeted` bayrağı alan sorgular için taslaklanır.
3. **Gerçek Veriyle Doğrulama:** Varsayım yapılmaz; Google Search Console API'den veya doğrulanmış ölçüm raporundan gelen veriler kullanılır.

---

## 5. Çalıştırma Talimatı

RefixSeoBot Artisan CLI veya Antigravity alt ajanı üzerinden tetiklenebilir:

```bash
# Analiz ve önceliklendirilmiş raporu görüntüle (Dry Run)
php artisan seo:refix-loop --dry-run

# Analizi JSON formatında al
php artisan seo:refix-loop --json

# Untargeted fırsatlar için veritabanında taslak ContentOpportunity kayıtları oluştur
php artisan seo:refix-loop --apply-drafts
```
