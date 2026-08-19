# Görsel Üretim Master Prompt

Bu dosya Codex ve Antigravity'nin House Chef Cyprus için görsel üretirken
uyacağı sözleşmedir. Kurallar burada tarif edilmiyor, **kod tarafından
zorunlu tutuluyor**: her biri `media:stage-delivered` ya da
`content:import-approved-synthetic-blog-media` içinde bir kontrole karşılık
gelir. Kurala uymayan dosya sessizce atlanır, sunucuda durur ve siteye
hiç çıkmaz.

Son teslimatta 141 tarif görselinin **92'si** bu sebeplerle içeri alınamadı.
Bölüm 6 o 92 dosyanın tam listesini veriyor.

---

## 1. Tarif görselleri

### 1.1 Dosya adı

```
recipe--{tarifin-tam-slugu}.png
```

Üç şart birden geçerli:

| Şart | Doğru | Yanlış |
|---|---|---|
| `recipe--` öneki | `recipe--adana-kebabi-MT5XA.png` | `adana-kebabi-MT5XA.png` |
| Slug **birebir** veritabanındaki | `recipe--adana-kebabi-MT5XA.png` | `recipe--adana-kebabi-Y5MTK.png` |
| Sonundaki 5 karakterlik ek | `...-MT5XA.png` | `...-adana-kebabi.png` |

**Sondaki 5 karakterlik eki uydurma.** Bu ek tarifin veritabanındaki
kimliğidir. Yanlış ek yazmak, görselin başka bir yemeğe yerleşme riski
demektir; kod bu yüzden uydurulmuş eki kabul etmez ve dosyayı atlar.

Doğru slug'ı almanın tek yolu tarifin kendi kaydından okumaktır. Tahmin
etme, türetme, eski bir listeden kopyalama.

### 1.2 Ölçü

```
En az 1600 x 1350 piksel
```

Bu bir tavsiye değil, alt sınır. Kod `1600` genişlik ve `1350` yükseklik
altındaki her dosyayı reddeder. Sebebi teknik: site görselden 400/800/1600
piksellik varyantlar üretiyor ve **büyütme yapmıyor**. 1536x1024 bir dosya
1600'lük varyantı üretemez.

En sık yapılan üç hata, hepsi son teslimatta görüldü:

| Üretilen | Neden reddedildi |
|---|---|
| `1536x1024` | İkisi de sınırın altında |
| `1610x977` | Genişlik yeter, **yükseklik 373 piksel eksik** |
| `1402x1122` | İkisi de sınırın altında |

`1610x977` özellikle dikkat: genişlik doğru olduğu için "olmuş" görünüyor.
Olmamış. En-boy oranını 16:9'a yakın tutan her üretim bu tuzağa düşer.

**Hedef oran yaklaşık 4:3.** `1600x1350` tam alt sınır; güvenli üretim için
`1920x1620` ver.

### 1.3 Biçim

PNG. JPEG de kabul edilir ama PNG tercih edilir.

### 1.4 Tarif adı kontrolü

Kod, görselin bağlanacağı tarifin **adının gerçekten bir yemek adı olup
olmadığını** ayrıca kontrol eder. Şu adlara görsel üretme, üretsen de
içeri girmez:

- İçinde `Usulü Limonlu` geçen her ad. `Akdeniz Mutfağı Usulü Limonlu
  Mantar` bir yemek adı değil, bir şablon çıktısıdır. Son teslimatta
  **11 dosya** bu yüzden reddedildi.
- Tek kelimelik ve sıfat olan adlar: `Fırın`, `Fırında`, `Et`, `Tavuk`,
  `Konya`, `Şiş`, `Dürüm`.
- Tek kelimelik ve `lı/li/lu/lü` ile biten adlar (`Zeytinli` gibi) -
  ardındaki isim kaybolmuş demektir.

`Menemen`, `Humus` gibi tek kelimelik gerçek yemek adları geçerlidir.

Böyle bir ada denk gelirsen görsel üretme; **adı raporla**, düzeltilmesi
gereken tarif kaydıdır.

### 1.5 Tarif yayında olmalı

Taslak durumdaki bir tarifin görseli içeri alınmaz. Son teslimatta 7 dosya
bu yüzden bekliyor. Bu senin hatan değil; tarif yayına alınınca görsel
kendiliğinden işlenir. Sadece bilgi olsun.

### 1.6 Teslim yeri

```
~/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/
```

---

## 2. Blog görselleri

### 2.1 Dosya adı - burada üç ayrı hata yapıldı

```
{yazının-tam-slugu}.png
```

Son teslimatta 6 blog görseli üretildi ve **altısı da içeri alınamadı**,
çünkü ad şu haldeydi:

```
blog--kibris-mutfagina-baslangic-rehberi.jpg
```

Beklenen ise buydu:

```
kibris-mutfagina-baslangic-rehberi-65PRZ.png
```

Üç fark var, üçü de tek başına dosyayı reddettirir:

1. **`blog--` öneki fazla.** Tarif dosyalarında `recipe--` öneki var, blog
   dosyalarında **hiçbir önek yok**. İkisi aynı kurala uymuyor.
2. **Slug'ın sonundaki 5 karakterlik ek eksik** (`-65PRZ`).
3. **Uzantı `.jpg` değil `.png` olmalı.**

Bu altı dosya sunucuda dört gün bekledi ve onay ekranında gri kutu olarak
göründü, çünkü kayıt vardı ama dosya yoktu.

### 2.2 Ölçü

Tarif görselleriyle aynı: en az `1600x1350`.

### 2.3 Teslim yeri

```
~/domains/housechefcyprus.com/shared/storage/app/media-import/blogs/
```

---

## 3. Yasak: macOS meta dosyaları

Teslim klasöründe **100 tane** `._recipe--...png` dosyası birikmişti. Bunlar
macOS'un `tar`/`scp` sırasında ürettiği AppleDouble meta dosyalarıdır,
görsel değildir, taramayı kirletir.

Arşiv üretirken:

```bash
COPYFILE_DISABLE=1 tar -czf paket.tar.gz klasor/
```

Sunucuda kalmışsa temizlik:

```bash
find . -name "._*" -delete
```

---

## 4. Teslimden sonra doğrulama

Dosyaları bıraktıktan sonra **her zaman** rapor modunu çalıştır ve çıktıyı
oku. Yazma yapmaz, sadece söyler:

```bash
php artisan media:stage-delivered
```

Çıktı `N dosya hazır, M dosya atlandı` der. **M sıfır değilse teslim
bitmemiştir.** Her atlanan satır kendi sebebini yazar. Sebebi düzelt,
yeniden üret, tekrar çalıştır.

"Yükledim" demeden önce `M = 0` olmalı.

---

## 5. Onay akışı - hiçbir görsel kendiliğinden yayına çıkmaz

Görselin siteye çıkması için iki kapı var:

1. **Otomatik denetim** - yukarıdaki kurallar. Kod uygular.
2. **İnsan onayı** - `/yonetim/medya?durum=onay` ekranında bir kişi
   görsele bakar ve onaylar.

Senin işin birinci kapıyı geçirmek. İkinci kapıyı geçemezsin ve
geçmeye çalışma: manifest dosyasını elle düzenleme, `approval_state`
alanını kendin yazma, onay kaydı üretme.

---

## 6. Şu an düzeltilmesi gereken teslimat

### 6.1 Yeniden üretilecek: ölçü yetersiz (42 dosya)

Aynı slug, aynı içerik, sadece **en az 1600x1350** olacak şekilde.

```
recipe--cevizli-sarma-corek-ZYXMN.png            1536x1024
recipe--cevizli-tarator-sos-V4ZPQ.png            1402x1122
recipe--cevizli-tel-kadayif-7MD0X.png            1610x977
recipe--citir-sigara-boregi-69V0Z.png            1536x1024
recipe--citir-tulumba-tatlisi-TWJ1Q.png          1402x1122
recipe--damla-sakizli-muhallebi-RPC0D.png        1402x1122
recipe--dereotlu-kuru-bakla-favasi-W5M66.png     1402x1122
recipe--dilber-dudagi-JAXKJ.png                  1396x1127
recipe--dugun-corbasi-0JSXP.png                  1402x1122
recipe--el-acmasi-peynirli-gozleme-0TZEJ.png     1402x1122
recipe--el-acmasi-peynirli-gozleme-QT22B.png     1536x1024
recipe--ermeni-mezesi-topik-F270P.png            1402x1122
recipe--etli-kabak-dolmasi-F18SJ.png             1609x977
recipe--ev-yapimi-rus-salatasi-DV6RE.png         1402x1122
recipe--ezogelin-corbasi-QC7HS.png               1402x1122
recipe--firin-tavuk-KWF25.png                    1402x1122
recipe--firinda-karnabahar-graten-4PH1C.png      1402x1122
recipe--firinda-kofte-patates-FNP91.png          1610x977
recipe--firinda-kuzu-incik-1FJ1P.png             1608x978
recipe--firinda-kuzu-tandir-QJPPE.png            1607x979
recipe--firinda-tavuk-RCERF.png                  1609x977
recipe--gaziantep-katmeri-E8GPM.png              1609x977
recipe--geleneksel-asure-AF7HY.png               1610x977
recipe--geleneksel-citir-lahmacun-23A2F.png      1610x977
recipe--geleneksel-coban-salatasi-7R954.png      1610x977
recipe--geleneksel-etli-nohut-yemegi-RSW7C.png   1609x977
recipe--geleneksel-kazandibi-EMK9R.png           1610x977
recipe--geleneksel-sac-kavurma-X1KQ3.png         1609x977
recipe--hakiki-bal-kaymak-tabagi-QD06H.png       1610x977
recipe--hatay-usulu-peynirli-kunefe-FTFQV.png    1610x977
recipe--havuc-corbasi-VNRE3.png                  1610x977
recipe--hunkar-begendi-KVTRH.png                 1610x977
recipe--iskender-kebabi-VWTF4.png                1609x978
recipe--izmir-kofte-0TZE2.png                    1608x978
recipe--karisik-meze-tabagi-V92P8.png            1609x977
recipe--karisik-tursu-4M9QP.png                  1607x979
recipe--kayseri-mantisi-3X1QP.png                1609x977
recipe--kibris-usulu-firin-tavuk-3M1QP.png       1586x992
recipe--kibris-usulu-firin-tavuk.png             1672x941   (ayrıca slug eki eksik)
recipe--limonlu-akdeniz-makarnasi.png            1536x1024  (ayrıca slug eki eksik)
recipe--sebzeli-teriyaki-tavuk.png               1536x1024  (ayrıca slug eki eksik)
recipe--zeytinyagli-nohutlu-pilav.png            1536x1024  (ayrıca slug eki eksik)
```

### 6.2 Adı düzeltilecek: slug eki yanlış (22 dosya)

Görselin kendisi geçerli. **Sadece dosya adını değiştir**, yeniden üretme.
Sol taraf mevcut ad, sağ taraf olması gereken slug:

```
recipe--acili-babaganus-Z3D9S.png                -> acili-babaganus-NJZQA
recipe--adana-kebabi-Y5MTK.png                   -> adana-kebabi-MT5XA
recipe--akdeniz-yassi-970NE.png                  -> akdeniz-yassi-P011Z
recipe--akdeniz-yesillikleri-salatasi-W5PV6.png  -> akdeniz-yesillikleri-salatasi-RFZE4
recipe--akdeniz-zeytinli-G0EZ6.png               -> akdeniz-zeytinli-PF2MF
recipe--ali-nazik-kebabi-981YE.png               -> ali-nazik-kebabi-C0493
recipe--antakya-usulu-kisir-9852K.png            -> antakya-usulu-kisir-3S3N1
recipe--antalya-usulu-tahinli-piyaz-QS9PM.png    -> antalya-usulu-tahinli-piyaz-1XF5W
recipe--avci-kebabi-JE0JW.png                    -> avci-kebabi-87E8S
recipe--babaganus-koz-patlican-salatasi-5FFBY.png-> babaganus-koz-patlican-salatasi-BRQ2J
recipe--baharatli-citir-patates-kizartmasi-H4K4K.png -> baharatli-citir-patates-kizartmasi-PS7H9
recipe--bakir-tavada-sucuklu-yumurta-0SHGD.png   -> bakir-tavada-sucuklu-yumurta-Z7W5B
recipe--balkabagi-corbasi-XC0NG.png              -> balkabagi-corbasi-RDC95
recipe--beyti-30BTS.png                          -> beyti-MJSQ6
recipe--bol-malzemeli-ev-kumpiri-ZBA17.png       -> bol-malzemeli-ev-kumpiri-HHH12
recipe--bolu-usulu-orman-kebabi-XM3GW.png        -> bolu-usulu-orman-kebabi-NKZWM
recipe--brokoli-corbasi-QEVTH.png                -> brokoli-corbasi-Q13XT
recipe--cevizli-baklava-J7PBK.png                -> cevizli-baklava-PZQJ2
recipe--cevizli-gavurdagi-salatasi-FDT3Y.png     -> cevizli-gavurdagi-salatasi-TC8TR
recipe--cig-borek-S0X26.png                      -> cig-borek-J6606
recipe--cig-kofte-YWCS4.png                      -> cig-kofte-A96HF
recipe--cop-sis-QAATK.png                        -> cop-sis-PV7SP
```

Yeni ad: `recipe--{sağdaki-slug}.png`

### 6.3 Görsel üretme: tarif adı geçersiz (11 dosya)

Bu 11 tarifin adı yemek adı değil, şablon çıktısı. Görsel üretme; adların
düzeltilmesi gerekiyor.

```
Akdeniz Mutfağı Usulü Limonlu Biber
Akdeniz Mutfağı Usulü Limonlu Bulgur
Akdeniz Mutfağı Usulü Limonlu Fasulye
Akdeniz Mutfağı Usulü Limonlu Ispanak
Akdeniz Mutfağı Usulü Limonlu Makarna
Akdeniz Mutfağı Usulü Limonlu Mantar
Akdeniz Mutfağı Usulü Limonlu Mercimek
Akdeniz Mutfağı Usulü Limonlu Nohut
Akdeniz Mutfağı Usulü Limonlu Patates
Akdeniz Mutfağı Usulü Limonlu Pirinç
Akdeniz Mutfağı Usulü Limonlu Tavuk fırın tepsisi
```

### 6.4 Bekleyen: tarif kaydı yok (29 dosya)

Dosya adındaki slug hiçbir tarife karşılık gelmiyor. Tarif önce
oluşturulmalı. Görseller sunucuda duruyor, kaybolmadı.

### 6.5 Üretilecek: blog görseli olmayan 21 yazı

Dosya adı **birebir** aşağıdaki gibi olacak. Önek yok, uzantı `.png`,
en az `1600x1350`:

```
sogan-ve-sarimsak-nasil-saklanmali-577WJ.png
corba-kivami-nasil-ayarlanir-Y20XY.png
pilavin-tane-tane-olmasi-nedenleri-ve-cozumleri-2YECA.png
makarna-sosu-secimi-hangi-sos-hangi-makarnaya-C941F.png
firin-yemeklerinde-sicaklik-ve-sure-BY3GS.png
hamur-islerinde-sik-yapilan-hatalar-DKWD9.png
dolaptaki-malzemelerle-yemek-planlama-QDQXV.png
artan-yemekleri-saklama-ve-yeniden-isitma-KGH9H.png
tariflerde-porsiyon-olcekleme-HPH5N.png
alerjen-etiketleri-nasil-okunur-YGAWA.png
besin-degerlerinin-tahmini-olmasi-ne-anlama-gelir-0558R.png
kibris-mezeleriyle-pratik-N3C1V.png
yaz-sicaklarinda-hafif-akdeniz-tarifleri-ZK3QT.png
cooking-mode-ile-adim-adim-ilerleme-7GR0C.png
orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri-KZM3Q.png
firin-dereceleri-rehberi-hangi-yemek-kac-derecede-pisirilir-T0ZQ1.png
kuru-bakliyatlar-buzdolabinda-saklanir-mi-guvelenmeyi-onleme-yontemleri-0QG4N.png
geleneksel-kibris-kebabi-tarifi-ve-evde-yapilis-puf-noktalari-ZPAJ5.png
firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktalari-BH3DY.png
private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi-BH3DZ.png
ev-yapimi-citir-peynirli-kraker-tarifi-pratik-ve-saglikli-atistirmalik-CH32H.png
```

---

## 7. Görsel içeriği

Ölçü ve ad kuralları kadar önemli olmasa da, üretilen görselin işi yemeği
göstermek:

- Yemek kadrajın merkezinde ve tanınır olsun. Tarif adı ne diyorsa o
  görünmeli: `İskender Kebabı` için tabakta iskender, jenerik bir et
  fotoğrafı değil.
- Servis edilmiş hâl - hazırlık aşaması ya da çiğ malzeme değil.
- Doğal ışık, sade zemin, gerçekçi porsiyon.
- Görselin üzerine **yazı, logo, filigran, çerçeve koyma**. Sitenin kendi
  tipografisi var; görsel üstündeki metin hem tekrar eder hem de
  çeviriyle birlikte yanlış dile düşer.
- Bir tarife bir görsel. Kolaj yapma.

---

## 8. Özet kontrol listesi

Teslimden önce her dosya için:

- [ ] Ad kalıbı doğru (`recipe--{slug}.png` / blog için `{slug}.png`)
- [ ] Slug birebir veritabanından, sonundaki 5 karakterlik ek dahil
- [ ] En az `1600x1350`
- [ ] PNG
- [ ] `._` ile başlayan dosya yok
- [ ] `php artisan media:stage-delivered` çıktısında `0 dosya atlandı`
