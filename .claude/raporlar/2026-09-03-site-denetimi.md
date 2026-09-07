# Site Denetimi - 3 Eylül 2026

Ölçüm tabanlı. Her sayı ya üretim veritabanından ya canlı sayfadan ya da
kaynaktan alındı; tahmin yok. Ölçemediğim yerler açıkça işaretli.

---

## 1. Sağlam olan, dokunmaya gerek yok

Denetimden temiz çıkan alanlar - buraları arayıp bulmaya çalışmak zaman kaybı.

| Alan | Ölçüm |
|---|---|
| Bağlantı bütünlüğü | 0 kırık, 0 öksüz sayfa |
| Karşılanmamış arama | 0 (gürültü eşiğin altında ayrı sayılıyor) |
| Varlık teslimi | 0 hatalı |
| Yedek eksiksizliği | 0 eksik tablo |
| Veritabanı sözleşmesi | 0 kanıtlanmış taşma |
| Tarif içeriği | 0 yer tutucu malzeme, 0 yer tutucu adım, 0 başlık çelişkisi |
| Hub sayfa kapsamı | **eksiksiz** (aşağıda) |

### Hub sistemi eksiksiz

33 malzeme sayfa eşiğini (≥5 tarif) geçiyor. 15'i temel malzeme olduğu için
küratörlü listeyle eleniyor - "Tuz", "Su", "Un", "Şeker" sayfası açmak tam da
bu sitenin ceza yediği türden zayıf sayfa üretirdi. Kalan **18'in 18'i de
yayında.** Mutfak (5/5) ve kategori (8/8) de tam.

**Sonuç: programatik sayfa üretiminde boşluk yok.** Daha fazla hub sayfası
istiyorsanız yol kod değil içerik: tarif sayısı arttıkça yeni malzemeler eşiği
geçer ve sayfaları kendiliğinden açılır.

---

## 2. Gerçek kusur: CSS'te çift tanım

Bu oturumda **iki ayrı hatayı** bu üretti, ikisi de canlıda görünür kusurdu.

| Ölçüm | Değer |
|---|---|
| Birden fazla kez tanımlı seçici | **65** |
| Katman sınırını aşan (tehlikeli olan) | 20 → **18** (2'si düzeltildi) |
| Tamamen ölü olduğu kanıtlanan | 2 - silindi |

`@layer components` içindeki tanım, aynı seçicinin katmansız tanımına yenilir:
CSS basamağında katmansız stiller en yüksek önceliğe sahiptir.

**Düzeltme (3 Eylül):** ilk raporda "katmanlı blok tamamen ölü kod" yazmıştım.
Bu fazla kesindi. CSS basamağı **bildirim bazında** çalışır: katmanlının
bildirdiği ama katmansızın bildirmediği özellik hâlâ geçerlidir. Tek tek
ölçtüm - 20 çakışmanın yalnız **2'si** her özelliğinde eziliyordu
(`.hc-public-header`, `.hc-mobile-drawer`). Onları sildim, görünüm değişmedi.

Kalan **18'i `@apply` taşıyor** ve bildirdiği bazı özellikler etkili. Körü
körüne silmek ya da birleştirmek tipografiyi bozar - sitenin en görünür
parçası. Bu yüzden otomatik düzeltmedim.

Örnek - tipografi ölçeği iki kez tanımlı:

```
.hc-display-xl  KATMANLI   @apply text-5xl ... lg:text-7xl;            <- font-family hâlâ etkili
.hc-display-xl  KATMANSIZ  font-size: clamp(2.75rem, 6.2vw, 4.75rem);  <- font-size'ı bu belirliyor
```

**Yapılan:** `CssLayerConflictRatchetTest` eklendi. Mevcut 18 çakışmayı
sabitliyor ve **yenisi eklenirse testi kırıyor.** Ayrıca bir çakışma
düzeltildiğinde listeden çıkarılmasını zorunlu kılıyor, böylece kilit gevşemiyor.

Bu hata sınıfı bu oturumda iki kez canlı kusur üretti: `.hc-v3-card`'ın
katmansız kopyası `height:100%` verip yönetim panelinde 249 karakterlik kartı
2.829 piksele şişirmişti; `--interactive` de -3px/-4px diye çelişiyordu.

**Kalan iş:** 18 çakışmayı teker teker birleştirmek. Her biri görsel doğrulama
istiyor; Codex'in "tasarım-token tutarlılığı" görevine somut hedef listesi
olarak verilebilir.

---

## 3. İçerik eksikleri (kod değil, üretim işi)

| Eksik | Sayı | Kim yapacak |
|---|---|---|
| Onaylı görseli olmayan yayındaki tarif | 45 | ↓ ikiye ayrılıyor |
| → görseli hazır, **onay bekliyor** | **18** | **siz** (`/yonetim/medya`) |
| → görsel üretilecek | 27 | Antigravity (katalog hazır) |
| Çeviri engelli tarif | 47 | sebebin tamamı yukarıdaki görsel eksiği |
| İnce blog yazısı | 6 | siz - şu an dizin dışı, acil değil |
| Alerjen eşleşmesi | **0 / 187 malzeme** | siz (aşağıda) |

**Çeviri sayısı ayrı bir iş değil.** 47 engelin 47'sinin de tek sebebi onaylı
görsel yokluğu. Görseller çözülünce çeviri kuyruğu kendiliğinden açılır.

**En yüksek getirili tek hamle: 18 onay.** Yeni görsel üretmeden görsel eksiğini
45'ten 27'ye, çeviri engelini de aynı oranda düşürür.

---

## 4. Alerjen sistemi boş

14 alerjen tanımlı, **0 malzeme eşleşmesi**, **0 tarif işareti**.

İyi haber: site bunu dürüstçe anlatıyor. Tarif sayfası "alerjensiz" iddiasında
bulunmuyor, feragat notu yerinde ve kalite paneli artık %100 yerine "ölçülmedi"
diyor. Yani **güvenlik açığı yok, veri eksiği var.**

Bu bir yemek sitesi için en büyük yapısal boşluk. 187 malzemenin alerjen
eşleşmesi girildiğinde:
- tarif sayfalarında gerçek alerjen uyarısı çıkar
- diyet uygunluğu "belirsiz" olmaktan çıkar
- kalite skorunun ölçülen ağırlığı %78'den %95'e çıkar
- "glutensiz tarifler", "laktozsuz tarifler" gibi yeni koleksiyon sayfaları
  mümkün olur (şu an veri olmadığı için açılamıyor)

Editoryal bir iş; sizin kararınız. Karar verirseniz veri girişi için ayrı bir
katalog hazırlayabilirim.

---

## 5. Performans

| Varlık | Ham | Sıkıştırılmış (gerçek transfer) |
|---|---|---|
| CSS | 184 KB | **31 KB** |
| JS | 53 KB | **18 KB** |

Gerçek transfer iyi durumda. CSS ham boyutu büyük ama gzip sonrası 31 KB kabul
edilebilir. Yine de baskın varlık CSS ve 65 çift tanımın bir kısmı doğrudan
oraya yazıyor - madde 2 çözülünce burası da küçülür.

Sayfa yapısı ölçüldü: görsellerin hepsinde `width`/`height` var (düzen kayması
yok), hero `fetchpriority="high"`, reklam betiği `async`, modül betiği ertelenmiş.

---

## 6. Ölçemediklerim

Dürüstlük payı - bunları doğrulayamadım:

- **Kaydırılmış başlığın görünümü.** Otomasyon sekmesi `hidden` açıldığı için
  `IntersectionObserver` ve `requestAnimationFrame` kısıtlı çalışıyor; gözcü
  öğesinin canlıdaki davranışını gözle teyit edemedim. CSS ve DOM doğru.
- **Yönetim panelinin görsel son hali.** Sınıfların doğru basıldığını
  doğruladım, piksel ölçemedim.

İkisi de sizin bir bakışınızla kapanır.

---

## 7. Öncelik sırası

1. **18 medya onayı** - sizde, en yüksek getiri, yeni üretim gerektirmiyor
2. **20 CSS çakışması** - bir hata sınıfını kalıcı kapatır, ölçülü hedef listesi var
3. **27 görsel üretimi** - Antigravity, katalog ve parti planı hazır
4. **Alerjen verisi** - en büyük yapısal boşluk, editoryal karar
5. 6 ince blog yazısı - dizin dışı oldukları için acil değil
