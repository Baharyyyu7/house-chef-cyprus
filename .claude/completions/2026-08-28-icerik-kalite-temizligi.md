# İçerik kalite temizliği - 2026-08-28

Zamanlayıcı 2026-08-02'den beri ölü olduğu için `seo:audit` 26 gündür hiç
koşmamıştı. Elle koşturulunca **124 açık sorun, skor 0** çıktı.

## Bulunanlar ve yapılanlar

### 1. Kapıdan geçmeden yayına girmiş 7 blog yazısı (error)
Hepsi 2026-08-16 tarihli, yani spam olayından kalma. `editorial_status`
onaylı ama `fact_check_status` ve `originality_status` hâlâ "pending".

Bayrakları "geçti" yapmak sahte inceleme olurdu; kontroller gerçekten
yapıldı:

- **Mercimek çorbası → taslağa alındı.** Yazı yarıda kesilmiş (malzeme
  listesi var, yapılışı yok), 404 veren "Bölüm 2" bağlantısı taşıyor,
  "modern gastronomi bilimi kanıtlıyor" diyerek olmayan araştırmaya
  dayanıyor ve "Google'da parlayacak" cümlesi okura görünen SEO metni.
  `fact_check_status = failed`.
- **Kıbrıs kebabı → düzeltildi.** "Kuzu gomen (terbiyeli gömlek yağı)"
  yanlıştı; gomen Kıbrıs Türkçesinde bir yeşil sebze, köftenin sarıldığı
  zar gömlek yağı. Aynı depodaki şeftali kebabı tarifi doğru terimi
  (banna) kullanıyor.
- **Bakliyat saklama → iki aşırı iddia yumuşatıldı.** "Güvelenmeyi
  tamamen engeller" ve "defne yaprağı nemlenmeyi önler" düzeltildi;
  yoğuşma uyarısı eklendi.
- **Fırın dereceleri → dizgi hatası** ("Sebze Y Yemekleri").
- **Ön ısıtma ve peynirli kraker → içerik doğru**, değişiklik gerekmedi.
- **Private Chef sayfası bilinçli atlandı.** Bu editoryal içerik değil,
  hizmet tanıtımı; hizmetin gerçekten verilip verilmediği doğrulanamadığı
  için olgu kontrolü "geçti" işaretlenmedi. Denetimdeki tek açık hata bu.

### 2. Yapılışı olmayan 38 tarif (yayındaki 215'in %18'i)
Adımlar yer tutucu ("Gereken şekilde pişirin."), ve daha kötüsü **152
malzeme satırının 152'si de "1 paket"** - gerçek miktar yok. Hepsi tam 4
malzemeli; Hünkar Beğendi'de beşamel için un ve tereyağı bile yok.

Bunlar tarif değil, şablon taslağı. Süsleyip "doğrulanmış tarif" diye
sunmak 3.649 sayfalık spam olayının aynısı olurdu. **Yayından alındılar**
(silinmediler, taslak duruyorlar). Yayındaki tarif 215 → 177.

Malzeme sayfaları eşiği kendiliğinden yeniden hesapladı: 21 → 18. Sitemap
ile liste senkron kaldı (bunun testi zaten vardı).

### 3. 79 ince tarif açıklaması
Her tarifin kendi malzeme ve adım verisine dayanarak 130-260 karakter
arası, birbirinden farklı açıklamalar yazıldı. Uydurma iddia yok, şablon
yok, tekrar yok (doğrulandı).

### 4. Çift içerik: üç kayıt birbirine girmiş
- `atom-acili-ezme-mezesi-W1NNC` → **"Atom Mezesi"** (asıl Atom: yoğurt +
  Arnavut biberi)
- `atom-acili-ezme-mezesi-3VD3J` → **"Acılı Ezme"** (yemek zaten buydu,
  başlığı yanlıştı)
- `cevizli-muhammara-acuka-9JWJB` → **taslak**. Slug muhammara vaat edip
  acılı ezme sunuyordu ve 3VD3J ile aynı yemekti.

### 5. Kod hatası: meta açıklaması teaser'a takılıyordu (commit 146eaa3)
`RecipeController` `excerpt ?: description` sırasını kullanıyordu. Özet
bir cümlelik teaser ("Yoğurtlu acı meze." - 18 karakter), açıklama ise
asıl metin. Google snippet'i ~155 karakter gösterdiği için arama
sonucundaki alanın neredeyse tamamı boşa gidiyordu. Denetim zaten ters
sırayı kullanıyordu; iki taraf çelişiyordu. JSON-LD de aynı sıraya
çekildi. 3 test eklendi.

## Sonuç

| | önce | sonra |
|---|---|---|
| açık SEO sorunu | 124 | 1 |
| skor | 0 | 90 |
| yayındaki tarif | 215 | 176 |
| yapılışı olmayan tarif | 38 | 0 |
| ince açıklama | 117 | 0 |

Kalan 1 sorun Private Chef sayfası - bilinçli bırakıldı, sahibinin kararı.

Değişiklikler öncesi yedek: `01M1464ZJ5V5GP98VA98K6527J` (doğrulandı).
