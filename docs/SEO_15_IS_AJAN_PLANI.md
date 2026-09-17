# SEO 15 İş Ajan Planı (housechefcyprus.com)

Makale: "The GPT-6 Astra SEO Agent Setup Behind 78k AI Mentions" (borjafat).
Bu belge o makaledeki 15 işi bu deponun mevcut komut ve modellerine uyarlar.
Hangi işin hazır, hangisinin engelli ve hangisinin eksik olduğunu gösterir.

**Kural:** Hiçbir ajan yazı yayınlamaz, toplu içeri aktarma yapmaz, dil
etkinleştirmez. Tüm çıktı `draft` olarak kalır; yayın kararı insana aittir.
Daha önce 3.200 sayfa toplu yayına çıktı; aynı hata tekrarlanmaz.

---

## 0. Mevcut durum (2026-09-17)

| Kaynak | Durum |
|---|---|
| Katalog (tarif/blog) | Yerel DB boş; canlı sunucuda 680 tarif, 2.210 blog |
| Search Console | API bağlantısı **yok** — `seo:search-console-import` hazır ama yetki bekliyor |
| AI corpora (llms.txt) | 14 korpus, 7 yerel ayar; önbellek taze |
| AI atıf ölçümü | **Ölçülmedi** ("unmeasured_ai_citations" olarak kayıtlı) |
| Backlink | 453 aday izleniyor, 0 gönderim |
| SEO sorunu | 2.274 açık |

Kritik yol: Search Console bağlanmadan hiçbir işin çıktısı ölçülemez.
"Bilinmeyen değer sıfır değildir" kuralı bu yüzden var.

---

## 1-15. İşlerin uyarlaması

Her iş için: **hazır komut** · **blokör** · **ajan talimatı**.
`--json` çıktıları makine okunur; tercih bu olmalı.

---

### İş 1: Anahtar kelime araştırması — ✅ HAZIR

```bash
php artisan seo:content-opportunities --json
```

- Mevcut: `ContentGapService` katalog boşlukları ve zero-result sinyallerini
  sayar; 2.276 fırsat kayıtlı.
- Ek kaynaklar (makaleden): GSC gösterimleri, Google Trends mevsimselliği,
  otomatik tamamlama, "People also ask" kutusundaki sorular.
- **Ajan talimatı:**
  - Fırsatları gösterim/tıklama/niyet olarak **ayrı listelerde** sırala.
  - Trend/güncel ispatı olmayan kelimeye "ölçüldü" deme; kaynağı yanında yaz.
  - Her kelimeyi tek bir mevcut hedefe eşle; yeni sayfa önerirken gerekçe ver.
  - Yazma gerekçesi: GSC satırı, rakip boşluğu veya gerçek sorgu. Uydurma yok.

---

### İş 2: Search Console denetimi — 🟡 ENGELLİ (API bağlantısı yok)

```bash
php artisan seo:search-console-import --json
```

- Blokör: `.env` içinde `GOOGLE_SEARCH_CONSOLE_CLIENT_ID/SECRET/REFRESH_TOKEN`
  eksik veya yetki alınmadı. Devreye girmeden önce dönem tam 28 gün
  kuralı var (karşılaştırılabilirlik).
- **Makaleden alınacak:** Grafiği tek metrikle okuma. Tıklama ve gösterim
  kısa listeleri aynı sayfaları üretmez; iki listeyi birlikte değerlendir.
- Google AI gösterimleri (Generative AI features raporu) Web toplamına
  **eklenmez** — Google zaten içinde sayıyor.

---

### İş 3: AI bahsetmeleri izleme — ❌ EKSİK (altyapı var)

Mevcut: `LlmsTxtController` korpus üretiyor; `AutonomousGrowthEngine` önbellek
tazeliyor. Eksik: gerçek atıfları **kayıt altına** alan bir ölçüm yok.

- **Ajan talimatı (makaleden):** Hazır alıcı soruları demek yerine önce
  **alıcının markayı bilmeden** sorduğu soruları kaydet. Her cevap için:
  tam soru, tarih, motor (ChatGPT/Perplexity/Gemini), mod ve çıktıyı sakla.
- Marka bahsi ≠ site bağlantısı: ikisini ayrı işaretle.
- Cevap yoksa "yok" demek yerine **eksik/missing** işaretle; yanlış sıfır
  üretme.
- Sitede dosya: `docs/media-review/` deseninde AI yanıt arşivi tutulabilir.

---

### İş 4: Atıf boşlukları — ❌ EKSİK

Mekanik: AI yanıtı hangi kaynağı öneriyor → o sayfadaki hangi gerçeği
destekliyor → bizim sitemiz o soruyu net cevaplıyor mu?

- **Ajan talimatı:**
  - Tam kaynaklı URL sakla; yalnız domain değil.
  - Desteklenen gerçeği tek cümleyle yaz.
  - Bizim hangi sayfanın o soruyu cevapladığını/cevaplamadığını raporla.
  - Öneri: document update / veri testi / yeni sayfa. Tek cevabı genel
    görünürlük sayılansayma.

---

### İş 5: Fırsat puanlama — 🟡 KISMİ (priority alanı var)

`ContentOpportunity` üzerinde `priority` (0-100) ve `risk_level` var; bu
tek eksen. Makale çift eksen + efor ister:

- Alıcı uyumu: 1-5, gerekçeli.
- Kanıt gücü: 1-5 (ne gözlemi var?).
- Efor tahmini: hangi iş (erişim, araştırma, düzenleme).
- **"Hangi eksik veri önceliği değiştirir?"** yaz. Puan tahmin değildir,
  trafik vaadi değildir.

---

### İş 6: Topik otorite haritası — ✅ HAZIR

Varlıklar: `KeywordTopic`, `SEO_TOPIC_CLUSTER.md`.

- **Ajan talimatı:** Harita alıcının **kararı** etrafında kur (yazlık
  Kıbrıs mutfağı rehberi, hellim kızartma, kahvaltı derlemesi ayrı
  kararlardır). Mevcut URL'yi ana soruya eşle; yeni sayfa öner ancak
  sorunun kendi cevabı yoksa. Bir sayfa bir ana soru. Okur doğal olarak
  sonraki sayfaya geçiyorsa link.

---

### İş 7: İçerik brifleri — ✅ HAZIR

Varlık: `ContentBrief` modeli + [GOREV_SEO_BLOG_URETIMI.md](GOREV_SEO_BLOG_URETIMI.md).

- **Ajan talimatı (makaleden):** Brif, mevcut sayfanın hangi soruyu zaten
  cevapladığını yazsın; öneri **yeni kanıtla** gerekçelensin (test, oran,
  süre, kaynak). Belgelenmiş özellikler ile elle test edilmiş bulgu ayrı
  etiketlenir. Doğrulanmamış iddia boş bırakılır. Bunlar alınabilir bilgi
  kazancıdır; uzun taslak tek başına bulgu değildir.
- Kalite eşiği: ≥900 kelime, somut öğreti, 3-6 gerçek SSS, kannibalizasyon
  kontrolü, alerjen satırı.

---

### İş 8: LLM yapısı — ✅ HAZIR (schema + llms.txt)

Varlık: `SeoSchemaService`, `LlmsTxtController`.

- Mevcut schema: Recipe, Article, FAQ, Heritage.
- **Ajan talimatı:** Başlık okurun sorusuyla eşleşsin; ana cevap okunabilir
  metinde ve etiketli tabloda olsun; önemli gerçek görsel/içerikte kilitli
  kalmasın. AI markup veya yeni schema "gerekli" değil; Google'ın yönergesi
  okunabilir içerik + doğru HTML. Başka asistanın kendi alma sistemi var;
  biçim düzeltmesi atıf garantisi değildir.

---

### İş 9: İç bağlantılar — ✅ HAZIR

```bash
php artisan seo:internal-links --json
```

- Mevcut: `InternalLinkService` yalnız editoryal incelemelik öneri listeler;
  otomatik eklemez.
- **Ajan talimatı (makaleden):** Kaynak sayfa + hedef + önerilen çapa + oturan
  cümleyi raporla. Gezinme bağlantısını içerik bağlantısından ayır.
  Hedef çalışıyor mu, canonical doğru mu diye denetle. Footer linki çok
  görünüyorsa içerikteki **bağlamsal** linklere bak; "çok link var" demeden.

---

### İş 10: Kannibalizasyon — ✅ HAZIR

```bash
php artisan seo:cannibalization-check --json
```

- Yeni: `SeoCannibalizationCheck` komutu + `CannibalizationCheckService`.
  Salt-okunur; yazmaz, yayınlamaz. `auto_publish=false` her çıktıda.
- Kaynaklar: blog `primary_keyword`, `SeoMetadata.primary_query` ve
  `focus_topic` — yalnız published/scheduled/approved içerik.
- Eşleştirme Türkçe→ASCII normalizasyonla (Ç→c, Ğ→g, I/İ/ı→i, Ö→o, Ş→s,
  Ü→u); "kıbrıs mezeleri" ile "KIBRIS MEZELERİ" aynı gruba düşer.
- Grup ≥3 sayfa ise `high`, 2 sayfa ise `medium`. Test:
  `tests/Feature/SeoCannibalizationCheckTest.php`.

- **Ajan talimatı (makaleden):** Aynı sorgu için hangi iki sayfa da sıralanıyor
  bul. Başlık benzerliği tek başına kanıt değildir:
  - Her sayfanın niyetini ve vaadini karşılaştır.
  - Zaman içinde hangi URL görünüyor, tıklama/pozisyon trendine bak.
  - Sonra "ayrıştır / yenile / birleştir" kararı; kanıt olmadan yapma.
  - İki yararlı sayfa aynı sorguda durabilir; asıl soru iki farklı ihtiyacı
    karşılayıp karşılamadıkları.

---

### İş 11: İçerik tazeleme — ✅ HAZIR

```bash
php artisan seo:content-refresh --json
```

- Mevcut: `ContentRefreshService` süresi gelen yayınlar için yalnız öneri
  üretir (yazmaz).
- **Ajan talimatı (makaleden):** Değişiklik **nedeni** kanıtlı olsun:
  güncel olmayan gerçek, değişen okur sorusu veya belgelenmiş düşüş. Mevcut
  metni önerilenle yan yana göster; değerli kalan bölümü koru; doğrulanan
  gerçekleri işaretle. Yayınlanmadan önce taban çizgisi (baseline) kaydet.
  Kuyruktaki öneri, ölçülmüş etki **değildir**.

---

### İş 12: Teknik denetim — ✅ HAZIR

```bash
php artisan seo:audit --json        # teknik + içerik + schema
php artisan seo:sitemap-audit --json
php artisan seo:health --json       # özet anlık görüntü
```

- Varlık: `SeoIssue` modeli, sürüm kontrolü, İndexNow, sitemap 5 kaynak.
- **Ajan talimatı (makaleden):** Sorunları sebep + etkilenen sayfa türüne
  göre grupla. "Alternate page with proper canonical" doğru bir hariç tutma
  olabilir; "Server error (5xx)" ise sorundur. İndekslenmemesi gereken URL'yi
  "hata" sayma; iş amaçlı önemli sayfa + yeniden üretilebilir sorunlara
  öncelik ver. Eski rapor satırını araştırırken URL Inspection + güncel
  fetch ile doğrula.

---

### İş 13: Link çıkışı — 🟡 KISMİ

Mevcut: `seo:backlinks-verify` (doğrulama, mesajat göndermez), 453 kayıtlı
aday. Gönderim/gönderim izleme aracı yok ve kurallar gereği gönderim insana
aittir.

- **Ajan talimatı (makaleden):** Gazetelerin gerçek sayfasını **oku**;
  niyet benzerliğini aday aday not et. Mesaj taslağı o anki makaleye atıf
  yapsın; araştırılan aday, gönderilen mesaj ve kazanılan link ayrı kayıt
  edilsin. Gönderime insan onayı şart.

---

### İş 14: Link değişimi yerlerini inceleme — 🟡 KISMİ

Mevcut: `seo:backlinks-verify` kaynak sayfada link var mı doğrular;
platform doğrulaması Google'ın onayı değildir.

- **Ajan talimatı (makaleden):** Kaynak sayfayı aç, hedef + çapanın
  niyetlenen yerleşimle eşleştiğini doğrula. İlgililik > domain skoru.
  Ticari koşulları onaya çıkar; yeni yerleşim otomatik taahhüt edilmez.

---

### İş 15: SEO raporlama — ✅ HAZIR

```bash
php artisan seo:morning-brief --json
php artisan seo:locale-growth --json
```

- Mevcut: `SeoMorningBrief` açık sorun + fırsat sayar; dış ölçüm kapalıysa
  "bilinmeyen sıfır değildir" notunu korur.
- **Ajan talimatı (makaleden):** Tam dönemleri eşleşen filtrelerle karşılaştır
  (şimdilik `seo:search-console-import` 28 gün şartı bunu zorlar). Yayınlanmış
  değişikliği öneri listesinden ayır; Web ölçümü, Google AI gösterimi ve
  dış AI atıfı üçünü ayrı tut; "değişiklik bunu yaptı" deme, ne değişti ve
  sonraki adım kime ait yaz. Mevsimselliği dönem karşılaştırmadan önce kontrol et.

---

## Eksik olanın özeti

| İş | Durum | İlk adım |
|---|---|---|
| 1 anahtar kelime | ✅ | `seo:content-opportunities` |
| 2 GSC denetimi | 🟡 | `.env` GSC yetkisi |
| 3 AI bahsetmesi | ❌ | Kayıt şablonu + soru seti |
| 4 atıf boşluğu | ❌ | Kaynaklı atıf arşivi |
| 5 puanlama | 🟡 | `priority` üstüne kanıt/efor ekseni |
| 6 topik haritası | ✅ | `KeywordTopic` + cluster |
| 7 içerik brifi | ✅ | `ContentBrief` |
| 8 LLM yapısı | ✅ | schema + llms.txt |
| 9 iç bağlantı | ✅ | `seo:internal-links` |
| 10 kannibalizasyon | ✅ | `seo:cannibalization-check` |
| 11 tazeleme | ✅ | `seo:content-refresh` |
| 12 teknik denetim | ✅ | `seo:audit` / `sitemap-audit` |
| 13 link çıkışı | 🟡 | Gönderim onay akışı (insan) |
| 14 link inceleme | 🟡 | `seo:backlinks-verify` |
| 15 raporlama | ✅ | `seo:morning-brief` |

## Genel ajan kuralları

1. Her çıktıya kaynak/kanıt yaz; kaynağı olmayan değer sıfır sayılır.
2. Google AI gösterimi ve Web gösterimi asla toplanmaz.
3. Tek bir AI cevabı genel görünürlük kanıtı değildir.
4. Aynı soruları sabit aralıkla tekrar sor; tek seferlik cevap desen kaçırır.
5. Kuyruktaki öneri etki değildir; ölçü ancak yayınlandıktan sonra gelir.
6. Yayın kararı insana aittir; tüm çıktı `draft`.