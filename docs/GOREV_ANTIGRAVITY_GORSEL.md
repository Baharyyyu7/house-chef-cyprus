# Antigravity - Görsel Üretim Görevi

Bu senin görevin. Codex'in görevi ayrı dosyada:
[GOREV_CODEX_TARIF_KAYITLARI.md](GOREV_CODEX_TARIF_KAYITLARI.md). Onun
listesindeki tariflere görsel üretme, henüz hazır değiller.

Tam kural metni: [GORSEL_URETIM_MASTER_PROMPT.md](GORSEL_URETIM_MASTER_PROMPT.md)

---

## Kurallar - kısa hâli

Bu dördü koddaki gerçek kontrollerdir. Uymayan dosya sessizce atlanır,
sunucuda durur, siteye hiç çıkmaz.

| Kural | Değer |
|---|---|
| **En küçük ölçü** | `1600 x 1350` - önerilen üretim `1920 x 1620` |
| **Tarif dosya adı** | `recipe--{tam-slug}.png` |
| **Blog dosya adı** | `{tam-slug}.png` - **önek yok** |
| **Biçim** | PNG |

Üç tuzak, üçü de son teslimatta yaşandı:

1. **`1610x977` yeterli değil.** Genişlik doğru olduğu için "olmuş"
   görünüyor ama yükseklik 373 piksel eksik. 16:9 oranında üreten her
   araç bu tuzağa düşüyor. **Oran 4:3'e yakın olmalı.**

2. **Blog dosyalarında önek yok.** Tarif dosyası `recipe--` ile başlar,
   blog dosyası hiçbir şeyle başlamaz. Geçen sefer simetri varsayılıp
   `blog--` eklendi ve altı dosyanın altısı da içeri giremedi.

3. **Slug'ın sonundaki 5 karakterlik ek uydurulamaz.** Aşağıdaki
   listelerde ekler zaten yazılı, birebir kopyala.

**macOS meta dosyaları:** arşivi `COPYFILE_DISABLE=1 tar -czf ...` ile üret.
Geçen sefer teslim klasörüne 100 tane `._` dosyası karıştı ve taramayı
kirletti.

---

## İş 1 - Yeniden üretilecek 41 tarif görseli

Görsel içeriği doğru, **sadece ölçü yetersiz**. Aynı yemek, aynı dosya adı,
sadece daha büyük üret. Sağdaki sütun mevcut ölçü - hepsi sınırın altında.

| Yemek | Dosya adı | Mevcut ölçü |
|---|---|---|
| Cevizli Tarator Sos | `recipe--cevizli-tarator-sos-V4ZPQ.png` | 1402x1122 |
| Cevizli Tel Kadayıf | `recipe--cevizli-tel-kadayif-7MD0X.png` | 1610x977 |
| Çıtır Sigara Böreği | `recipe--citir-sigara-boregi-69V0Z.png` | 1536x1024 |
| Çıtır Tulumba Tatlısı | `recipe--citir-tulumba-tatlisi-TWJ1Q.png` | 1402x1122 |
| Damla Sakızlı Muhallebi | `recipe--damla-sakizli-muhallebi-RPC0D.png` | 1402x1122 |
| Dereotlu Kuru Bakla Favası | `recipe--dereotlu-kuru-bakla-favasi-W5M66.png` | 1402x1122 |
| Dilber Dudağı | `recipe--dilber-dudagi-JAXKJ.png` | 1396x1127 |
| Düğün Çorbası | `recipe--dugun-corbasi-0JSXP.png` | 1402x1122 |
| El Açması Peynirli Gözleme | `recipe--el-acmasi-peynirli-gozleme-0TZEJ.png` | 1402x1122 |
| El Açması Peynirli Gözleme (2) | `recipe--el-acmasi-peynirli-gozleme-QT22B.png` | 1536x1024 |
| Ermeni Mezesi Topik | `recipe--ermeni-mezesi-topik-F270P.png` | 1402x1122 |
| Etli Kabak Dolması | `recipe--etli-kabak-dolmasi-F18SJ.png` | 1609x977 |
| Ev Yapımı Rus Salatası | `recipe--ev-yapimi-rus-salatasi-DV6RE.png` | 1402x1122 |
| Ezogelin Çorbası | `recipe--ezogelin-corbasi-QC7HS.png` | 1402x1122 |
| Fırın Tavuk | `recipe--firin-tavuk-KWF25.png` | 1402x1122 |
| Fırında Karnabahar Graten | `recipe--firinda-karnabahar-graten-4PH1C.png` | 1402x1122 |
| Fırında Köfte Patates | `recipe--firinda-kofte-patates-FNP91.png` | 1610x977 |
| Fırında Kuzu İncik | `recipe--firinda-kuzu-incik-1FJ1P.png` | 1608x978 |
| Fırında Kuzu Tandır | `recipe--firinda-kuzu-tandir-QJPPE.png` | 1607x979 |
| Fırında Tavuk | `recipe--firinda-tavuk-RCERF.png` | 1609x977 |
| Gaziantep Katmeri | `recipe--gaziantep-katmeri-E8GPM.png` | 1609x977 |
| Geleneksel Aşure | `recipe--geleneksel-asure-AF7HY.png` | 1610x977 |
| Geleneksel Çıtır Lahmacun | `recipe--geleneksel-citir-lahmacun-23A2F.png` | 1610x977 |
| Geleneksel Çoban Salatası | `recipe--geleneksel-coban-salatasi-7R954.png` | 1610x977 |
| Geleneksel Etli Nohut Yemeği | `recipe--geleneksel-etli-nohut-yemegi-RSW7C.png` | 1609x977 |
| Geleneksel Kazandibi | `recipe--geleneksel-kazandibi-EMK9R.png` | 1610x977 |
| Geleneksel Sac Kavurma | `recipe--geleneksel-sac-kavurma-X1KQ3.png` | 1609x977 |
| Hakiki Bal Kaymak Tabağı | `recipe--hakiki-bal-kaymak-tabagi-QD06H.png` | 1610x977 |
| Hatay Usulü Peynirli Künefe | `recipe--hatay-usulu-peynirli-kunefe-FTFQV.png` | 1610x977 |
| Havuç Çorbası | `recipe--havuc-corbasi-VNRE3.png` | 1610x977 |
| Hünkar Beğendi | `recipe--hunkar-begendi-KVTRH.png` | 1610x977 |
| İskender Kebabı | `recipe--iskender-kebabi-VWTF4.png` | 1609x978 |
| İzmir Köfte | `recipe--izmir-kofte-0TZE2.png` | 1608x978 |
| Karışık Meze Tabağı | `recipe--karisik-meze-tabagi-V92P8.png` | 1609x977 |
| Karışık Turşu | `recipe--karisik-tursu-4M9QP.png` | 1607x979 |
| Kayseri Mantısı | `recipe--kayseri-mantisi-3X1QP.png` | 1609x977 |
| Kıbrıs Usulü Fırın Tavuk | `recipe--kibris-usulu-firin-tavuk-3M1QP.png` | 1586x992 |

Şu dört dosyada **ölçüye ek olarak slug eki de eksik**. Doğru slug'ı
tariften okuyup adı tamamla:

| Yemek | Mevcut (eksik) ad | Mevcut ölçü |
|---|---|---|
| Kıbrıs Usulü Fırın Tavuk | `recipe--kibris-usulu-firin-tavuk.png` | 1672x941 |
| Limonlu Akdeniz Makarnası | `recipe--limonlu-akdeniz-makarnasi.png` | 1536x1024 |
| Sebzeli Teriyaki Tavuk | `recipe--sebzeli-teriyaki-tavuk.png` | 1536x1024 |
| Zeytinyağlı Nohutlu Pilav | `recipe--zeytinyagli-nohutlu-pilav.png` | 1536x1024 |

**Teslim yeri:**
`~/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/`

---

## İş 2 - Üretilecek 21 blog görseli

Dosya adı birebir aşağıdaki gibi. **Önek yok, uzantı `.png`, en az
`1600x1350`.**

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

Blog görselleri yazının konusunu anlatan editoryal illüstrasyon; tek bir
yemek fotoğrafı olmak zorunda değil.

**Teslim yeri:**
`~/domains/housechefcyprus.com/shared/storage/app/media-import/blogs/`

---

## Görsel içeriği

- Yemek kadrajın merkezinde ve tanınır olsun. `İskender Kebabı` için
  tabakta iskender, jenerik et fotoğrafı değil.
- Servis edilmiş hâl. Hazırlık aşaması ya da çiğ malzeme değil.
- Doğal ışık, sade zemin, gerçekçi porsiyon.
- Üzerine **yazı, logo, filigran, çerçeve koyma.** Sitenin kendi tipografisi
  var; görsel üstündeki metin çeviriyle birlikte yanlış dile düşer.
- Bir tarife bir görsel. Kolaj yapma.

---

## Teslimden sonra - bu adım zorunlu

```bash
php artisan media:stage-delivered
```

Bu komut hiçbir şey yazmaz, sadece söyler. Çıktı `N dosya hazır, M dosya
atlandı` der.

**M sıfır değilse teslim bitmemiştir.** Her atlanan satır kendi sebebini
yazar; sebebi düzelt, yeniden üret, tekrar çalıştır.

Şimdiye kadar eksik olan adım buydu: atlanan dosyalar sessizce kalıyordu
ve kimse fark etmiyordu.

---

## Sınırın dışı

- Manifest dosyasını elle düzenleme.
- `approval_state` alanını kendin yazma.
- Görseli yayına alma. Onayı `/yonetim/medya?durum=onay` ekranında bir
  insan verir; senin işin ilk kapıyı geçirmek.
