# Doğrulama raporu: 7 taslak tarif

Tarih: 2026-10-12. Girdi `yazim/`, çıktı `dogrulanmis/`. Hiçbir şey yayınlanmadı, sunucuya ve depoya dokunulmadı.

Tüm dosyalarda kontrol edildi:
- Katalog kimlikleri: tüm `ingredient_id` değerleri `katalog_tam.txt` içindeki Türkçe adla eşleşiyor.
- Ölçü kodları: hepsi `katalog.json` içindeki etkin birimlerde var (gram, kilogram, litre, adet, su_bardagi, cay_bardagi, yemek_kasigi, cay_kasigi, demet, dis, dal).
- Kanonik değerler: çay kaşığı 5, yemek kaşığı 15, çay bardağı 100, su bardağı 200 ml; gram/kilogram g; litre ml. Hata yok.
- Malzeme ve adım eşleşmesi: adımda geçen her malzeme listede, listedeki her malzeme bir adımda kullanılıyor.
- Uzun çizgi, akıllı tırnak, "enfes / pratik / afiyet olsun", "Adım N" başlığı: yok. ("pratik" yalnızca macunun kaynak adında geçiyor: Pratik Mutfak.)
- Biçim: JSON `indent=2` ile yeniden yazıldı; anahtarlar ve yapı aynı.

---

## 1. ekmek-kadayifi.json (2 değişiklik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| steps[0] "Kadayıfı ıslat" | "...tepsiyi eğip fazla suyu dökün, dağıtmamak için kadayıfı elinizle hafifçe bastırarak süzün." | "...tepsinin bir kenarını hafifçe yükseltip eğik bırakın, fazla su kendiliğinden süzülünce dökün. Kadayıfı elinizle bastırmayın." | Coğrafi işaret belgesi süzmede elle müdahaleyi açıkça yasaklıyor: "Süzmede kesinlikle el ile müdahale edilmez". | https://ci.turkpatent.gov.tr/Files/GeographicalSigns/200426d5-474b-4c5e-bea4-fc39753001db.pdf |
| kaynaklar, notlar | Belge "okunamadı" diye kaynaklara eklenmemişti; notlarda "kaynakların hiçbiri gram vermiyor" yazıyordu | Belge eklendi, gram oranı notlara yazıldı | Belge metni PDF'ten çıkarılıp okundu: tescil no 473, mahreç işareti, tescil 11.12.2019 | aynı |

Yöntem doğru: kuru disk önce suyla ıslatılıyor, süzülüyor, tepside ocakta sıcak şerbet kenarlardan kepçeyle veriliyor, şerbetin tamamı çekilene kadar kısık ateşte pişiyor, ters çevrilip soğuyor. Bu sıra Küre, Mynet ve İslam ve İhsan'la uyumlu. Ters çevirme Küre'de geçiyor. Coğrafi işaret belgesi de alt yüzü üste gelecek şekilde servis ediyor.

Şerbet ve disk: üç ev tarifi tek bir "orta boy" disk için 3-4 su bardağı şeker ve 4-4,5 su bardağı su kullanıyor. Taslaktaki 3 su bardağı şeker ve 4 su bardağı su bu aralıkta.

Doğrulanamayanlar:
- **Şerbet miktarı diskin ağırlığına bağlı.** Coğrafi işaret belgesi 500 g kuru disk için 2,5 kg şeker ve 2 l su istiyor, yani disk ağırlığının 5 katı şeker, 4 katı su. Bu orana göre taslaktaki şerbetin suyu yaklaşık 200 g'lık, şekeri yaklaşık 120 g'lık bir diske yetiyor. Market disklerinin gramajı bulunamadı. Tarif ev usulü, Afyon usulünden belirgin biçimde daha az şekerli. Diskin gramajı biliniyorsa şerbet bu orana göre ayarlanmalı.
- Belge ve Cumhuriyet kadayıfı kapak kapalı pişiriyor. Taslak kapaksız pişiriyor, Mynet ve İslam ve İhsan da kapak belirtmiyor. Hata sayılmadı, değiştirilmedi.

## 2. lokma-kibris.json (5 değişiklik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| recipe.servings | 6 | 10 | Kopiaste'nin 230 g unlu tarifi 50 lokma veriyor. 500 g un ve patatesle yaklaşık 70-100 lokma çıkıyor, bu da 6 kişiye 12-16 lokma eder. | https://www.kopiaste.org/?p=326 |
| recipe.cook_minutes | 50 | 70 | Hesap: patates 25 dk, yağın ısınması yaklaşık 10 dk, 8-10 parti kızartma yaklaşık 35 dk. Önceki 50 dk tek parti kızartmayı hesaba katıyordu. | Kızartma süresi hükümet yayınından: 3-4 dk/parti |
| Patates | 150 g, "1 orta boy" | 100 g, "1 küçük boy" | Notlar "iki kaynağın ortası" diyordu, ama 150 g iki kaynağın da üstündeydi. 500 g una göre hükümet oranı 86-114 g, Visit Cyprus oranı 125 g ediyor. | https://publications.gov.cy/assets/user/publications/2026/2026_31/HTML/42 , https://www.visitcyprus.com/discover-cyprus/food-drink/local-recipes/loukoumades-honey-dough-balls/ |
| steps[5] "Lokmaları kızart" | "Her seferinde 10-12 lokma" | "Her seferinde 8-10 lokma" | İç tutarlılık. 1 l yağın 5 cm derin olması için tencerenin çapı yaklaşık 16 cm olmalı. Bu genişlikte 10-12 lokma aynı adımın şef ipucundaki "tencereyi doldurmayın" kuralıyla çelişiyor. | iç hesap |
| notlar | | Yukarıdakilerin hesabı eklendi | | |

Doğrulandı: 170-180 °C yağ, 3-4 dk kızartma, 1 saat mayalanma, şerbetin 5 dk kaynatılıp soğutulması, aynı gün ılık servis, bal/tarçın/cevizin süsleme olması. Hepsi hükümet yayınında var ("Traditional dishes of Cyprus", basılı sayfa 42). Patatesin Kıbrıs lokmasının ayırt edici malzemesi olduğu Kopiaste'de geçiyor.

Doğrulanamayanlar:
- Kıbrıs Türk mutfağına özgü bir kaynak yok. İsim "Kıbrıs Lokması", kaynaklar Rum Kıbrıs loukoumades tarifleri.
- Şerbet oranı (hacimce 1:1) iki kaynağın arasında bir seçim, doğrudan bir kaynağı yok.

## 3. kibris-macunu-ceviz.json (7 değişiklik, güvenlik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| steps "Kireçli suda sıkılaştır" instruction | "Kireci tülbente koyup..." Kireç türü ve oran adımda yazmıyordu. | "Cevizleri örtecek kadar taze su ölçün; her litre su için 1 tepeleme yemek kaşığı gıda sınıfı sönmüş kireci (kalsiyum hidroksit) tülbente koyup..." | Kireç türü ve kaynaklı oran adımın kendisinde olmalı | Oran: https://www.kopiaste.org/?p=185 . Kalsiyum hidroksit: https://www.visitcyprus.com/discover-cyprus/food-drink/local-recipes/glyko-karydaki-walnut-sweet-preserve/ |
| aynı adımın chef_tip'i | "Yalnızca gıdaya uygun sönmüş kireç (kalsiyum hidroksit) kullanın ve eldiven takın..." | Ambalajında gıdaya uygun yazan sönmüş kireç kullanın. Sönmemiş kireç (kalsiyum oksit, kesme ya da topaç kireç), nalbur ya da inşaat kireci kullanmayın, çünkü sönmemiş kireç suyla ısınıp sıçrar ve yakar. | Kaynaklardan ikisi tehlikeli kireci öneriyor: mykibris "sönmemiş kireç", yemek.com "sönmemiş topaç kireç" ya da inşaat kireci kaymağı. Açık bir yasak gerekli. | https://www.mykibris.com/haber/kibris%E2%80%99in-ceviz-macunu-nasil-yapilir-puf-noktalariyla-cok-ozel-bir-tarif-3298 , https://yemek.com/tarif/ceviz-receli-2/ |
| yeni adım "Kireçten arındır" (10800 sn) | "akan suyun altında 3-4 kez yıkayın" (haşlama adımının içinde) | Ovarak yıkama ve temiz soğuk suda 1 saat bekletme, toplam üç tur. Yüzeyde beyaz tortu ya da kayganlık kalmamalı. | Kireçli işlemlerde artık kireci uzaklaştırmak için NCHFP kuralı uygulandı: kireçli suyu dök, yıka, temiz suda 1 saat beklet, iki kez daha yinele. | https://extension.illinois.edu/food-preservation/pickling-faq , https://extension.usu.edu/preserve-the-harvest/research/getting-crisp-home-pickled-vegetables.pdf |
| "Yıkayıp üç kez haşla" | başlık ve ilk cümle yıkamayı içeriyordu | "Üç kez haşla"; yıkama yeni adıma taşındı | Yineleme önlendi | |
| "Limonlu suda beklet" | "Limonlu su cevizlere parlaklık ve diri bir doku verir." | cümle silindi | Kaynaksız iddia. mykibris parlaklığı iki kısa haşlamaya bağlıyor. | mykibris |
| recipe.rest_minutes | 11880 | 12060 | Yeni adımın 3 x 1 saatlik bekletmesi eklendi | |
| excerpt, description, malzeme adı, yeni_malzemeler.tr | "kireçle", "kireçli suda"; "Gıda Kireci (Sönmüş Kireç)" | "gıda sınıfı sönmüş kireçle", "kireçten iyice arındırılır"; "Gıda Sınıfı Sönmüş Kireç (Kalsiyum Hidroksit)"; hazırlık notu "tepeleme, her litre suya 1 yemek kaşığı; sönmemiş kireç değil" | Görev kuralı: metin gıda sınıfı sönmüş kireci açıkça adlandırmalı | |

Kireç çevirileri kontrol edildi, doğru. Portekizce "cal hidratada alimentícia (hidróxido de cálcio)" Brezilya'da sönmüş kireç demek. Brezilya'da sönmemiş kireç "cal virgem" diye geçiyor ve çeviride kullanılmamış. Yeşil ceviz çevirileri de doğru.

Doğrulandı:
- 50 ceviz yaklaşık 2 kg (Kopiaste).
- Toplama zamanı mayıs sonu ile haziran ortası arası (Kopiaste).
- Günde iki su değişimi, 6-7 gün (mykibris, kibristatatile, Kopiaste).
- Kireçte 8-10 saat (mykibris, kibristatatile).
- 5 dk, 5 dk, sonra yaklaşık 45 dk haşlama (mykibris).
- Limonlu suda 2 saat (mykibris).
- 1,25 kg şeker, 1,5 su bardağı su ve şekerle bir gece bekletme (mykibris; kibristatatile'nin 100 ceviz oranı da aynı).
- 5-6 saat dinlendirme (kibristatatile).
- 105 °C bitiş (Kopiaste).
- Misafire suyla ikram (mykibris, Pratik Mutfak).

Doğrulanamayanlar:
- Kireçte bekletme süresi kaynaklar arasında tutarsız. Kopiaste aynı oranla 3-4 saat bekletiyor ve bu adımı isteğe bağlı sayıyor. Visit Cyprus 12 saat bekletiyor. Taslağın 8-10 saati korundu. Sönmüş kireç suda düşük oranda çözündüğü için oranın üstü süreden daha az belirleyici, ama bunu doğrulayan Kıbrıs'a özgü bir kaynak yok.
- Notlardaki "ikram geleneği Hürriyet'te geçiyor" iddiası açılıp kontrol edilmedi.

## 4. sigara-boregi-hellimli.json (4 değişiklik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| steps "Börekleri sar" | "kenara 1 tatlı kaşığı harcı" | "kenara harcın 24'te birini (1 tepeleme yemek kaşığı kadar)" | Miktar tutarsızdı. 250 g rendelenmiş hellim ve yumurtadan yaklaşık 550-600 ml harç çıkar. 24 x 10 ml yaklaşık 240 ml eder, harcın yarısı artardı. | iç hesap |
| excerpt | "...çıtırlaşan Kıbrıs usulü sigara böreği." | "...çıtırlaşan sigara böreği." | "Kıbrıs usulü hellimli sigara böreği" hiçbir kaynakta yok. In Your Pocket Kuzey Kıbrıs'taki sigara böreğini beyaz peynir ve maydanozla anlatıyor. | https://www.inyourpocket.com/kyrenia/turkish-cypriot-food-traditional-dishes-to-try-in_79697f |
| description | "Kıbrıs'ta hellimli börekler naneyle yapılır" | "Kıbrıs'ın hellimli böreklerinde nane sık kullanılır" | Tek bir mekân yazısı ve SBS tarifi bu kesin genellemeyi taşımıyor | https://yemek.com/kibris-mekanlari/ , https://www.sbs.com.au/food/recipe/bourekia/a2u498vm5 |
| notlar | | gerekçeler eklendi | | |

Doğrulandı:
- Kızartma sıcaklığı 170 °C olarak adımda yazıyor. Kaynaklarda sayı yok, notlarda bunun genel bilgi olduğu belirtilmiş.
- Fırın alternatifi 200 °C (yemek.com fırında sigara böreği).
- 3 yufkadan 24 börek, kişi başı 4.
- 6 börekten 4 parti x 3 dk, 720 sn ile tutarlı.

Doğrulanamayanlar: "Hellim yüksek ısıda akıp dağılmaz" açıklaması kaynaklarda yok. Hellimin yüksek erime noktası bilinen bir özellik, bu yüzden bırakıldı.

## 5. kibris-usulu-firin-tavuk.json (3 değişiklik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| description | "Kıbrıslı ev aşçılarının tepsi tavuğunda..." | "Bu tepsi tavuğunda... Kıbrıslı aile tariflerini limonlu ve kekikli Yunan usulünden ayıran ayrıntı tarçın, domates ve soğandır." | İki aile tarifinden bütün Kıbrıslı ev aşçıları için genelleme yapılıyordu. Kopiaste Yunan usulünü oregano ve sarımsaklı, annesinin Kıbrıs usulünü tarçın, havuç, domates ve soğanlı anlatıyor. | https://www.kopiaste.org/?p=117 |
| notlar (Meliz Berg) | "tarçın ve kekiği birlikte" | "tarçın ve kuru oreganoyu (rigani) birlikte" | Meliz'in tarifinde 1 tsp kuru oregano var, kekik yok | https://thehappyfoodie.co.uk/recipes/chicken-potato-and-tomato-roast/ |
| notlar (süreler) | Kopiaste "toplam yaklaşık 1,5 saat"; Meliz süresi yoktu | Kopiaste: künyede 1,5 saat, yöntemde yaklaşık 2 saat. Meliz: 35-40 dk folyolu, sonra 50-60 dk açık. | Doğru süreler | aynı |

Doğrulandı:
- 200 °C, önce folyolu sonra açık pişirme (Meliz).
- Meliz'in kitabı Kıbrıs Türk mutfağı ("Turkish-Cypriot comfort food").
- Su miktarı adımlarla tutarlı (1 + 0,5 su bardağı).
- İç sıcaklık 75 °C.

Doğrulanamayanlar:
- Taslağın 40 dk folyolu ve 35-40 dk açık pişirmesi Meliz'in açık süresinden 15-20 dk kısa. Değiştirilmedi, çünkü adımda pişme ölçütü var (berrak su, 75 °C).
- Katalogdaki 257 "Kuru kekik" İngilizcede "dried thyme". Kaynaklardaki ot oregano. Katalog düzeyindeki bu çeviri burada değiştirilemez.

## 6. antalya-usulu-tahinli-piyaz-1xf5w.json (2 değişiklik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| Kuru Fasulye hazırlık notu | "küçük taneli, tercihen Çandır fasulyesi" | "küçük taneli, sıra ya da Çandır fasulyesi" | Küre'ye göre bugün sıra tipi tercih ediliyor. Çandır tarihte kullanılmış, üretimi kısıtlı olduğu için azalmış. Notlar ikisini aynı fasulye sanıyordu ("Çandır (sıra)"). | https://kureansiklopedi.com/tr/detay/antalya-piyazi-df9f3 , https://www.trthaber.com/haber/yasam/100-yillik-tat-antalya-piyazi-artik-tescilli-354463.html |
| notlar | Çandır=sıra varsayımı; "tescil tarihi kaynaklarda farklı" | Düzeltildi. Küre: mahreç işareti, tescil no 315, tescil 29.12.2017. TRT tarih vermiyor (haber 09.03.2018 tarihli). Tahin oranı NTV'deki MasterChef tarifiyle karşılaştırıldı. | | Küre, TRT, NTV (arama sonucu) |

Doğrulandı:
- Sos oranı (Küre: 1 kg tahin, 250 ml sirke, 250 ml limon).
- Tahinin limon ve sirkeyle katılaşıp suyla açılması (yemek.com).
- Antalya'da ana yemek sayılması ve köfteyle sunulması (Küre).
- Üzüm sirkesi.
- 45-60 dk haşlama (yemek.com: ıslatılmış fasulye yaklaşık 45 dk).
- Yarım su bardağı tahin, aralığın alt ucunda. MasterChef oranı 300 g fasulyeye yaklaşık 0,6 su bardağı ediyor.

Doğrulanamayanlar: "Fasulye ılıkken sosla karıştırılırsa sosu daha iyi tutar" kaynaksız bir teknik ipucu. Bırakıldı.

## 7. kiymali-kol-boregi-y4fpg.json (3 değişiklik)

| Alan | Eski | Yeni | Neden | Kaynak |
|---|---|---|---|---|
| description | "Kol böreğinde her yufka sosla ıslatılıp ikiye katlanır... Bu tarifte harç," | "Bu tarifte her yufka... Harç," | Bütün kol böreklerinde yufka katlanmıyor. yemek.com kol böreği yufkayı ikiye kesiyor. | https://yemek.com/tarif/kol-boregi/ |
| steps "Yufkayı sosla katla" | "tezgaha" | "tezgâha" | TDK yazımı. Diğer tariflerdeki "kâse" ve "kâğıt" yazımıyla tutarlı. | |
| notlar | "(3 yufka, 400 g kıyma, 180 derece, 40 dakika)" | 40 dakikanın sayfanın toplam pişirme süresi olduğu, fırın süresinin ayrıca verilmediği yazıldı. Kol böreği 180 °C'de yaklaşık 30 dk. | Yanlış atıf | https://yemek.com/tarif/kiymali-kol-boregi/ |

Doğrulandı:
- 3 yufka, 400 g kıyma, süt-yağ-yumurta sosu, yarım ay katlama (yemek.com).
- Spiral diziliş ve 180 °C (Hürriyet).
- Süreler toplamla tutarlı: harç 15 + fırın 40 = 55; soğuma 15 + dinlenme 10 = 25.

Doğrulanamayanlar:
- Fırında 35-40 dk süresinin doğrudan bir kaynağı yok. yemek.com kıymalı tarif "üzeri kızarana kadar" diyor.
- Sıvı Yağ (45) iki satırda geçiyor. İçe aktarıcının bunu kabul edip etmediği burada denenmedi. Kabul etmiyorsa iki satır tek satırda 130 ml olarak birleştirilmeli.
