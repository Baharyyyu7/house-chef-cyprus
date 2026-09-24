# Antigravity - Ana Sayfa Hero Videosu

Ana sayfanın tepesindeki kahraman görselin üzerine binecek kısa döngü
videosu. Site altyapısı hazır: dosyayı doğru ada ve klasöre koyduğun anda
kendiliğinden devreye girer. Dosya gelene kadar site bugünkü hâliyle çalışır.

Görsel üretim kuralları ayrı dosyada:
[GOREV_ANTIGRAVITY_GORSEL.md](GOREV_ANTIGRAVITY_GORSEL.md)

---

## 1. Neden bu kadar kısıtlı

Şu anki hero görseli ölçülmüş değerlerle:

| | Boyut |
|---|---|
| Masaüstü (AVIF) | 221 KB |
| Mobil (AVIF) | 60 KB |

Bu görsel sayfanın LCP elemanı ve `fetchpriority="high"` ile öne alınmış.
Video onun yerine geçmiyor, **üstüne biniyor**: görsel yine ilk boyanan şey,
video sayfa yüklendikten sonra sessizce açılıyor.

Bu yüzden dosya boyutu bir tercih değil, sınır. Aşarsan video devreye
girmez ya da sayfayı yavaşlatır.

---

## 2. Teknik şartname

| Alan | Değer |
|---|---|
| **Dosya adı** | `hero-loop.mp4` ve `hero-loop.webm` |
| **Klasör** | `public/videos/` (sunucuda `public_html/videos/`) |
| **Çözünürlük** | 1920 x 1024 (hero görselle aynı en-boy: 1717x916 ≈ 1.875:1) |
| **Süre** | 6 - 8 saniye |
| **Kare hızı** | 25 fps |
| **Boyut sınırı** | **MP4 en fazla 2 MB, WebM en fazla 1.6 MB** |
| **Ses** | **Ses kanalı hiç olmayacak** (izi bile) |
| **Döngü** | Son kare ilk kareye kusursuz bağlanacak |
| **Codec** | MP4: H.264 High, WebM: VP9 |
| **faststart** | MP4'te `moov` atomu başta olacak |

Kodlama komutları:

```bash
# MP4 (H.264), sessiz, faststart
ffmpeg -i kaynak.mov -an -c:v libx264 -profile:v high -crf 30 \
  -preset slow -pix_fmt yuv420p -r 25 -vf "scale=1920:1024" \
  -movflags +faststart hero-loop.mp4

# WebM (VP9), sessiz
ffmpeg -i kaynak.mov -an -c:v libvpx-vp9 -crf 36 -b:v 0 \
  -r 25 -vf "scale=1920:1024" hero-loop.webm
```

Boyut tutmuyorsa önce `-crf` değerini yükselt (30 → 34), sonra süreyi
kısalt. **Çözünürlüğü düşürme** - ekranda tam genişlik kaplıyor.

---

## 3. Sahne - en önemli bölüm

### 3.1 Bu bir yemek tanıtımı değil, hareket katmanı

Video metnin arkasında, koyu bir perdenin altında duruyor. Kimse onu
seyretmiyor; sayfaya hayat katıyor. Bu yüzden **sahne sakin olmalı.**

Üretken video hâlâ yemekte kendini ele veriyor: eriyen kenarlar, yanlış
buhar fiziği, kendi kendine dönen malzemeler, elin parmak sayısı. Duran bir
görselde fark edilmeyen bu kusurlar harekette hemen görünür ve yemek
sitesinde güveni doğrudan zedeler.

**Çözüm: kusurun görünemeyeceği çekimleri seç.**

### 3.2 Yap

- **Yavaş kamera kayması.** Sabit bir masa üstünde çok yavaş yatay kaydırma
  ya da hafif yakınlaşma. Nesneler hareket etmiyor, sadece kamera.
- **Odak kayması.** Ön plandaki zeytinden arkadaki hellime odak geçişi.
- **Buhar.** Sıcak tabaktan yükselen ince buhar - yavaş, dağınık, az.
- **Işık oyunu.** Pencereden gelen ışığın yaprak gölgesiyle çok hafif
  kıpırdaması.
- **Sığ alan derinliği.** Arka planın yumuşak olması hem güzel hem de
  yapay zekâ kusurlarını gizler.

### 3.3 Yapma

- **Ellerle hazırlık yapma.** Doğrayan, karıştıran, servis eden el - yapay
  zekânın en çok battığı yer.
- **Akan sıvı yapma.** Dökülen zeytinyağı, akan sos: fizik yanlış çıkıyor.
- **Ateş, cız cız pişen et, tavada zıplayan malzeme yapma.**
- **Hızlı kesme, geçiş efekti, zoom patlaması yapma.** Tek çekim, tek
  hareket.
- **Yazı, logo, filigran koyma.**
- **İnsan yüzü koyma.**

### 3.4 İçerik ve renk

Sahne mevcut hero görselinin dünyasıyla aynı olmalı, çünkü video onun
üzerine biniyor ve geçiş göze çarpmamalı:

- Kıbrıs/Akdeniz sofrası: ızgara hellim, zeytin, kiraz domates, pide,
  kekik, toprak tabak, keten örtü.
- Sıcak ve koyu ton. Arka plan koyu, yemek sıcak ışık altında.
- **Kadrajın sol yarısı sade kalmalı** - başlık ve düğmeler orada duruyor.
  Yoğunluk sağ tarafta olsun, tıpkı şu anki görselde olduğu gibi.

### 3.5 Döngü

Video sonsuz dönecek. Son kare ilk kareye takılmadan bağlanmalı.

En kolay yolu: tek yönlü yavaş bir hareket üret, sonra kendisinin tersiyle
birleştir (ping-pong). Kamera gidip geri gelir, ek yeri görünmez.

```bash
# ping-pong dongu
ffmpeg -i ileri.mp4 -vf reverse geri.mp4
ffmpeg -f concat -safe 0 -i <(printf "file '%s'\nfile '%s'\n" "$PWD/ileri.mp4" "$PWD/geri.mp4") -c copy dongu.mp4
```

---

## 4. Teslim

```
public/videos/hero-loop.mp4
public/videos/hero-loop.webm
```

Sunucuda hem release klasörüne hem `public_html/videos/` altına gitmeli;
web kökü ayrı bir dizin ve release'in `public` klasörüne bağlı değil.

Teslimden sonra kontrol:

```bash
# Boyut ve sure
ffprobe -v error -show_entries format=duration,size -of default=noprint_wrappers=1 hero-loop.mp4

# Ses kanali olmadigini dogrula - cikti BOS olmali
ffprobe -v error -select_streams a -show_entries stream=codec_type -of csv=p=0 hero-loop.mp4
```

Ses akışı çıkıyorsa dosya reddedilir: sessiz otomatik oynatma için ses
kanalının hiç olmaması gerekiyor, sadece susturulmuş olması yetmez.

---

## 5. Video ne zaman oynar

Altyapı şu koşulların **hepsini** arıyor. Biri bile tutmazsa görsel
gösterilir, video hiç indirilmez:

- Ekran genişliği 1024px ve üzeri
- `prefers-reduced-motion` kapalı
- Tarayıcı `Save-Data` istemiyor
- Bağlantı yavaş değil (2g/3g değil)
- Dosya sunucuda mevcut

Yani mobilde video hiç yüklenmez; mobil kullanıcı 60 KB'lik görselde kalır.
Bu bilinçli bir karar: hero süsü için kimsenin mobil verisi harcanmıyor.

---

## 6. Onay

Video ana sayfanın en görünür yeri. Görsellerdeki gibi otomatik bir onay
kuyruğu yok, o yüzden kural açık:

**Ürettiğin dosyayı doğrudan yayına koyma.** Önce üret, kontrol komutlarını
çalıştır, sonucu raporla. Yayına alma kararını kullanıcı verir.

Yapay zekâ ile üretildiği bilgisini de raporla; sitenin görsellerde
uyguladığı şeffaflık kuralı burada da geçerli.
