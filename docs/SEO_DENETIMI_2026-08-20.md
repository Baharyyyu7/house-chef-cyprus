# SEO Denetimi - 20 Ağustos 2026

Canlı site üzerinde ölçülmüş bulgular. Hedef: tr, en, pl, fa dillerinde
dünya çapında organik trafik.

---

## Yönetici özeti

Sitenin teknik temeli iyi: sitemap bölünmüş ve güncel, Recipe ve BlogPosting
şemaları doğru, başlıklar benzersiz, HTTPS ve canonical yerinde.

Ama **çok dilli trafik hedefi şu anda teknik olarak imkânsız.** Sitede dil
URL'i yok; dört dil de aynı adreste yaşıyor. Google yalnız Türkçe sürümü
indeksleyebiliyor. İngilizce, Lehçe ve Farsça içerik üretilse bile arama
motorunda hiçbir zaman görünmez.

Bu tek madde, üretilecek bütün içeriğin getirisini belirliyor. İçerik
üretmeden önce çözülmesi gerekiyor.

---

## 1. ENGELLEYİCİ - Dil URL'i yok

**Etki: Çok yüksek.** Hedefin tamamını bloke ediyor.

**Kanıt:**

```
/en   -> 404      /de   -> 404
/pl   -> 404      /fa   -> 404
POST /dil -> 419  (dil değişimi oturum/çerez ile)
<html lang="tr">  (her ziyaretçiye aynı URL)
```

Dil seçimi `POST /dil` ile çerezden yapılıyor. Bunun sonuçları:

- Her dil **aynı URL'de** yaşıyor. Google bir URL için tek bir sürüm
  indeksler; o da Türkçe.
- **hreflang kurulamaz.** hreflang farklı URL'leri birbirine bağlar; farklı
  URL yoksa etiket de yazılamaz. Sitede hiç hreflang yok - bu bir eksik
  değil, mevcut mimarinin zorunlu sonucu.
- Googlebot çerez taşımaz ve `Accept-Language` göndermez. Türkçe dışındaki
  hiçbir sürümü **hiç görmez.**

**Çözüm:** Dil önekli URL yapısı.

```
/            → tr (varsayılan)
/en/...      → İngilizce
/pl/...      → Lehçe
/fa/...      → Farsça
```

Her sayfa kendine canonical verir, hreflang kümesi kendisi dahil bütün
dilleri ve `x-default`'u listeler. Çerez yalnız **kullanıcı tercihini
hatırlamak** için kalır; içeriği belirleyen URL olur.

Bu bir mimari değişiklik ve içerik üretiminden **önce** gelmeli. Aksi hâlde
üretilen İngilizce, Lehçe, Farsça yazıların tamamı arama motoruna görünmez
kalır.

---

## 2. YÜKSEK - robots.txt sitemap'i duyurmuyor

**Kanıt:** `robots.txt` içinde `Sitemap:` satırı yok. Sitemap kendisi
sağlam ve 200 dönüyor:

| Sitemap | URL |
|---|---|
| pages | 16 |
| recipes | 181 |
| blog | 27 |
| images | 86 |

**Çözüm:** Tek satır.

```
Sitemap: https://housechefcyprus.com/sitemap.xml
```

Search Console'a elle eklenmiş olsa bile Bing ve diğer motorlar bu satırı
arıyor. En ucuz düzeltme.

---

## 3. KARAR GEREKTİRİYOR - Yapay zekâ tarayıcıları kapalı

**Kanıt:** `robots.txt` şunları tamamen engelliyor:

```
GPTBot, ClaudeBot, Google-Extended, CCBot, Bytespider,
Amazonbot, Applebot-Extended, meta-externalagent
```

Bu bilinçli bir içerik politikası ve savunulabilir: içeriğin izinsiz model
eğitiminde kullanılmasını engelliyor.

Ama şunu bilerek almalısın: **site ChatGPT, Claude, Perplexity ve Google AI
Overviews yanıtlarında kaynak olarak görünemez.** Yemek tarifi, "Kıbrıs'ta
ne yenir" gibi sorgularda kullanıcıların giderek daha büyük bölümü bu
yüzeylerde. Turist niyetli aramalarda bu yüzeyler özellikle güçlü.

İki uç var, ortası da mümkün:

| Seçenek | Sonuç |
|---|---|
| Şimdiki hâl | İçerik korunur, AI yanıtlarında hiç görünmezsin |
| `Google-Extended` ve `GPTBot` açılır | AI Overviews ve ChatGPT'de kaynak olabilirsin |
| Yalnız `ai-train: no`, `ai-input: yes` | Eğitime hayır, yanıt üretmede alıntıya evet |

Üçüncü seçenek çoğu yayıncının vardığı denge. **Bu senin kararın**; ben
değiştirmedim.

---

## 4. İYİ DURUMDA - dokunma

Denetimde sorun çıkmayan, doğru kurulmuş alanlar:

- **Structured data**: tarif sayfalarında `Recipe`, `HowToStep`,
  `BreadcrumbList`; blog sayfalarında `BlogPosting`, `FAQPage`,
  `Question/Answer`, `Organization`. FAQ şeması özellikle değerli.
- **Başlıklar benzersiz**: `Baklava Dilimi · House Chef Cyprus`,
  `Adana Kebabı · House Chef Cyprus` - şablon çakışması yok.
- **Sitemap bölünmüş ve `lastmod` taşıyor.**
- **Canonical her sayfada var.**
- **HTTPS ve yönlendirmeler temiz.**
- **Hero görseli optimize**: 221 KB AVIF, `fetchpriority=high`.

---

## 5. Öncelik sırası

| # | İş | Etki | Zorluk |
|---|---|---|---|
| 1 | Dil önekli URL yapısı + hreflang | Çok yüksek | Yüksek |
| 2 | `robots.txt`'e Sitemap satırı | Orta | Çok düşük |
| 3 | AI tarayıcı politikası kararı | Yüksek | Karar |
| 4 | Lehçe (`pl`) dilini ekle | - | Düşük |
| 5 | İçerik üretimi (tr/en/pl/fa) | Yüksek | Sürekli |

**5'i 1'den önce yapma.** Dil URL'leri olmadan üretilen yabancı dil içeriği
arama motoruna görünmez; emek boşa gider.

---

## 6. Dil stratejisi

Site şu an `tr, en, de, fa` destekliyor. İstenen: `tr, en, pl, fa`.
Lehçe eklenecek, Almanca kararı senin.

Her dil kendi arama niyetinden başlamalı; Türkçe yazının çevirisi değil.

| Dil | Kitle | Damar |
|---|---|---|
| `tr` | Türkiye ve Kıbrıs'taki ev aşçısı | "hellim nasıl kızartılır" |
| `en` | Kıbrıs'a gelen turist, diaspora | "what is halloumi", "cyprus food guide" |
| `pl` | Kıbrıs'a yüksek hacimli turist gönderen pazar | "co jeść na cyprze", "halloumi przepis" |
| `fa` | Bölgedeki Farsça konuşan okuyucu | kendi mutfak terimleriyle |

**Lehçe neden mantıklı:** Polonya, Kıbrıs'a düzenli charter turizmi olan ve
İngilizce içerikle doymamış bir pazar. Rekabet İngilizceye göre çok daha
düşük.

**Uyarı:** Google'ın 2025 duruşuna göre yapay zekâ çevirisi kendiliğinden
spam değil - ama **ölçekli düşük değerli çeviri** "scaled content abuse"
kapsamına giriyor. Ayrıca yardımcı içerik sistemi **site geneli** çalışıyor:
çok sayıda zayıf dil sayfası, güçlü Türkçe sayfaların sıralamasını da
aşağı çeker.

Kural: **gerçekten iyi yapamayacağın dil sayfasını hiç açma.**

---

## 7. Ölçüm

İki hafta sonra bakılacaklar:

- Search Console'da dil başına **gösterim**
- Hedeflenen sorgularda **ortalama sıra**
- hreflang hata raporu (kurulduktan sonra)
- Sıralaması hiç oluşmayan sayfalar → silinmeli
