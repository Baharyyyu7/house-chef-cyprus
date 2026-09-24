# Codex - Tarif Kaydı Görevi

Bu senin görevin. Antigravity'nin görevi ayrı dosyada:
[GOREV_ANTIGRAVITY_GORSEL.md](GOREV_ANTIGRAVITY_GORSEL.md). Sen görsel
üretmiyorsun; **görsellerin bağlanacağı tarif kayıtlarını hazırlıyorsun.**

Tam kural metni: [GORSEL_URETIM_MASTER_PROMPT.md](GORSEL_URETIM_MASTER_PROMPT.md)

---

## Durum

Sunucuda 119 tarif görseli duruyor. 49'u siteye çıktı. Kalanların
çoğunluğu **görsel hatası yüzünden değil, tarif kaydı hazır olmadığı için**
içeri giremiyor:

| Sebep | Adet | Kimin işi |
|---|---|---|
| Ölçü yetersiz | 41 | Antigravity |
| **O slug ile tarif yok** | **29** | **Sen** |
| **Tarif adı yemek adı değil** | **11** | **Sen** |
| **Tarif taslak durumda** | **7** | **Sen** |

---

## İş 1 - Adı bozuk 11 tarif

Kod, görselin bağlanacağı tarifin adının gerçekten bir yemek adı olup
olmadığını kontrol ediyor (`Recipe::titleDescribesADish`). Bu 11 ad
şablon çıktısı, yemek adı değil:

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

Hepsi tek bir üretim şablonundan çıkmış: `Akdeniz Mutfağı Usulü Limonlu`
+ malzeme adı. Kod `Usulü Limonlu` geçen her adı reddediyor.

**Karar senin değil.** Bu tarifler:
- gerçek bir yemeğe karşılık geliyorsa adı düzeltilmeli
  (`Limonlu Zeytinyağlı Ispanak` gibi gerçek bir yemek adı),
- karşılık gelmiyorsa yayından kaldırılmalı.

Hangisi olduğuna karar vermeden önce içeriklerine bak: malzeme ve adım
listesi gerçek bir tarif mi, yoksa o da mı şablon?

Bulgularını raporla, toplu yeniden adlandırma yapma. Tek bir seeder
çalışması daha önce 678 tarifin 628'inin slug'ını değiştirip teslim edilmiş
görselleri canlıdan kopardı.

---

## İş 2 - Kaydı olmayan 29 tarif

Bu 29 slug için görsel teslim edilmiş ama karşılığında tarif kaydı yok.
Görseller sunucuda duruyor, kaybolmadı; tarif oluşturulduğu anda
kendiliğinden işlenecekler.

Çoğu gerçek ve tanınmış yemekler - özellikle Kıbrıs mutfağından
olanlar sitenin ana konusu:

```
antalya-piyazi-0GXHZ              kibris-kolokas-4P9LK
arnavut-cigeri-UQPPT              kibris-macunu-ceviz-7L1XP
asure-3VTCZ                       kibris-molohiya-8L1XP
ayran-asi-corbasi-GYXDL           kibris-pisisi-5P1XK
ayva-tatlisi-AUZST                kibris-tatlisi-2M7LK
baklava-VH8CT                     kibris-usulu-firin-makarna-1Q9LP
bamya-corbasi-7XT9H               kibris-usulu-humus-8X1QP
beyran-corbasi-FGD8R              kibris-usulu-kleftiko-9X3QP
biber-dolmasi-0T1YN               hellimli-borek-8M3QP
borek-cesitleri-MDSV2             incir-tatlisi-1H09T
bulgur-pilavi-6GZ3M               irmik-helvasi-5S8RP
burma-kadayif-4K72X               kabak-tatlisi-81NQP
cilbir-P5K7M                      karnabahar-graten-7M2QP
coban-salatasi-Y1V8H              karniyarik-9P7LQ
                                  keskul-8M2LP
```

**Önce şunu ayır:** bu slug'lardan bazıları zaten var olan bir tarifin
farklı yazımı olabilir (`asure-3VTCZ` ile `geleneksel-asure-AF7HY`,
`coban-salatasi-Y1V8H` ile `geleneksel-coban-salatasi-7R954`,
`karnabahar-graten-7M2QP` ile `firinda-karnabahar-graten-4PH1C`). Bunlar
yeni tarif değil, **çift kayıt riski**.

Her slug için üç ihtimalden hangisi olduğunu belirle:
1. Var olan bir tarifin kopyası → yeni tarif açma, görseli mevcut tarife bağla
2. Gerçekten eksik bir tarif → tarifi oluştur ve yayınla
3. Anlamsız/şablon → görseli reddet

Sondaki 5 karakterlik ekler mevcut kayıtlarla eşleşmiyor; bunlar üretim
sırasında uydurulmuş olabilir. Eşleştirmeyi **yemek adı üzerinden** yap,
ek üzerinden değil.

---

## İş 3 - Taslak durumdaki 7 tarif

Görselleri hazır ve kurallara uygun, sadece tarif yayında değil:

```
Acılı Babaganuş
Akdeniz Yeşillikleri Salatası
Avcı Kebabı
Bol Malzemeli Ev Kumpiri
Bolu Usulü Orman Kebabı
+ 2 tane daha
```

Tarif içeriği tamamsa yayınla; görsel aynı gün içeri girer. Eksikse neyin
eksik olduğunu raporla.

---

## Yapmayacakların

- **`media:stage-delivered --rename-to-current-slug` çalıştırma.** Bu
  seçenek dosyaları veritabanındaki slug'a taşır. Yanlış veritabanına
  bakarak çalıştırılırsa görselleri hiçbir tarifi göstermeyen adlara taşır
  ve geri alınamaz. Bu komut kullanıcının açık kararıyla çalışır.
- **Toplu slug yeniden yazımı yapma.** Bir kez 628 tarifin slug'ı toplu
  değişti ve teslim edilmiş görsellerin tamamı bir anda eşleşmez oldu.
- **Manifest dosyasını elle düzenleme.**
- **Görsel yayına alma.** Onayı `/yonetim/medya?durum=onay` ekranında bir
  insan verir.
- **Taslak havuzundaki blog yazılarını toplu yayınlama.** Bir kez ~3200
  makine üretimi sayfa canlıya çıktı.

---

## Doğrulama

Her değişiklikten sonra:

```bash
php artisan media:stage-delivered
```

Çıktıdaki `M dosya atlandı` sayısının **düştüğünü** görmelisin. Düşmüyorsa
yaptığın değişiklik sorunu çözmemiş demektir.
