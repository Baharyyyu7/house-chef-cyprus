# SEO Blog Üretimi - Codex ve Antigravity

Bu belge blog içeriğinin nereye yazılacağını, nasıl üretileceğini ve neyin
kesinlikle yapılmayacağını tanımlar. Görsel üretimi ayrı belgelerde:
[GOREV_ANTIGRAVITY_GORSEL.md](GOREV_ANTIGRAVITY_GORSEL.md) ·
[GOREV_CODEX_TARIF_KAYITLARI.md](GOREV_CODEX_TARIF_KAYITLARI.md)

---

## 0. Önce okunacak: bu sitede bir kez ne oldu

**16 Ağustos 2026'da yaklaşık 3.200 makine üretimi sayfa toplu olarak yayına
çıktı.** Arama sonuçları dolduruldu, temizlemek günler aldı ve sitenin
güvenilirliği zarar gördü.

Bu yüzden aşağıdaki kural pazarlık konusu değil:

> **Hiçbir ajan hiçbir yazıyı yayına almaz.** Üretilen her şey taslak
> olarak durur. Yayın kararı insana aittir ve tek tek verilir.

Üretim komutu bunu kodla zorunlu tutuyor: teslim klasöründen gelen yeni
yazılar `draft` durumunda açılır, `published_at` boş kalır.

Bir de gerçekçi olalım: "dünyadan deli gibi ziyaretçi" hedefine ölçekli
otomatik içerikle gidilmiyor. Google'ın **scaled content abuse** politikası
tam olarak bunu hedefliyor ve cezası sitenin tamamen düşmesi. 500 vasat
yazı 50 iyi yazıdan daha az trafik getirir, üstüne siteyi riske atar.
Aşağıdaki kalite eşikleri bu yüzden var.

---

## 1. Teslim yeri

```
~/domains/housechefcyprus.com/shared/storage/app/content-import/blog/{slug}.json
```

Sunucuda, yerelde değil. Dosya adı yazının slug'ı olacak; slug hem dosya
adını hem de URL'i belirler. Bu dizin yazılabilir olduğu için yalnız JSON
veri dosyası kabul edilir; `.php` dosyaları asla çalıştırılmaz ya da içe
alınmaz.

Uygulama komutu bu klasörü ve depodaki `resources/editorial/blog-content`
klasörünü birlikte okur:

```bash
php artisan blog:apply-editorial-content            # rapor, hiçbir şey yazmaz
php artisan blog:apply-editorial-content --apply    # taslakları yazar
```

Aynı slug iki yerde varsa teslim klasörü kazanır.

---

## 2. Dosya biçimi

Sunucu teslimi JSON nesnesidir. Depodaki PHP editoryal dosyaları yalnız
sürüm kontrolündeki güvenilir kaynaklardır; teslim dizinine kopyalanmaz.

```json
{
  "title": "Hellim Nasıl Kızartılır? Dağılmadan Kızarma Sırrı",
  "excerpt": "Hellim neden tavada dağılır, neden lastikleşir? Sıcaklık, ...",
  "meta_title": "Hellim Nasıl Kızartılır: Dağılmadan Kızartma Rehberi",
  "meta_description": "Hellim kızartmanın sıcaklık, süre ve tava kuralları; ...",
  "primary_keyword": "hellim nasıl kızartılır",
  "secondary_keywords": ["hellim tarifi", "halloumi kızartma", "hellim dağılıyor"],
  "faq": [
    {"question": "Hellim neden dağılıyor?", "answer": "..."}
  ],
  "body": "## Giriş\n\nMarkdown gövde."
}
```

Alan kuralları:

| Alan | Kural |
|---|---|
| `title` | 55-65 karakter, birincil anahtar kelime doğal biçimde geçsin |
| `meta_title` | En fazla 60 karakter |
| `meta_description` | 140-158 karakter, tıklamayı hak eden bir vaat |
| `excerpt` | 1-2 cümle, meta_description'ın kopyası olmasın |
| `primary_keyword` | Tek bir ifade |
| `secondary_keywords` | 3-6 ifade |
| `body` | **En az 900 kelime**, Markdown, `##` ve `###` başlıklarla bölünmüş |
| `faq` | 3-6 gerçek soru; uydurma "sık sorulan" soru yazma |

---

## 3. Anahtar kelime araştırması

**Kelimeleri uydurma. Ölç.**

Depoda gerçek platform verisinden fırsat üreten bir komut var; başlangıç
noktan bu olsun:

```bash
php artisan seo:content-opportunities --json
```

Bunun üstüne dış kaynak ekle:

- **Google Search Console** - sitenin hâlihazırda gösterim aldığı ama
  tıklanmayan sorgular. En yüksek getirili yer burası: Google zaten seni
  o sorguda görüyor, sıralaman yetersiz.
- **Google Trends** - mevsimsellik. Kıbrıs ve Akdeniz mutfağında yaz/kış,
  bayram, Ramazan gibi belirgin dalgalar var.
- **Otomatik tamamlama ve "İlgili aramalar"** - gerçek insan dili.
- **"People also ask" kutuları** - doğrudan `faq` alanına girecek sorular.

Her yazı için şunu yaz ve teslimle birlikte raporla:

```
slug            : hellim-nasil-kizartilir
birincil kelime : hellim nasıl kızartılır
neden bu kelime : GSC'de 1.240 gösterim, 12 tıklama, ortalama sıra 18
rakip           : ilk 3 sonuç forum ve video; yazılı rehber yok
```

Gerekçesi olmayan yazı üretme.

---

## 4. Diller

Hedef diller: **tr, en, pl, fa** (varsayılan `tr`). Lehçe yeni ekleniyor.

> **Önce bunu oku:** Sitede şu anda dil URL'i yok. `/en`, `/pl`, `/fa`
> adresleri 404 dönüyor ve dil değişimi çerezle yapılıyor. Bu hâliyle
> **Türkçe dışındaki hiçbir sayfa arama motorunda görünemez** - Googlebot
> çerez taşımaz. Ayrıntı: [SEO_DENETIMI_2026-08-20.md](SEO_DENETIMI_2026-08-20.md).
>
> Dil önekli URL yapısı kurulana kadar **yabancı dil yazısı üretme.**
> Ürettiğin içerik indekslenmez, emek boşa gider. Önce Türkçe damarı derinleştir.

**Her yazıyı dört dile çevirme.** Makine çevirisi dört katı sayfa üretir,
dört katı bakım yükü getirir. Google'ın 2025 duruşuna göre yapay zekâ
çevirisi kendiliğinden spam değil, ama **ölçekli düşük değerli çeviri**
"scaled content abuse" kapsamına giriyor. Üstelik yardımcı içerik sistemi
site geneli çalışıyor: çok sayıda zayıf dil sayfası, güçlü Türkçe
sayfaların sıralamasını da aşağı çeker.

Her dil **kendi arama niyetinden** başlar, Türkçe yazının çevirisinden değil:

| Dil | Kime yazıyoruz | Örnek niyet |
|---|---|---|
| `tr` | Türkiye ve Kıbrıs'taki ev aşçısı | "hellim nasıl kızartılır" |
| `en` | Kıbrıs'a gelen turist, diasporadaki okuyucu | "what is halloumi", "cyprus food guide" |
| `pl` | Kıbrıs'a charter turizmi olan pazar | "co jeść na cyprze", "halloumi przepis" |
| `fa` | Bölgedeki Farsça konuşan okuyucu | kendi mutfak terimleriyle |

`en` ve `pl` için **turist niyeti** en değerli damar: "Kıbrıs'ta ne yenir",
"hellim nedir", "Kıbrıs kahvaltısı". Hem yüksek hacimli hem de sitenin
gerçekten yetkin olduğu konu.

**Lehçe neden:** Polonya Kıbrıs'a düzenli charter turizmi gönderiyor ve bu
pazar İngilizce içerikle doymuş değil. Rekabet İngilizceye göre belirgin
şekilde düşük.

Çeviri üretiyorsan slug'ı dil ekiyle ayır ve `primary_keyword` alanını
**o dilin gerçek sorgusuyla** doldur.

Kural: **gerçekten iyi yapamayacağın dil sayfasını hiç açma.**

---

## 5. Kalite eşiği

Bir yazı ancak şunların hepsini karşılıyorsa teslim edilir:

- [ ] En az 900 kelime, `##` başlıklarla bölünmüş
- [ ] Birincil kelimenin ölçülmüş bir gerekçesi var (Bölüm 3)
- [ ] Yazı **somut bir şey öğretiyor**: sıcaklık, süre, oran, sıra
- [ ] En az bir yerde sitenin kendi verisine bağlanıyor (tarif bağlantısı)
- [ ] 3-6 gerçek SSS
- [ ] Aynı konuda zaten yayında bir yazı yok (**kannibalizasyon kontrolü**)
- [ ] Sağlık, alerji, beslenme iddiası varsa uyarı satırı var

Son madde ciddi: alerjen ve beslenme yazılarında tıbbi tavsiye verilmez,
hekime yönlendirme cümlesi konur. Depodaki alerjen yazısı bunun örneği.

---

## 6. Kesinlikle yapılmayacaklar

- **Yazı yayınlama.** `status` ve `published_at` alanlarına dokunma.
- **`blog:publish-scheduled` çalıştırma.**
- **Taslak havuzunu toplu yayına alma.** 3.200 sayfalık olay buradan çıktı.
- **Aynı içeriği farklı slug'larla tekrar üretme.**
- **Ölçülmemiş anahtar kelimeyle yazı üretme.**
- **Görsel gömme.** Blog tasarımı görselsiz; kapak görselini sistem yönetir
  ([GOREV_ANTIGRAVITY_GORSEL.md](GOREV_ANTIGRAVITY_GORSEL.md)).
- **PHP veya başka çalıştırılabilir dosya bırakma.** Sunucu teslim klasörüne
  bırakılan `.php` dosyaları reddedilir ve asla çalıştırılmaz.
- **Depoya (yerel diske) yazma.** Teslim yeri Bölüm 1'de.

---

## 7. Teslim sonrası

```bash
# Rapor: ne yazılacağını gösterir, hiçbir şey yazmaz
php artisan blog:apply-editorial-content

# Taslakları oluştur
php artisan blog:apply-editorial-content --apply
```

Çıktıda `yeni taslak: N` satırını ve `N yazı TASLAK olarak açıldı` uyarısını
göreceksin. Bu doğru sonuç.

Ardından raporla:
- kaç yazı teslim edildi
- her birinin birincil kelimesi ve gerekçesi
- hangi dilde
- kannibalizasyon kontrolünün sonucu

**Yayına alma. Rapor et, dur.**

---

## 8. Ölçüm

İş bittiğinde başarı "kaç yazı üretildi" değil. İki hafta sonra bakılacak
olan şu:

- Search Console'da **gösterim** artışı
- Hedeflenen sorgularda **ortalama sıra** değişimi
- Yazı başına **tıklama**

Sıralaması hiç oluşmayan yazı başarısızdır ve silinmesi üretilmesinden
daha değerlidir.
