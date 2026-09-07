# Antigravity - Tarif Görseli Üretim Kataloğu

> Bu dosya **ilerlemenin kaydı**. Her partiden sonra kutucukları işaretle ve
> "Son tamamlanan" satırını güncelle. Kullanıcı "devam et" dediğinde
> işaretsiz ilk partiden devam edilir. Sıra değişmez.


## Durum

- Toplam üretilecek: **27** tarif, **6** parti (parti başına 5)
- Son tamamlanan parti: **yok**
- Sıradaki parti: **Parti 1**
- Onay bekleyen (ÜRETME): 18 tarifin görseli zaten hazır, yönetici onayı bekliyor


## Her partide yapılacaklar

1. Partideki 5 tarifi sırayla üret. Sırayı atlama.
2. Dosyaları `storage/app/media-import/recipes/` altına koy.
3. Manifest satırlarını `storage/app/media-import/approved-media-manifest.json` içine ekle.
4. `php artisan media:intake` ile önce **rapor** al, sonra `--apply` ile yaz.
5. Bu dosyada partinin kutucuklarını işaretle ve "Durum" bölümünü güncelle.
6. Dur ve kullanıcıya rapor ver. Bir sonraki partiye "devam et" denmeden geçme.


## Teknik sözleşme (uyulmazsa içe aktarma reddedilir)

| Kural | Değer |
|---|---|
| Dosya adı | `recipe--<slug>.jpg` - slug birebir aynı olmalı, yoksa `MEDIA_SOURCE_SLUG_MISMATCH` |
| Dizin | `storage/app/media-import/recipes/` |
| Biçim | JPEG, PNG veya WebP |
| Asgari ölçü | 1400x850 piksel **ve** 1.35 megapiksel |
| Önerilen ölçü | **1920x1280** - hero (1600x1000) ve kare (1080x1080) profillerini büyütmeden karşılar |
| Büyütme | Yasak. Küçük görsel `SOURCE_UPSCALE_LIMIT_EXCEEDED` ile reddedilir. |
| Manifest alanları | `content_type: "recipe"`, `public_id`, `source_filename` |

### Görselin içeriği

Kapı yalnız ölçüye bakmıyor; **görsel tarifle uyuşmalı** (`fidelity_status`).
Malzeme listesinde olmayan bir şeyi tabağa koyma. Yanlış yemeğin fotoğrafı
onaya bile gelmeden düşer. Her tarifin malzemeleri aşağıda yazılı.

Ayrıca bu sitede yapay zekâ ile üretilmiş görsel **damgalanır ve öyle etiketlenir**;
gerçek fotoğraf gibi sunma.


---

## Parti 1 · 5 tarif

- [ ] **1. Pratik Lavaş Ekmeği**
  - Dosya: `recipe--pratik-lavas-ekmegi-R91JQ.jpg`
  - `public_id`: `01M041NMPVD6MGYGKHHXMR91JQ`
  - Mutfak / kategori: Akdeniz Mutfağı · Hamur İşi
  - Tarif: Mayasız, hızla hazırlanan, tavada pişen ince Akdeniz lavaşı; tüm yemeklerin yanında mükemmel.
  - Malzemeler (görselde bunlar görünmeli): Un, Tuz, Zeytinyağı
- [ ] **2. Katmer**
  - Dosya: `recipe--katmer-WJXS9.jpg`
  - `public_id`: `01M041NMQ3TV6M0RDWAHHWJXS9`
  - Mutfak / kategori: Türk Mutfağı · Hamur İşi
  - Tarif: Gaziantep'in simgesi; tereyağı ve cevizle hazırlanan, sacda pişen çıtır hamur katmeri.
  - Malzemeler (görselde bunlar görünmeli): Un, Tereyağı, Şeker, Tuz, Ceviz, Yumurta
- [ ] **3. Lor Peynirli Tepsi Böreği**
  - Dosya: `recipe--lor-peynirli-tepsi-boregi-BRJGD.jpg`
  - `public_id`: `01M041NMQXG3Q2B9JDT7CBRJGD`
  - Mutfak / kategori: Türk Mutfağı · Hamur İşi
  - Tarif: Lor peyniri ve maydanozla hazırlanan, sütlü yufkalardan yapılan hafif ve lezzetli tepsi böreği.
  - Malzemeler (görselde bunlar görünmeli): Baklavalık Yufka, Peynir, Yumurta, Süt, Zeytinyağı, Tuz
- [ ] **4. Sütlü Revani**
  - Dosya: `recipe--sutlu-revani-5G74T.jpg`
  - `public_id`: `01M041NMRDZWVRBYS1N425G74T`
  - Mutfak / kategori: Türk Mutfağı · Hamur İşi
  - Tarif: İrmik ve undan yapılan, ılık sütlü şerbetle ıslatılarak servis edilen geleneksel Türk tatlısı.
  - Malzemeler (görselde bunlar görünmeli): Un, Şeker, Yumurta, Süt, Tereyağı, Tuz
- [ ] **5. Yoğurtlu Soğan Halkası**
  - Dosya: `recipe--yogurtlu-sogan-halkasi-KBD7T.jpg`
  - `public_id`: `01M041NMTTHAH0RQXJEJKKBD7T`
  - Mutfak / kategori: Akdeniz Mutfağı · Hamur İşi
  - Tarif: Yoğurt ve undan hazırlanan çıtır hamura batırılmış, yağda kızartılmış soğan halkası.
  - Malzemeler (görselde bunlar görünmeli): Un, Yoğurt, Yumurta, Tuz, Şeker, Soğan

- [ ] Parti 1 içe aktarıldı (`media:intake --apply`) ve bu dosya güncellendi

---

## Parti 2 · 5 tarif

- [ ] **6. Klasik Etli Kuru Fasulye**
  - Dosya: `recipe--klasik-etli-kuru-fasulye-EF3XT.jpg`
  - `public_id`: `01M04GT6TVNS0WQQX3G5VEF3XT`
  - Mutfak / kategori: Türk Mutfağı · Ana Yemek
  - Tarif: Milli yemeğimiz etli kuru fasulye.
  - Malzemeler (görselde bunlar görünmeli): Kuru Fasulye, Kuşbaşı Et, Kuru Soğan, Domates Salçası, Sıvı Yağ
- [ ] **7. Zeytinyağlı Taze Fasulye**
  - Dosya: `recipe--zeytinyagli-taze-fasulye-6VP8P.jpg`
  - `public_id`: `01M04GT6WW0VSQFEHPVPT6VP8P`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Yaz aylarının vazgeçilmezi.
  - Malzemeler (görselde bunlar görünmeli): Taze Fasulye, Domates, Kuru Soğan, Zeytinyağı, Toz Şeker, Tuz
- [ ] **8. Yayla Çorbası**
  - Dosya: `recipe--yayla-corbasi-B91E5.jpg`
  - `public_id`: `01M04JW38SNFH2TTR2MMEB91E5`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Naneli, yoğurtlu nefis yayla çorbası.
  - Malzemeler (görselde bunlar görünmeli): Süzme Yoğurt, Pirinç, Un, Yumurta Sarısı, Kuru Nane, Tereyağı
- [ ] **9. Şehriyeli Tavuk Suyu Çorbası**
  - Dosya: `recipe--sehriyeli-tavuk-suyu-corbasi-51BKY.jpg`
  - `public_id`: `01M04JW3A8PEWS9D5DDAK51BKY`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Şifalı tavuk suyu çorbası.
  - Malzemeler (görselde bunlar görünmeli): Tavuk But, Tel Şehriye, Havuç, Limon Suyu, Tuz
- [ ] **10. İşkembe Çorbası**
  - Dosya: `recipe--iskembe-corbasi-4DGVJ.jpg`
  - `public_id`: `01M04JW3ASXBBTHEF8DM44DGVJ`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Sarımsaklı sirkeli işkembe çorbası.
  - Malzemeler (görselde bunlar görünmeli): Dana İşkembe, Un, Yumurta Sarısı, Sarımsak, Sirke

- [ ] Parti 2 içe aktarıldı (`media:intake --apply`) ve bu dosya güncellendi

---

## Parti 3 · 5 tarif

- [ ] **11. Kremalı Mantar Çorbası**
  - Dosya: `recipe--kremali-mantar-corbasi-7BHF0.jpg`
  - `public_id`: `01M04JW3BA43YGT9FDSH67BHF0`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Kremalı nefis mantar çorbası.
  - Malzemeler (görselde bunlar görünmeli): Kültür Mantarı, Kremsa (Sıvı Krema), Tereyağı, Un, Karabiber
- [ ] **12. Yeşil Mercimek Çorbası**
  - Dosya: `recipe--yesil-mercimek-corbasi-WATFE.jpg`
  - `public_id`: `01M04JW3BWM39K2HEK78YWATFE`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Besleyici yeşil mercimek çorbası.
  - Malzemeler (görselde bunlar görünmeli): Yeşil Mercimek, Arpa Şehriye, Kuru Soğan, Domates Salçası, Sıvı Yağ
- [ ] **13. Tavuklu Erişte Çorbası**
  - Dosya: `recipe--tavuklu-eriste-corbasi-1G79Y.jpg`
  - `public_id`: `01M04JW4DB62PK4DB43R01G79Y`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Şifa deposu tavuklu erişte çorbası.
  - Malzemeler (görselde bunlar görünmeli): Tavuk Göğsü, Erişte, Havuç, Tavuk Suyu, Tereyağı, Karabiber
- [ ] **14. Soğan Çorbası**
  - Dosya: `recipe--sogan-corbasi-B6PTB.jpg`
  - `public_id`: `01M04JW4DX5T1FQ9B6BFCB6PTB`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Karamelize soğanlı enfes çorba.
  - Malzemeler (görselde bunlar görünmeli): Kuru Soğan, Tereyağı, Un, Et Suyu, Kaşar Peyniri
- [ ] **15. Kremalı Patates Çorbası**
  - Dosya: `recipe--kremali-patates-corbasi-YM9K7.jpg`
  - `public_id`: `01M04JW4FAT7RCXARRKY9YM9K7`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Yumuşacık içimli patates çorbası.
  - Malzemeler (görselde bunlar görünmeli): Patates, Krema, Tereyağı, Tavuk Suyu, Karabiber

- [ ] Parti 3 içe aktarıldı (`media:intake --apply`) ve bu dosya güncellendi

---

## Parti 4 · 5 tarif

- [ ] **16. Pancar Çorbası**
  - Dosya: `recipe--pancar-corbasi-XB02F.jpg`
  - `public_id`: `01M04JW4FRDV7MTQVMVYFXB02F`
  - Mutfak / kategori: Türk Mutfağı · Çorba
  - Tarif: Rengiyle büyüleyen pancar çorbası.
  - Malzemeler (görselde bunlar görünmeli): Kırmızı Pancar, Patates, Havuç, Su, Limon Suyu
- [ ] **17. Mersin Usulü Tavuk Tantuni**
  - Dosya: `recipe--mersin-usulu-tavuk-tantuni-2C97T.jpg`
  - `public_id`: `01M04JW4P4CD31EDDHYZX2C97T`
  - Mutfak / kategori: Türk Mutfağı · Ana Yemek
  - Tarif: Mersin usulü tavuk tantuni.
  - Malzemeler (görselde bunlar görünmeli): Tavuk Göğsü, Sıvı Yağ, Toz Kırmızı Biber, Su, Lavaş
- [ ] **18. Pideli Köfte**
  - Dosya: `recipe--pideli-kofte-ETGGS.jpg`
  - `public_id`: `01M04JW4PGS0EDRCR3QM4ETGGS`
  - Mutfak / kategori: Türk Mutfağı · Ana Yemek
  - Tarif: Bursa'nın meşhur pideli köftesi.
  - Malzemeler (görselde bunlar görünmeli): Dana Kıyma, Tırnak Pide, Domates Salçası, Tereyağı, Yoğurt
- [ ] **19. Patlıcan Musakka**
  - Dosya: `recipe--patlican-musakka-7QKTR.jpg`
  - `public_id`: `01M04JW4QRPJ1GCRECEEC7QKTR`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Kıymalı patlıcan yemeği.
  - Malzemeler (görselde bunlar görünmeli): Patlıcan, Dana Kıyma, Kuru Soğan, Sivri Biber, Domates
- [ ] **20. Mercimek Köftesi**
  - Dosya: `recipe--mercimek-koftesi-S57CM.jpg`
  - `public_id`: `01M04JW4R5DSD3T20T175S57CM`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Çay saatlerinin vazgeçilmezi.
  - Malzemeler (görselde bunlar görünmeli): Kırmızı Mercimek, İnce Bulgur, Kuru Soğan, Taze Soğan, Maydanoz

- [ ] Parti 4 içe aktarıldı (`media:intake --apply`) ve bu dosya güncellendi

---

## Parti 5 · 5 tarif

- [ ] **21. Yumurtalı Ispanak Yemeği**
  - Dosya: `recipe--yumurtali-ispanak-yemegi-JWKGN.jpg`
  - `public_id`: `01M04JW4RPTV492C19BQ4JWKGN`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Protein dolu ıspanak yemeği.
  - Malzemeler (görselde bunlar görünmeli): Ispanak, Kuru Soğan, Yumurta, Zeytinyağı, Tuz
- [ ] **22. Kabak Mücveri**
  - Dosya: `recipe--kabak-mucveri-PQNWS.jpg`
  - `public_id`: `01M04JW4S6ST2MXNPEQZGPQNWS`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Kızarmış nefis kabak mücver.
  - Malzemeler (görselde bunlar görünmeli): Kabak, Yumurta, Un, Dereotu, Sıvı Yağ
- [ ] **23. Zeytinyağlı Lahana Sarması**
  - Dosya: `recipe--zeytinyagli-lahana-sarmasi-HD18H.jpg`
  - `public_id`: `01M04JW4SP2S4PMWTGKEBHD18H`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Hafif ve lezzetli lahana sarması.
  - Malzemeler (görselde bunlar görünmeli): Beyaz Lahana, Pirinç, Kuru Soğan, Kuş Üzümü, Dolmalık Fıstık
- [ ] **24. Kıymalı Bezelye Yemeği**
  - Dosya: `recipe--kiymali-bezelye-yemegi-DWHF3.jpg`
  - `public_id`: `01M04JW4T6YPXFYH4NNXZDWHF3`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Klasik ev yemeği etli bezelye.
  - Malzemeler (görselde bunlar görünmeli): Taze Bezelye, Dana Kuşbaşı, Patates, Havuç, Domates Salçası
- [ ] **25. Sote Taze Fasulye**
  - Dosya: `recipe--sote-taze-fasulye-3CRST.jpg`
  - `public_id`: `01M04JW4TN8P2FNFDPN313CRST`
  - Mutfak / kategori: Türk Mutfağı · Zeytinyağlı / Sebze
  - Tarif: Pratik taze fasulye sote.
  - Malzemeler (görselde bunlar görünmeli): Taze Fasulye, Kuru Soğan, Sarımsak, Domates, Zeytinyağı

- [ ] Parti 5 içe aktarıldı (`media:intake --apply`) ve bu dosya güncellendi

---

## Parti 6 · 2 tarif

- [ ] **26. Patlıcan Közlemesi**
  - Dosya: `recipe--patlican-kozlemesi-AMD2T.jpg`
  - `public_id`: `01M04JW4V4Z05Z2FPSJHBAMD2T`
  - Mutfak / kategori: Türk Mutfağı · Salata / Meze
  - Tarif: Köz kokulu patlıcan salatası.
  - Malzemeler (görselde bunlar görünmeli): Patlıcan, Kapya Biber, Sarımsak, Zeytinyağı, Limon
- [ ] **27. Yeşil Mercimek Salatası**
  - Dosya: `recipe--yesil-mercimek-salatasi-PG4NP.jpg`
  - `public_id`: `01M04JW4W2T5PDM2YQHBWPG4NP`
  - Mutfak / kategori: Türk Mutfağı · Salata / Meze
  - Tarif: Besleyici doyurucu salata.
  - Malzemeler (görselde bunlar görünmeli): Yeşil Mercimek, Dereotu, Maydanoz, Kırmızı Köz Biber, Nar Ekşisi

- [ ] Parti 6 içe aktarıldı (`media:intake --apply`) ve bu dosya güncellendi

---

## Üretme: onay bekleyenler

Bu tariflerin görseli zaten hazır ve uyum denetimini geçmiş; yalnız yönetici
onayı bekliyorlar. Yeni görsel üretmek kopya yaratır.

- Sarımsaklı Zeytinyağlı Makarna (`sarimsakli-zeytinyagli-makarna`)
- Yoğurtlu Yulaf Kasesi (`yogurtlu-yulaf-kasesi`)
- Peynirli Omlet (`peynirli-omlet`)
- Tarhana Çorbası (`tarhana-corbasi`)
- Yoğurtlu Buğday Çorbası (`yogurtlu-bugday-corbasi`)
- Mantarlı Risotto (`mantarli-risotto`)
- İmam Bayıldı (`imam-bayildi`)
- Zeytinyağlı Enginar (`zeytinyagli-enginar`)
- Zeytinli Açma (`zeytinli-acma`)
- Lahmacun (`lahmacun`)
- Sebzeli Kiş (`sebzeli-kis`)
- Sütlaç (`sutlac`)
- Revani (`revani`)
- Yoğurtlu İncir Tatlısı (`yogurtlu-incir-tatlisi`)
- Limonlu Semifreddo (`limonlu-semifreddo`)
- Tabule (`tabule`)
- Yunan Usulü Fasulye Salatası (`yunan-usulu-fasulye-salatasi`)
- Zaatarlı Pide Dilimleri (`zaatarli-pide-dilimleri`)
