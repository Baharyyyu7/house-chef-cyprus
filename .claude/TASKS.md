# Açık işler

Uzun koşularda bağlam özetleniyor; bu dosya o özetten sağ çıkıyor.
Sohbet geçmişi değil, burası okunur.

**Son ölçüm: 2026-09-27**

## Durum

| Dil | Tıklama | Gösterim | CTR | Sıra |
|---|---|---|---|---|
| tr | 40 | 3730 | 1.07% | 9.9 |
| en | 10 | 1575 | 0.63% | 10.7 |
| de | 5 | 1021 | 0.49% | 9.4 |
| fa | 14 | 183 | **7.65%** | **6.8** |
| pl | 5 | 161 | 3.11% | 8.8 |
| ru | 4 | 533 | 0.75% | 7.8 |
| pt | 3 | 204 | 1.47% | 10.4 |

Toplam **81 tıklama / 7.407 gösterim** (28 gün). Önceki gün 69 / 6.439.

Yayındaki tarif **134** (534'ten), yayındaki yazı **192** (237'den). 27 Eylül'de
AdSense "Düşük değere sahip içerik" dedi; herkese açık şablon yüzey taslağa alındı.
`app:invariants` temiz. Açık veri kalitesi kaydı yok.

## Dışarıdan gelen

- **Google Eylül 2026 spam güncellemesi 24 Eylül'de başladı, hâlâ yayılıyor.**
  status.search.google.com'dan doğrulandı. Kopya/şablon içerik tam olarak
  bu güncellemenin hedefi. Temizlik bu yüzden acildi.

## Bende değil, kullanıcıda

- [ ] **AdSense "İnceleme iste"**: temizlik yayında olduktan sonra kullanıcı
      "Sorunları giderdiğimi onaylıyorum" kutusunu işaretleyip istemeli.
      Form kullanıcının hesabında; ben göndermem.

- [ ] **GPTBot engeli** - Hostinger hPanel → CDN. Destek talebi mühendise
      iletildi, yanıt bekleniyor. Site Bing'de sıfır görünürlükte.
- [ ] **400 tarif fotoğrafsız** (20'si indekste). İçerikleri sağlam, eksik
      olan yalnız fotoğraf. Uydurma görsel yasak (sahte medya olayı geçmişi).
      Görsel SEO'nun geri kalanı zaten tamam: alt 27/27, lazy 24/27,
      width+height 27/27. WebP ve dosya adı iyileştirmesi ancak gerçek
      fotoğraf olunca anlamlı - şu an `card-800x600.jpg` yer tutucusu var.
- [ ] **Ses malzemesi**: yaşanmış Kıbrıs mutfak olayları, gerçek sayılar,
      tartışmalı mutfak görüşleri. Yazıları rakiplerden ayırmanın tek yolu.
- [ ] **PR #27 birleştirilmeyi bekliyor** (MERGEABLE/CLEAN). Merge kararı sizin.

## Sırada

- [ ] **Yabancı dil tarif sayfaları kalıp çeviri yüzünden kapalı.** 25 Eylül'de
      "590 başlık, önce Rusça" diye yazılmıştı; varsayım yanlıştı. Rusça
      tarif sayfalarının neredeyse hepsi noindex, sebebi başlık değil
      kalıp adım çevirisi; başlığı düzeltmek sayfayı açmaz. Açan şey tam
      çeviri (başlık, özet, açıklama, adımlar, malzemeler). 26 Eylül'de
      en çok Rusça gösterim alan 6 tarif tam çevrildi, 6'sı da indekse
      açıldı. Sırada: aynı yöntemle diğer dillerde gösterim alan tarifler.
      Önce Türkçe kaynağın temiz olduğunu doğrula: Lahmacun'un kaynağı
      çöp, çevrilmemeli.
- [ ] **Lahmacun Türkçe'de baştan yazılmalı.** Malzemeler "1 adet peynir,
      2 adet tavuk, 3 adet kırmızı et, 4 adet balık", adımlar genel şablon.
      Türkçe'de noindex ama Rusça'da 40 gösterim alıyor; çok aranan bir yemek.
- [ ] **Yabancı dil tarif çevirisi (asıl büyüme işi).** 27 Eylül'de 28 tarif
      x 6 dil = 168 sayfa gerçek çeviriyle açıldı (3 parti: yabancı talep,
      sonra TR gösterim sırası). Kalan 106 tarif. Yöntem: `coni:ceviri-toplu
      <klasör>`, dosya `{slug}.json`, dil başına title, excerpt, description,
      step_titles, steps. **Sonra her tarif x dil için `hasTranslation` ölç**:
      "dizine açıldı" sayacı yalnız kapıya bakıyor, dolu `chef_tip` çevrilmezse
      sayfa 404 kalıyor. Türkçe kaynakta şablon dolgu cümlesi varsa ("Taze
      malzemeleri, dengeli baharat uyumu...") çeviriye taşıma.
- [ ] **Yabancı sayfalarda sabit Türkçe arayüz** (28 Eylül canlı tarama,
      /en/, /de/, /fa/). Öncelik sırasıyla:
      1. Tarif filtresi tüm malzemeleri ham Türkçe adla listeliyor, "Ana
         Malzeme"/"Sebze Karışımı" yer tutucuları dahil. Yalnız o dilde
         yayındaki tariflerin malzemeleri, çevrilmiş adla.
      2. Başlık/alt bilgi/menü: "İçeriğe geç", "Giriş yap", "e-posta ile üye
         ol", "Kıbrıs Özel Şef", "Haftalık Menü", "Kıbrıs Rehberleri".
      3. Özel şef fiyat widget'ı ("1. İstenen Hizmet", "2 - 4 Kişi") tamamen
         Türkçe; villa kiralayan yabancı turist tam hedef kitle.
      4. Tarif sayfası: ekipman/affiliate kutusu, alerjen uyarısı, saklama
         notu varsayılanı; `tips`, `common_mistakes`, `equipment`,
         `storage_notes` alanları çevrilmiyor (HasTranslations dışında).
      5. Blog kartı: "Yazıyı oku", "Yazıya özel görsel hazırlanıyor",
         "Tüm yazılar"; ana sayfa "Malzeme dolabı", "Elindekilerle eşleştirme".
      6. Farsça: bazı tarif başlıkları "... (دستور پخت اصیل مدیترانه‌ای)", blog
         açıklamaları "بررسی تخصصی... در زمینه <Türkçe başlık>" kalıbı.
      Tarama betiği: sayfa metninde ı/ş/ğ/İ ve "ve/için/ile" arayan basit
      tarayıcı; oturum 27-28 Eylül'de scratchpad'de.
- [ ] **Türkçe kaynakta kalan şablon dolgu**: zeytinyagli-nohutlu-pilav,
      nohutlu-akdeniz-kasesi, limonlu-akdeniz-makarnasi açıklamaları ("Taze
      malzemeleri, dengeli baharat uyumu ve geleneksel pişirme tekniğiyle...")
      ve baklava-dilimi ("Bu özgün ev mutfağı sürümü..."). Çeviriler dolgusuz.
- [ ] **Olası kopya tarifler** (yayında, ikisi de açık): kibris-usulu-flaouna
      ile kibris-usulu-flaouna-q4hmp, sutlac ile sutlac-kibris,
      hellim-ve-domatesli-kahvalti-tavasi ile hellimli-kahvalti-tavas.
      İçerikleri farklı; kanibalizasyon ölçülüp karar verilmeli.
- [ ] **Taslaktaki 400 tariften talep görenler yeniden yazılmalı.** 28 günde
      64'ü gösterim aldı (toplam 206). Başta: Kıbrıs cevizli macunu,
      hellimli sigara böreği, ekmek kadayıfı, kazandibi, lokma, irmik
      helvası, revani, anari tatlısı. Ölçülü, özgün adımlı yazılıp
      yayınlanmalı; kalite kapısı ve otomatik yayın yolu artık ölçüsüz ya
      da şablon kümeli tarifi geçirmiyor.
- [ ] **Taslağa alınan 10 eşsiz yazı** (tavuk suyu, köfter/sucuk, peynirli
      kraker, özel şef x2 vb.) 25 Eylül toplu yayınıyla gelmiş, hiç
      incelenmemişti. İncelenip derinleştirilirse açılabilir; özel şef
      yazısı sitenin vermediği bir hizmeti anlatıyor.
- [ ] **Başlığı yanlış vaat eden 2 tarif noindex'te, düzeltme bekliyor:**
      Antalya Usulü Tahinli Piyaz (ne malzemede ne adımda tahin var) ve
      Kıymalı Kol Böreği (adımlar peynirli harç diyor). Düzeltilmiş içerik
      içe aktarılınca ImportRecipeContent noindex'i kendi kaldırır.
      Kıymalı Bezelye'de kıyma yerine kuşbaşı var, indekste, küçük.
- [ ] **main ile visual-redesign-v2 ayrışması.** Üretim v2'den çalışıyor,
      v2 main'den 422 commit ileride. İki dal uzlaştırılmadıkça her PR
      aynı çakışmayı yaşayacak.
- [ ] **Açıklanamayan toplu yayın** (26 Eylül'de tekrarlamadı). 25 Eylül 06:00'da 247 yazı yayınlandı,
      181'i kopyaydı. Faili bulamadım: auto-drip değil (limit 5),
      publish-scheduled değil (scheduled_at boş), blog:publish-all üretimde
      yok. Başka bir ajan doğrudan toplu güncelleme yapmış. Tekrarlarsa
      `app:invariants` yakalar ama önlemek daha iyi.
- [ ] **`blog:publish-all` (PR #27 ile geliyor)** kapısız toplu yayın
      yapıyor - 16 Ağustos olayının mekanizması. Kapıya bağlanmalı ya da
      kaldırılmalı.
- [ ] Şablon yazıların içeriğini derinleştirme (gösterim alanlardan başla).

## Bitti

- [x] 27 Eylül: **AdSense düşük değer temizliği** (`a250242`). 400 tarif + 45
      noindex yazı taslağa (yedek: `yedekler/2026-09-27-*.json`), kalıp
      çevirili tarif sayfaları o dilde 404, otomatik yayın yolu kalite
      kapısına bağlandı, 23 kırık tarif bağlantısı onarıldı.
- [x] 27 Eylül: **Hareket katmanı** (`31a07a6`): kinetik başlıklar, 3B kartlar,
      eğim, patlamalar, konfeti, okuma çizgisi, sayfalar arası morf.
      Adım zamanlayıcı düğmeleri hiç çalışmıyordu (tırnak hatası), düzeldi.
- [x] 27 Eylül: malzeme adları 6 dilde temizlendi (220 malzeme, 1.257 ad;
      geri alma `coni:malzeme --geri-al=29945`). Birim, kesir, hazırlık notu
      ve 25 arayüz dizesi 6 dilde (`bd7ffb4`, sonraki commit). Türkçe kaynakta
      Pastırmalı Yumurta'nın kalıp adımları gerçekleriyle, Kıbrıs Sütlacı'nın
      çelişen özeti düzeltildi; 684 adımdan şablon şef ipucu silindi (yedek:
      `yedekler/2026-09-27-kalip-sef-ipucu.json`).
- [x] Dil dil büyüme ölçümü ölü tabloya bağlıydı (`46e39cb`).
- [x] Kaynağı içinde taşıyan başlık çeviri sayılmıyor (`f3f9282`).
- [x] Yayın test kapısı yanlış sebeple kapanıyordu (`9d86237`).
- [x] Kapya biber yazısı: güvensiz konserve tarifi, 7 dilde düzeltildi.
- [x] İnce yazı reklam taşımıyor (`52e599f`), yükleyici de kapalı.
- [x] Yönetim panelindeki 5 uyarıdan 4'ü kapandı (9 mükerrer medya,
      471 şablon tarif taslağa, 13 ölçü dönüşümü doğrulandı + 1 reddedildi,
      mantar besin değeri 160→22 kcal düzeltildi).
- [x] PR #27 çakışmaları çözüldü, MERGEABLE (`57ddc3e`).
- [x] 25 Eylül: 178 kapıdan geçmemiş yazı. 30'u eşine bağlandı
      (`seo:blog-duplicate-title-consolidation`), 148'i taslağa alındı.
      Yedekler: `shared/storage/app/yedekler/`.

- [x] 26 Eylül: **20 gerçek makale kurtarıldı.** "taslak-inceleme" adlı iç
      adreslerde duruyor, sahte bir noindex ile gizleniyordu; temiz
      adreslerde şablon çöpü vardı. Kayıtların adresleri değiş tokuş edildi
      (çeviriler, görseller, SEO kayıtları iyi kayıtla birlikte temiz adrese
      geçti), şablonlar taslağa alındı, meta başlıklara sızmış
      "(Taslak İnceleme)" etiketi 7 dilde temizlendi. 7 dilde indekste.
- [x] Birleştirme kuralı "uzun olan kazanır"dan "özgün olan kazanır"a
      çevrildi (`64d19a6`); o kural bu yirmi makaleyi gömen kuraldı.
- [x] 6 Rusça tarif tam çevrildi ve indekse açıldı; Rusça malzeme adları
      global olarak düzeltildi ("Свежий İRmik" gibi kalıplar gitti).
- [x] 2 şablon tarif taslağa (Sarımsaklı Zeytinyağlı Makarna = pirinç,
      bulgur, makarna, un).

## Ortam tuzakları

- `php artisan test` SIGTERM alıyor; `php vendor/bin/phpunit` kullan.
  `--parallel` yok, `--log-json` yok, `--log-junit` var.
- Autoloader classmap başka worktree'yi gösterebiliyor (23 ve 26 Eylül'de
  iki kez). Artık TestCase ilk testte bunu kontrol edip net bir hatayla
  duruyor; görürsen `composer dump-autoload`. Elle kontrol:
  `php -r 'require "vendor/autoload.php"; echo (new ReflectionClass("App\\Models\\Recipe"))->getFileName();'`
  Bu depo çıkmazsa `composer dump-autoload`.
- **Modeli kısmi sütunla çekme.** `get(['id','slug','body'])` yapıp sonra
  `engeller()` çağırmak yanlış sonuç verir; bakılan sütunlar NULL gelir.
  Bu oturumda iki kez aynı tuzağa düştüm.
- **Kayıtlı robots kapıyı ezer.** `indekslenebilir()` yalnız kapıya bakar;
  canlı sayfayı `seo_metadata.robots` belirler (noindex hangisinde olursa
  kazanır). "Kapıdan geçiyor" demek "indekste" demek değil; emin olmak için
  canlı sayfanın robots etiketini oku.
- **Düzenli ifade satır sonunu yutmasın.** `\s*` satır sonunu da yer;
  Markdown gövdede başlığı paragrafla birleştirir. Yatay boşluk için
  `[ \t]*` kullan. 26 Eylül'de 130 gövde böyle bozuldu, yedekten onarıldı.
- **Parmak izi örtüşmesi eş bulmaz.** Şablon yazılar ortak giriş/çıkış
  paragrafı taşıyor; örtüşmeye bakarak kanonik atamak 148 yazıyı aynı
  hedefe bağladı. Geri alındı.
