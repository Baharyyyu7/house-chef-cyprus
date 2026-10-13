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
- [x] (12 Ekim: çöp kayıt id 34 taslakta, gerçek tarif geleneksel-citir-lahmacun-23a2f 7 dilde açık; eski /lahmacun ve iki taslak nüsha adresi ona 301, 30693) **Lahmacun Türkçe'de baştan yazılmalı.** Malzemeler "1 adet peynir,
      2 adet tavuk, 3 adet kırmızı et, 4 adet balık", adımlar genel şablon.
      Türkçe'de noindex ama Rusça'da 40 gösterim alıyor; çok aranan bir yemek.
- [x] (12 Ekim ölçüldü: "kalan 106" eskidi; 11 Ekim turuyla her dilde 123/127 açık. Kapalı 4: firin-sebzeli-taco-kasesi, sutlu-revani-5g74t, etli-kabak-dolmasi-f18sj, sucuklu-yumurta, TR kaynak ya da TR noindex yüzünden) **Yabancı dil tarif çevirisi (asıl büyüme işi).** 27 Eylül'de 28 tarif
      x 6 dil = 168 sayfa gerçek çeviriyle açıldı (3 parti: yabancı talep,
      sonra TR gösterim sırası). Kalan 106 tarif. Yöntem: `coni:ceviri-toplu
      <klasör>`, dosya `{slug}.json`, dil başına title, excerpt, description,
      step_titles, steps. **Sonra her tarif x dil için `hasTranslation` ölç**:
      "dizine açıldı" sayacı yalnız kapıya bakıyor, dolu `chef_tip` çevrilmezse
      sayfa 404 kalıyor. Türkçe kaynakta şablon dolgu cümlesi varsa ("Taze
      malzemeleri, dengeli baharat uyumu...") çeviriye taşıma.
- [ ] **Yabancı sayfalarda kalan Türkçe** (28 Eylül ikinci tarama sonrası).
      Arayüz, çerez bandı, malzeme seçicisi, taksonomi, diyet adları, blog
      dizini ve SSS düzeldi; ticari bloklar yabancı dilde gizli. Kalan:
      blog kategori adları (BlogCategory çevrilmiyor), Farsça blog
      açıklamalarındaki kalıp ("بررسی تخصصی... در زمینه <Türkçe başlık>"),
      tarifin ipucu/sık hata/saklama alanlarının çevirisi (şimdilik yabancı
      dilde gizli). Özel şef/catering hizmetinin yabancı dilde sunulması
      kullanıcı kararı.
- [x] (12 Ekim ikinci tur: yeniden tarandı, 18 tarife 24 satır) **Tarif tutarlılığı**: adımda geçip listede olmayan malzeme. 28 Eylül'de
      31 tariften 15'i düzeltildi (27 satır, yedek
      `yedekler/2026-09-28-eksik-malzeme-eklenen.json`). Kalan 16'nın çoğu
      yanlış alarm ("krema kıvamı", "tereyağına döner"); gerçek olanlar:
      ezogelin tereyağı, babagannuş maydanoz, Ali Nazik ve mercimek köftesi
      salça, havuç çorbası dereotu, lahana sarması tarçın, ev açması yumurta,
      bazlama tereyağı, ayva tatlısı limon. Denetim betiği scratchpad'de
      (denetim2.php); `İ` küçültmesi "İrmik"i kaçırıyor.
- [x] (12 Ekim ölçüldü: açık 124 TR tarifte "Adım N" başlığı 0, boş başlık 0; kalıp yalnız taslaklarda) **100 tarifte Türkçe adım başlığı "Adım 1..N"** kalıbı. Çeviri
      partilerinde başlıklar gerçekleriyle yazılıyor; Türkçesi ayrıca
      düzeltilmeli.
- [x] (12 Ekim ikinci tur, 5 açıklama düzeltildi) **Türkçe kaynakta kalan şablon dolgu**: zeytinyagli-nohutlu-pilav,
      nohutlu-akdeniz-kasesi, limonlu-akdeniz-makarnasi açıklamaları ("Taze
      malzemeleri, dengeli baharat uyumu ve geleneksel pişirme tekniğiyle...")
      ve baklava-dilimi ("Bu özgün ev mutfağı sürümü..."). Çeviriler dolgusuz.
- [x] (12 Ekim ölçüldü: flaouna-q4hmp taslak ve 301; sütlaç ocak, sutlac-kibris fırın + pirinç unu + tarçın; hellim-ve-domatesli sade, hellimli-kahvalti-tavas yumurta/soğan/biberli; altı sayfa 28 günde toplam 1 gösterim, kanibalizasyon yok, ayrı yemekler) **Olası kopya tarifler** (yayında, ikisi de açık): kibris-usulu-flaouna
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
- [x] (12 Ekim ölçüldü: Tahinli Piyaz ve Kıymalı Kol Böreği'nin tüm nüshaları taslakta, 404; yayındaki bezelye tarifinin başlığı "Etli Bezelye Yemeği", kuşbaşıyla uyumlu, yalnız slug 'kiymali') **Başlığı yanlış vaat eden 2 tarif noindex'te, düzeltme bekliyor:**
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
- [x] 28 Eylül: gece çeviri içe aktarımı (02:20) düzeltilmiş 1.257 malzeme
      adını eski dosyalardaki çöple geri yazıyordu; içe aktarıcı artık dolu
      malzeme adını ezmiyor (`bff1d38`), adlar yeniden uygulandı.
- [x] 28 Eylül: 2 kopya tarif taslağa + eşine 301 (132 tarif yayında).
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

- Ana checkout'un `public/build/manifest.json`'u 27 Eylül'den kalma (480 bayt); tam paket orada 125 Vite hatası verir. Tam paketi worktree'de koş (11 Ekim).
- Canlı ölçümde adrese sorgu parametresi ekleme: `?k=` sayfayı noindex yapar.

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


# 8 Ekim denetimi - düzeltme turu

Dal: claude/web-system-analysis-v2 (taban c0cf1fd = canlı). Ajan dalları: claude/fix-{guvenlik,cokdil,onyuz,operasyon}.

## Benim paketim
- [x] Sahte onay komutları + AutonomousPublishingGateService silindi
- [x] 23 referanssız toplu-yayın seeder'ı silindi
- [x] GuardsPublication (insan onayı + günlük tavan), publishAllReady limit 5
- [x] Kalite kapısı: cümle tekrarı, yer tutucu, ham HTML, kırık iç link, okunan metinden uzunluk
- [x] blog:html-govde-cevir (HTML→Markdown)
- [x] blog:kusurlulari-yayindan-al
- [x] Tarif tutarlılık kapısı (başlık↔malzeme, genel adım)
- [x] README + docs/OPERASYON.md
- [ ] 33 referanssız komut: bilinçli olarak dokunulmadı (elle çalıştırılan araçlar; düşük fayda)

## Entegrasyon
- [x] 4 ajan dalı merge (2847de9); ilk release /tarifler 500 ile otomatik geri alındı (önbellek nesne açmıyor), 92c6fc3'te düzeldi
- [x] Tam paket 1817/1817 yeşil, phpstan 0
- [x] Canlı 5281bfe: /health commit, CSS manifest eşit, /index.php 301, /en iç linkler 44/44 200, çerez bandında ham anahtar yok, porsiyon/alerjen var

## Üretim verisi (8 Eki, yapıldı)
- [x] 41 yazı taslağa (25 şablon + 16 ünlü), yedek shared/storage/app/private/yedek/
- [x] 66 HTML gövde Markdown'a (metin kaybı yok, oran >=1.01)
- [x] 7 ikiz birleşti, 301'ler canlıda doğrulandı
- [x] seo:recipe-quality-scan --apply (3 tarif noindex), media:blog-fallback-variants (6), cache:clear
- [x] GitHub varsayılan dal visual-redesign-v2, PR 33 kapandı, dependabot target-branch
- [ ] SCHEDULER_TRIGGER_TOKEN döndürülemedi: wrangler askinasli26 hesabında, gerçek Worker başka hesapta. Sunucu .env geri alındı, zamanlayıcı eski token'la çalışıyor. O hesapta yanlışlıkla boş hc-zamanlayici Worker'ı oluştu (kod/cron yok) - silinmeli.
- [ ] app:invariants kapali_sayfaya_ceviri_isi (213): noindex içerik için açık çeviri işleri (yalnız iş listesi kirliliği)

## Yayın 9 Ekim (canlı ee6945d)
- [x] 49a2e47 blog SSS ve FAQPage yalnız TR; ee6945d porsiyon Enter testi
- [x] Tam paket 1825/1825 yeşil; release5.log exit 0; deploy + lsphp + /health commit ee6945d
- [x] Canlı: /tarifler?q[]=x ve ?max_minutes=abc 500 -> 200; /en/blog'da FAQPage ve blog SSS yok (footer /sss bağlantısı hariç)
- [x] Porsiyon: gizli submit düğmesi azalt düğmesinden önce (sunucu HTML); test yeşil
- [x] Porsiyon Enter gerçek tarayıcıda doğrulandı: 6 yazılıp Enter -> ?servings=6, azalt düğmesi tetiklenmedi
- [ ] Dolap (/dolabim) üye girişi istiyor; sayımlar canlıda doğrulanmadı
- [x] 12 blog yazısı TR/EN çifti: TR FAQPage 1, EN FAQPage 0 ve EN SSS bölümü 0 (EN sayfalar 200)
- [ ] Posta: info@ kutusu şifresi hPanel'den güncellenmeli (SMTP kimlik doğrulaması başarısız)
- [x] Medya onay kapısı: yükleyen kendi görselini onaylayamaz (38ddbb5, uploaded_by_user_id; migration bekleyen kayıtları doldurdu). Canlıda admin girişi olmadığı için davranış canlıda doğrulanmadı; tam paket 1828/1828, pre-deploy yedek 01M4G131WF08Q700JGHJFNMCJV doğrulandı, restore testi yapılmadı
- [x] Machina "İnsan Onayı Ver": sabit true ve sahte "kuyruğa alındı" kaldırıldı; onay audit_logs'a yazılıyor (d44b3d0, tam paket 1828/1828, canlı d44b3d0). Canlıda audit satırı admin girişi olmadığı için doğrulanmadı; öneri hâlâ uygulanmıyor
- [ ] Karar bekleyen: medya beyanları kaydı, BlogCategory çevirisi, footer 17 EN bağlantısı, favori listesi kartı

## Yayın 10 Ekim (EN statik sayfalar)
- [x] Footer'daki bilgi, politika, yasal merkez ve iletişim sayfaları için EN metin: lang/en/information_pages.php (10 sayfa), lang/en/legal_center.php (11 bölüm, 3 politika, merkez). Makine destekli çeviri; hukuki metin sahibi tarafından gözden geçirilmeden dayanak yapılmamalı
- [x] Yönlendirme: config/locales.php localized_static_routes (yalnız en). /de/sss gibi çevrilmemiş dil adresleri hâlâ Türkçeye 301
- [x] Arayüz metinleri: 68 anahtar tr/en/de/fa/pl/pt/ru.json; TR değerleri eski Türkçe metinlerle birebir aynı. de/fa/pl/pt/ru değerleri makine yazımı, gözden geçirilmedi (bu sayfalar o dillerde zaten Türkçeye yönleniyor)
- [x] Canonical: EN sayfalar kendi /en/ adresini gösteriyor; gorsel-ve-kaynak-politikasi takma adı kendi adresini canonical tutuyor
- [x] Sitemap: gizlilik, cerezler, kullanim-kosullari eklendi (TR listede de artık var)
- [x] Testler: EnglishInformationAndLegalPagesTest 12/12; hedefli 151/151. Tam suite ilk koşuda 1839/1842: 3 hata (de/fa/pl/pt/ru JSON'da yeni anahtarlar eksikti), düzeltildi
- [x] Tam suite release.sh adım 4'te (temiz kopya): 1846/1846 yeşil; release4.log, RELEASE_EXIT=0, yayın 7c3de40
- [x] Canlı doğrulama (7c3de40): /health commit 7c3de40; /en/hakkimizda, /en/gizlilik, /en/yasal-bilgilendirme, /en/iletisim, /en/cerezler, /en/kullanim-kosullari 200, html lang=en
- [ ] Hukuki gözden geçirme: gizlilik, çerez, kullanım koşulları ve yasal merkez EN metinleri sahibi tarafından okunmalı
- [x] (12 Ekim ölçüldü: 1f2c561 göçü canlıda koşmuş, iki sütun var; 4aceb39 eksikti, b413ccd tamamladı) Eski blog kategori çevirisi (4aceb39) ve medya beyanı zamanları (1f2c561) bu yayınla birlikte gidiyor; canlıda kontrol edilmedi

## 13 Ekim sekizinci tur: krem karamel, gece ölçümü, çift zamanlayıcı, TR malzeme notu
- [x] krem-karamel yayında (elle:taslak-yayin 30766, kapı temiz, o gün ilk yayın). TR 200 + index,follow + sitemaps/recipes.xml. coni:ceviri-toplu 6 yazıldı 6 açıldı; 6 dil 200 + index,follow, hasTranslation 6/6, Türkçe kalıntı 0
- [x] Gece ölçümü: content_translations 13 Ekim 00:00-05:00 arası 0 satır (02:20 içe aktarım koştu, ezmedi). recipe_quality 02:05 "ok 0": 13 yeni/onarılmış tarifin hepsi index,follow. Her dilde 136 açık tarif (123 + 13); yayında 138, index 137 (#1500 bilerek noindex)
- [x] Bulunan ve düzeltilen (ce9e245): Hostinger cron 17 Eylül 19:00'da geri gelmiş, beş dakikada bir :02'de (shared/logs/scheduler.log); Worker her dakika ~:43'te ikinci schedule:run koşuyor. withoutOverlapping eşzamanlılığı engelliyor, aynı dakikayı değil: günlük görevler (backup:completeness, content:integrity-guard, db:schema-contract, recipe_quality, IndexNow, çeviri içe aktarımı...) en az 25 Eylül'den beri her gün iki kez. Yalnız onOneServer'lı claude_coni 26 Eylül'den beri tek. routes/console.php sonunda 58 göreve onOneServer; ScheduleConsolidationTest +1 (eski kodda kırılıyor). Blog damlası 26 Eylül'den beri yayın yapmadığı için çift yayın olmadı
- [x] Lahmacun dürüm (coni:kaynak 30767 + coni:ceviri-toplu --zorla 6 dil): adım 5 "(fanlı, alt-üst ısıtma)" -> "alt-üst ısıtma ayarında 250 °C" (yumuşak kalması gereken dürümde fan kurutur); adım 11 dürüm kâğıda dikey, katlı ucu aşağıda, üst üçte biri dışarıda, alttaki kâğıt katlı ucun üzerine. Canlı 7/7 200 + index, yeni metin var, eski ifade 0
- [x] fa #265 "قادایف" -> "کادایف" (coni:malzeme 30768); /fa/recipes/ekmek-kadayifi qaf 0, kaf 35
- [x] Bulunan ve düzeltilen (deb01be): TR malzeme notu adın önüne konuyor ve Blade'de satırın altında bir kez daha basılıyordu ("6 adet orta boy sakız kabağı, her biri yaklaşık 200 g Kabak" + altında aynı not). Yayındaki 25 tarifte 108 notun 66'sı virgüllü. Not artık her dilde addan sonra virgülle, alt satır kalktı. TurkishIngredientNoteTest 2 (eski kodda ikisi de kırılıyor)
- [x] Yayın deb01be (ce9e245 + deb01be): tam paket 1877/1877 (20 dk), release.sh exit 0, /health deb01be. Canlı: etli kabak "6 adet Kabak, orta boy sakız kabağı, her biri yaklaşık 200 g", not tek kez; menemen "½ demet Maydanoz, servis için"; lahmacun dürüm "550 gram Un, 500 g hamura, 50 g açarken serpmek için". schedule:list 58; aynı dakikada ikinci schedule:run 3/3 "Skipping ... already ran on another server"; Worker cron'suz dakikada koşuyor (06:13:43)
- [ ] 14 Ekim sabahı ölç: integrity_reports 13->14 Ekim gecesi her guard için tek satır (backup:completeness, content:integrity-guard, db:schema-contract, recipe_quality), backup:list günlük yedek 1
- [x] Yönlendirme denetimi (elle:yanlis-yonlendirme 30775): 191 SlugRedirect'te eski adres ile hedefin kelime örtüşmesi ölçüldü, 38 düşük; tek tek okundu. 22 silindi (konusu tutmuyor ya da taslağa gidip 404'le bitiyordu: mantar rehberi -> enginar dolması, Lefke hurması -> Kıbrıs tatlısı, kemik suyu -> baharat tablosu, tulumba -> köfter, simit -> ekşi maya; #1195 yayındaki #3434'ü gölgeliyordu). 4 doğru hedefe çevrildi (atom ezme -> atom-mezesi-w1nnc, şeftali kebabı -> yayındaki #3449, tavuk tantuni yazısı -> tantuni tarifi, simit yazısı -> sokak-simidi tarifi). Yeni #4184: somon -WG06B (47 gösterim, 404) -> yayındaki #3493. Dokunulmadı 12 (pilavuna = flaouna, halva-irmik, kibris-lokma, anari, Kıbrıs tatlısı...). Canlı 11/11 beklenen kod. Geri alma: payload.once
- [x] İncelendi, kusur değil: 11 Ekim TR kaynak düzeltmelerinden (coni:kaynak 30630/30639/30678/30684) sonra yazılmamış 74 çeviri alanı: anlam değiştiren 4 düzeltmenin (hellim kabuksuz, kolokas çıtır, flaouna ortası açık, arpa şehriye) çevirisi zaten doğru anlamda, kalanı TR dilbilgisi. Adım başlıklarında kalıp çeviri 0. İngilizce slug'lı yazıların TR sayfası Türkçe, EN sayfası İngilizce
- [x] Sahte onay (elle:sahte-onay 30776): 8 Ekim'de taslağa alınan 16 ünlü yazısı hâlâ approved + 25 Eylül 06:30:18 sahte onaylıydı ve BlogPublicationReadinessService kapısını geçiyordu; seo:auto-drip (08:00, limit 3) yalnız son güncellenen 9 taslağa baktığı için tesadüfen yayınlanmamışlardı (104 adayın kapıyı geçen tek 16'sı, sırada 30-45). Aynı seriden 5 yazı başka başlık kalıbıyla kaçmış, yayındaydı (Macron, Modi, Xi Jinping, Zelenski, futbol yıldızları; 28 günde 0 gösterim): taslağa, TR+EN 404, site haritasında yok. 58 taslağın sahte onayı silindi (editorial_status draft). Şimdi damla adayı 63, kapıyı geçen 0; onaysız taslak kapıdan geçemiyor (readiness insan onayı istiyor). Yayında 100 yazı. Yayındaki 20 yazı (EN rehberler 4601-4620) damgayı taşıyor, dokunulmadı. Geri alma: payload.once
- [ ] Bulunan: 3 blog slug'ı Türkçe İ küçültmesiyle bozuk (makarna-yapiminin-i-ncelikleri #4572 yayında, #4559 ve #4576 taslak); 20 Eylül 06:24 toplu içe aktarım dosyasından geldi, uygulamada bu kod yolu yok. Yayındaki adres gösterim aldığı için değiştirilmedi
- [x] İncelendi, kusur değil: 11 yeni tarifte Recipe şeması yok, çünkü kendi fotoğrafları yok (20 Eylül kuralı, RecipeController: marka görseli yemeğin fotoğrafı diye bildirilmez). Fotoğraf gelince şema kendiliğinden basılır

## 12 Ekim yedinci tur: ikinci taslak partisi
- [x] Ölçüm (GSC 28 gün, sayfa): lahmacun-durum-8FQ8V 39 ("lahmacun durum/dürüm", sıra ~9), revani-kibris 16, hosmerim 11, krem-karamel 9; sutlu-revani-5g74t 11 ("sütlü revani kıbrıs" 9). Dördü de saf şablon ("200 g un, 150 g şeker", "Malzemeleri tezgaha dizin"). Nüsha: lahmacun-durum-jbfpk (#1396) taslak
- [x] Bulunan: yayındaki Sütlü Revani (#515) açıklaması "irmik ve undan" ama listede irmik yok; kapı baslik_malzeme:revani, TR noindex, EN 404. seo:recipe-quality-scan yalnız noindex ekler, kaldırmaz
- [x] Katalog: #26 Balık, #46 Tavuk Baget, #137 Tavuk Kanat, #156 Kırmızı Soğan 6 dilde yarım çeviri ("Kırmızı Onion", "Hähnchen Baget"). coni:malzeme rapor 30714, 24 ad, canlıda doğrulandı. Yayındaki tarifte yoktu, 13 taslak satırında vardı. Tarama: Türkçe adın 4+ harfli kelimesi çeviride aynen geçiyor mu (scratchpad taslak2/malzeme_ad2.php)
- [x] Yazım A bitti (lahmacun-durum-8fq8v: yumuşak dürüm lahmacunu, Antep tescili 236; krem-karamel 6 x 150 ml). Yazım B bitti: Kıbrıs'ta revani = şamali (yumurtasız, unsuz, yoğurt, damla sakızı, badem; Cyprus Food Museum, DAÜ Karaer 2022, Kopiaste) -> başlık "Kıbrıs Revanisi (Şamali)"; hoşmerimin Kıbrıs kaynağı yok, Balıkesir tescili 187 -> başlık "Hoşmerim", country Türkiye; Sütlü Revani ayrı yemek (scratchpad taslak2/yazim/SUTLU_REVANI_NOTU.md)
- [x] A ikilisi taslakta yazıldı: doğrulayıcı 7+6 düzeltme (krem karamelde sıcak tencereye su çelişkisi, benmari suyu ayrı; dürümde kaynaksız süre yerine görsel işaret, kâğıda sarma isteğe bağlı), rapor scratchpad taslak2/dogrulanmis/RAPOR_*.md. coni:taslak 30715, coni:malzeme-ekle --taslak 30716 (31 satır). Kapı: dürüm 11 adım 23 satır, krem karamel 10 adım 8 satır, ikisi eksikMi hayır, tutarlılık boş, şablon hayır. Dürümün robots boş (yayında index,follow verilmeli); nüsha lahmacun-durum-jbfpk (#1396) yayında 8fq8v'ye 301
- [x] B ikilisi taslakta yazıldı: doğrulayıcı "şamali = revani" iddiasını kaynaksız buldu (Havadis: hamura yağ girerse revani olur) -> başlık "Şamali (Kıbrıs İrmik Tatlısı)", slug revani-kibris kalır (Kıbrıs Gıda Müzesi ravani adını veriyor; slug değişimi otomatik 301 ile revani aramasını başka tatlıya taşırdı). Höşmerim: TDK yazımı, tescil belgesindeki "kazanın altı kapatılarak" ocak demek; künefe peyniri tescildeki mayalı tuzsuz taze peynire uygun (Selçuk Ü. 2017 tezi). Yeni malzeme #268 Çiğ Badem İçi (coni:malzeme 30717). coni:taslak 30718, coni:malzeme-ekle --taslak 30719 (18 satır). Kapı 4/4 temiz (şamali 9 adım 11 satır, höşmerim 6 adım 7 satır)
- [x] Yayında ama kapalı 4 tarif ölçüldü: #515 Sütlü Revani (irmik yok), #603 Etli Kabak Dolması (et yok, indekste başka nüsha yok), #1500 Sucuklu Yumurta (temiz ama #631 Bakır Tavada Sucuklu Yumurta indekste: nüsha kararı, dokunma), #5 taco kasesi (TR index, 0 dil)
- [x] Kod 1e9bb9b: coni:taslak --yayinda (yalnız yayında + noindex tarif; indeksteki reddedilir; status/robots değişmez). ConiTaslakTest 4/4. Tam paket arka planda
- [x] Yazım C + doğrulayıcı (9 + 10 düzeltme): dolma harcı 12 yarım kabağa sığmıyordu, pirinç 120 -> 80 g (Kültür Bakanlığı 75 g); revani saklama uyarısı 2 saat kuralını aşıyordu; kıymalı yemek 72 °C (Tarım ve Orman Bak.); paket gram (kabartma tozu 10 g, vanilin 5 g, Dr. Oetker). Yeni #269 Kabartma Tozu (coni:malzeme 30720). Girdiler sunucuda ~/taslak_c.json, ~/ekle_c.json (30 satır)
- [x] Yayın 1e9bb9b: tam paket 1873/1873 (37 dk, makine yükü), release.sh exit 0, /health 1e9bb9b. build-artifacts boş
- [x] coni:taslak --yayinda 30722 + coni:malzeme-ekle 30723 (30 satır). Etli Kabak Dolması kapı temiz -> robots index,follow (elle:indeks-ac 30724), canlıda meta index,follow ve sitemaps/recipes.xml içinde
- [x] Bulunan ve düzeltilen (5346fbe, yayında değil): kapı mb_strtolower("İrmik") = "i̇rmik" yüzünden irmikli Sütlü Revani'yi "irmik yok" sayıyordu, "Ispanaklı" başlıklar hiç denetlenmiyordu. Canlıda 1168 tarifte eski/yeni kural: farklı 6; yayında tek değişim Sütlü Revani açılması, 5 ıspanaklı taslak doğru işaretlenir. RecipeConsistencyGateTest +1 (eski kodda kırılıyor). Tam paket arka planda
- [x] Yayın 6faced9 (5346fbe dahil): tam paket 1874/1874, release.sh exit 0, /health 6faced9. Sütlü Revani yeni kodla kapı temiz -> indeks-ac 30729; 7 dilde 200 + index,follow
- [x] Çeviri: iki tarif x 6 dil (başlıklar otomatik tamamlamayla, translator_note İngilizce), sunucuda ~/ceviri_c, coni:ceviri-toplu 12 yazıldı 0 hata; hasTranslation 12/12, engel null. Canlı: Etli Kabak Dolması 6 dilde 200 + index,follow (fa adresi /fa/recipes/), Türkçe kalıntı 0. Sütlü Revani 6 dilde 200 ama noindex (TR robots'a bağlı) -> 5346fbe yayınından sonra indeks-ac ile açılır -> coni:taslak --yayinda + coni:malzeme-ekle (yayın kipi) -> kapı -> robots index,follow (raporlu) -> coni:ceviri-toplu 6 dil -> hasTranslation
- [x] Kullanıcı kararı 12 Ekim: "yayınla, 11 taslağı aç". GuardsPublication günlük tavan 10 (UTC takvim günü): 10 yayında (elle:taslak-yayin 30725; RecipePublicationService alanları, tamlık eşiği 95 yayındaki 127 tarifin 108'inde de tutmadığı için kullanılmadı; kapı 11/11 temiz). Canlı: 10/10 TR 200 + index,follow + sitemaps/recipes.xml. krem-karamel TASLAKTA, 13 Ekim 00:00 UTC sonrası yayınlanacak (aynı betik, yalnız krem-karamel)
- [x] Mutfak düzeltme (30726): ekmek-kadayifi, hosmerim, krem-karamel country Türkiye ama cuisine Kıbrıs -> #2 Türk Mutfağı; başlık eki "Kıbrıs Yemek Tarifi" kalktı
- [x] Nüsha 301 (30727, SlugRedirect 4179-4183): antalya-usulu-tahinli-piyaz-qs9pm, kiymali-kol-boregi-xpek0/kshev, lahmacun-durum-jbfpk, kibris-lokma; canlıda 301. Dokunulmadı: kibris-macunu-cevizli-sucuk-* ve kibris-usulu-firin-4st5z/-tavuk-baget (ayrı yemek)
- [x] Çeviri: 3 ajan, 11 tarif x 6 dil (scratchpad taslak2/ceviri2, başlıklar otomatik tamamlamayla). coni:ceviri-toplu 18 + 24 + 18 = 60 yazıldı, 0 hata. Canlı 60/60: 200 + index,follow + Türkçe kalıntı 0
- [ ] **13 Ekim 00:00 UTC sonrası krem-karamel**: sunucuda hazır. Sıra: `DENE=1 php artisan tinker --execute="require getenv(\"HOME\").\"/yayinla_krem.php\";"` -> DENE'siz -> canlı 200/index -> `php artisan coni:ceviri-toplu ~/ceviri2_krem --neden="13 Ekim yayinlanan tarifin 6 dil cevirisi"` -> 6 dil canlı
- [ ] Bulunan (düzeltilmedi): lahmacun dürüm 5. adım "fanlı, alt-üst ısıtma" belirsiz, 11. adım "kâğıdın alt kenarına yatay koyun" katlama ile geometri uyuşmuyor (coni:kaynak ile TR + 6 dil); fa ekmek kadayıfı katalog adı "قادایف", aranan "کادایف" (coni:malzeme #265); etli kabak dolması hazırlık notları uzun ("6 adet orta boy sakız kabağı, her biri yaklaşık 200 g Kabak")

## 12 Ekim altıncı tur: taslaktaki talep gören tarifler
- [x] Ölçüm (GSC 28 gün, sayfa satırı -> tarif, SlugRedirect çözümlü): 140 taslak tarif 963 gösterim. Betik scratchpad taslak/talep.php, çıktı sunucuda ~/taslak_talep.json
- [x] Aynı yemeği yayında olan taslaklar 301 (canlıda doğrulandı): kazandibi -> geleneksel-kazandibi-emk9r, mercimek-corbasi-sabah -> klasik-kirmizi-mercimek-corbasi-vkztq, ayva-tatlisi -> kaymakli-firin-ayva-tatlisi-34m3m (rapor 30707, SlugRedirect 4175-4177); halva-irmik -> sutlu-irmik-helvasi-rt8qq (30708, 4178). Zaten 301 olanlar: patlican-kozlemesi-cdw76, sote-taze-fasulye-s96g1, kibris-kolokoti-8w914
- [x] Kod 7292d21: coni:taslak (taslağı baştan yazar: metin, süre, porsiyon, zorluk, ülke, gruplar, adımlar; eski satır/adım/grup/çeviriyi siler ve raporda saklar; status/robots değişmez; --dene, --geri-al) + coni:malzeme-ekle --taslak. Test ConiTaslakTest 3, ConiMalzemeEkleTest +1
- [x] Yayın 43d2685 (7292d21 + belgeler): tam paket 1872/1872, release.sh exit 0, üretimde coni:taslak var
- [x] İçerik: 7 taslak yazıldı (iki ajan, scratchpad taslak/yazim/<slug>.json, biçim: recipe{}, groups[], steps[], ingredients[] katalog id + canonical, yeni_malzemeler[7 dil], kaynaklar[]): ekmek-kadayifi (#1618), lokma-kibris (#1594), kibris-macunu-ceviz (#1597), sigara-boregi-hellimli (#1541), kibris-usulu-firin-tavuk (#1172), antalya-usulu-tahinli-piyaz-1xf5w (#618), kiymali-kol-boregi-y4fpg (#620)
- [x] (yapıldı: doğrulayıcı her dosyada 2-7 düzeltme, rapor .claude/raporlar/2026-10-12-taslak-tarif-dogrulama.md; coni:malzeme 30711 yeni #265 Hazır Ekmek Kadayıfı, #266 Taze Yeşil Ceviz, #267 Gıda Sınıfı Sönmüş Kireç, 6 dil; coni:taslak 30712; coni:malzeme-ekle --taslak 30713, 75 satır, metrik birimde de elle kanonik gerekti. Ölçüm: 7/7 draft/verified, eksikMi hayır, tutarlılık boş, şablon hayır) Sonra: bağımsız doğrulayıcı ajan (kaynak, miktar, adım-malzeme tutarlılığı) -> yeni malzemeler coni:malzeme (Türkçe ad anahtarlı, 6 dil) -> coni:taslak --dene, coni:taslak -> coni:malzeme-ekle --taslak --dene, sonra yaz -> RecipeQualityGateService eksikMi=false ve olc/sablonMu=false ölç -> kullanıcıya yayın kararı için liste (AGENTS.md: ajan yayınlamaz)
- [ ] **Kullanıcı kararı: 7 taslağın yayını** (AGENTS.md: ajan yayınlamaz). Yayınlanırsa: status published + robots index,follow (kibris-usulu-firin-tavuk zaten index,follow), sonra 6 dil çevirisi coni:ceviri-toplu. Fotoğraf yok (yer tutucu). Açık sorular: ekmek kadayıfı şerbeti ev usulü hafif (Afyon tescili 500 g kuru kadayıfa 2,5 kg şeker + 2 l su); lokmanın Kıbrıslı Türk kaynağı yok (Kıbrıs hükümeti kitabı + Visit Cyprus); ceviz macununda kireç oranı 1 tepeleme yemek kaşığı / litre, gıda sınıfı sönmüş kireç
- [ ] Atlanan/sonraki parti: revani-kibris (yayındaki Sütlü Revani TR noindex, ayrı yemek mi karar), hosmerim, krem-karamel, tulum-peynirli-gozleme (el açması peynirli gözleme var), lahmacun-durum-8fq8v (39 TR gösterim), zeytinyagli-portakalli-kek, zeytinyagli-nohutlu-pilav. Çöp başlıklı taslaklar (Baharatlı #596, Acılı #527, Tatlı Tarçınlı #518, Fırın #524) yazılmaz
- [ ] Bulunan: katalogda bozuk EN adlar ("Fresh Balık", "Chicken Bagmeat", "Chicken Kanat"); Larastan IntegrityReport payload tipini boş dizi çıkarıyor (ConiKaynak baseline'da)

## 12 Ekim beşinci tur: gece çeviri içe aktarımı gerçek çevirileri eziyordu
- [x] Ölçüm: açık tarif pt/pl/fa 96 (en/de/ru 123). 27 tarif x 3 dil kapı "sablon". Sebep: `content:import-translations --apply` her gece 02:20 teslim klasörünü (translations/{pt,pl,fa}/ 129 kalıp tarif + 251 blog dosyası) yeniden okuyup dolu çeviriyi eziyor. 12 Ekim 02:20'de 1098 alan: pt/pl/fa 27 tarif başlık/özet/açıklama + 131 adım başlığı + 131 talimat + 15 ipucu; en 3 açıklama + 11 adım alanı, ru 10 adım başlığı (11 Ekim sadakat düzeltmeleri geri dönmüştü). 11 Ekim akşamı TR "Adım N" başlıkları düzelince eski dosyalar "kaynak metin çevirinin içinde" denetimini geçmeye başladı
- [x] Veri (IntegrityReport 30704, guard elle:gece-ceviri-ezilmesi): 1098 satır 00:20 yedeğinden (01M4PQQQ9T6801ETT6Y58EJPNV) geri yazıldı; 02:20 sonrası bu satırlara başka yazım yoktu, hepsi yedekten farklıydı. pt/pl/fa 96 -> 123. Canlıda pt cacik, fa adana, pl iskender, pt katmer, fa simit, pl beyti 200 + index, kalıp 0. Geri alma: sunucuda RAPOR=30704 ile ~/bosluk_geri_al.php
- [x] Aynı gece 02:46 (seo:autonomous-growth içindeki content:import-translations) 3240 ve 3510 pt blog yazılarının 11 Ekim pt-BR çevirisini (30634) eski Avrupa Portekizcesi dosyayla ezmişti: 5 alan (3240 özet+gövde, 3510 başlık+özet+gövde). Şimdiki değer 30634 "once" ile birebir, yedek pt-BR. IntegrityReport 30705 ile yedekten geri yazıldı; canlıda iki sayfa 200 + index, 3510'da "pimentão" 44, "pimento vermelho" 0, "frigorífico" 0. Aynı pencerede 64 satır daha yalnız zaman damgası değişti, değer aynı
- [x] Kod 9440ba0: dosya yalnız eksik alanı doldurur (dolu çeviri ezilmez), adımları kalıp dosya reddedilir. Test: ImportRejectsHalfTranslatedRecipeTest iki yeni test, eski kodda kırılıyor
- [x] Yayın 9440ba0 (9372183 + 726e891 + 9440ba0): tam paket 1867/1867 (worktree), release.sh exit 0, /health 9440ba0, invariants temiz. Yayından önce rapor kipi 506 dosya kabul ediyordu; eski kodla bu gece 30702 (15 EN rehber, yazı 4606-4620) ve 1098 alan yeniden ezilecekti
- [x] Canlıda yeni kodla elle `content:import-translations --apply`: 401 geçerli dosya, 0 alan yazıldı, 105 dosya "adımlar kalıp çeviri" ile reddedildi, değişen satır 0, her dilde 123 açık tarif
- [x] 9372183 canlıda: /de/rezepte "Yer fıstığı" 0, "Erdnüsse" 1, "Düşük kalorili" 0; en adana og:image:alt "Adana Kebabı – Adana Kebab Recipe"; pt Kıbrıs tatlısı yazısı kutusu "Todas as receitas verificadas", Türkçe kutu metni 0; /mutfaklar/kibris-mutfagi "Kıbrıs · Kıbrıs Yemek" 0, "niyetleri" 0, index; pt etsiz çiğ köfte "3 dentes de alho, ralados"
- [x] Kullanıcı kararları (12 Ekim): ana sayfa üst düğmeleri uygun (c89469b olduğu gibi kalır); TR hub anahtar kelime şeridi ve "niyetleri için" SSS yanıtı kalksın (9440ba0 ile zaten yayında); Portekizce bayrak 🇧🇷 (db53443, PortugueseLocaleIsBrazilianTest kararı korur)
- [x] db53443 yayında: tam paket 1868/1868, release.sh exit 0; canlıda ana sayfa 🇧🇷 3, 🇵🇹 0; pt sayfa 🇧🇷 6, 🇵🇹 0
- [x] (13 Ekim: 00:00-05:00 arası 0 satır; her dilde 136 açık tarif) Yarın 02:20 ve 02:45 sonrası ölç: content_translations updated_at o pencerelerde 0 satır; pt/pl/fa 123

## 12 Ekim dördüncü tur: EN parantezli ad, damla sakızı, blog kategori adı, EN rehber boşlukları
- [x] Kod a7f0037: EN tekilde parantezden önceki kısım çekilir ("1 kapia pepper (sweet red)"); çoğulda ad olduğu gibi ("2 tırnak pide (Turkish flatbread)"). sayilabilir de Mastixkorn/Mastixkörner, pt lágrima/lágrimas de mástique
- [x] Veri (coni:malzeme 30701): #115 en "mastic tears", de "Mastixkörner", pt "lágrimas de mástique". Canlıda flaouna en "3 mastic tears", de "3 Mastixkörner", pt "3 lágrimas de mástique"
- [x] Kod b413ccd: açık de/ru/pt/pl/fa blog yazıları kategoriyi "Mutfak Rehberi" basıyordu (bağlantı + articleSection); 4aceb39 anahtarları yalnız EN'e ve yayında olmayan kategorilere koymuştu. 10 kategori x 6 dil; kart, karusel, kategori sayfası başlığı, iç bağlantı kicker'ı, llms.txt taxonomy_name($m, $dil) kullanır. Test: yabancı dillerin anahtar kümesi EN ile aynı
- [x] Veri (IntegrityReport 30702, guard elle:en-yazi-bosluk): İngilizce asıllı 15 rehberde içe aktarım biçim etiketlerini boşluklarıyla silmişti ("Quick answer:Turkish eggs, orçılbır(pronounced", "Warm the yogurt.Mix", "Usepul biber"). 3 ajan yalnız boşluk ekledi (667 boşluk), dogrula.py her alanda "girdi = çıktıdan boşluk silinmiş hali" doğruladı, 379 kelime-kelime eklemeyi elle okudum. Canlıda 15 sayfa 200 + index, kalan yapışma 0. Geri alma: bosluk_geri_al.php (RAPOR=30702)
- [x] Yayın b413ccd (a7f0037 + b413ccd): tam paket 1861/1861, release.sh exit 0, /health b413ccd. Canlıda: en adana "1 kapia pepper (sweet red)", iskender "2 tırnak pide (Turkish flatbread)"; de flaouna ölçekli "½ Mastixkorn", "1 Mastixkorn"; de/ru/pt/pl/fa yazıda articleSection Küchenratgeber / Кулинарный гид / Guia de cozinha / Poradnik kuchenny / راهنمای آشپزی, "Mutfak Rehberi" 0, index; kategori sayfası başlığı "Küchenratgeber · Blog", "Kitchen Guide · Blog"
- [x] (yayında 9440ba0 ile, beşinci tur) **9372183 commitli, YAYINDA DEĞİL** (hedefli 51/51; tam paket koşulmadı): Kıbrıs yazısı kutusu 7 dilde; TR hub görünür anahtar kelime şeridi ve "niyetleri için" SSS yanıtı kalktı (KibrisHubFiveVariantDominanceTest çevrildi); tarif ve keşif süzgecinde diyet/alerjen adı kodlu anahtardan (canlıda /de/rezepte "Yer fıstığı", "Düşük kalorili"); yabancı sayfada kart alt'ı ve og/twitter image:alt tarifin o dildeki adı (canlıda en/adana og:image:alt "Adana Kebabı servis edilmiş hâlde"); pt "3 dentes de alho, ralados" (açık tek satır etsiz-cig-kofte-a96hf). Sıradaki adım: tam paket, ff-merge, release, canlıda bu beş noktayı ölç
- [ ] (eski satır) Kod (yama hazır, yayında değil): Kıbrıs yazısı kutusu yabancı sayfada Türkçe ("Kıbrıs Mutfağı / Doğrulanmış tariflerin tamamı tek koleksiyonda / Kıbrıs Yemekleri"); TR hub'da görünür anahtar kelime şeridi ("Kıbrıs · Kıbrıs Yemek · Kıbrıs Yemek Tarif · Yemek Kıbrıs") ve okura "arama niyeti" anlatan SSS yanıtı
- [ ] Bulunan: içe aktarım "delivery" dosyası (content-import/translations/en/blog-*.json) zaten yapışık geldi; kaynak HTML hiçbir yerde yok. Biçim (kalın adım başlığı, italik terim) geri gelmedi, yalnız boşluk

## 12 Ekim üçüncü tur: sayıya uyan malzeme adı, saatli süre, pt hazırlık notu
- [x] Ölçüm: açık 123 DE ve 96 PT sayfada birimsiz satır; katalog adı kimi malzemede tekil kimi malzemede çoğul ("1 Eier", "1 Kartoffeln", "2 Quitte", "1½ Zwiebel", "1 ovos", "4 pão lavash", "2 limão-siciliano")
- [x] Kod 8e786c2: lang/{de,pt}/sayilabilir.php tekil-çoğul çiftleri, adSayiyla de/pt; "de"den sonraki tümleç ve parantez içi değişmez; DE sıfat zinciri ve zarflı ad küçük harf ("2 geröstete rote Paprika", "1 frisch gepresster Zitronensaft"). Tüm DE/PT kataloğu (256/258 ad) ½/1/1½/3 ile benzetildi
- [x] Kod 070cfd0: SureMetni::dakika, 60 dk ve üstü saatle ("6 sa 31 dk"); rozet, kart, meta/og/twitter, adım düğmesi, zamanlayıcı "6:00:00", gömülü kart, haftalık menü, pişirme oturumu, vitrin. Yerelde 1280/375 doğrulandı, kart tek satır
- [x] (3297b03) PT hazırlık notu mastar ("1 cebola, picar"; 58 satırın ~45'i) ve Portekiz Portekizcesi ("do frigorífico", "sumo", "cozer") -> uyumlu sıfat ("1 cebola, picada", "3 tomates, ralados"), Brezilya sözcükleri. RU/PL mastarı o dillerin liste geleneği, dokunulmaz
- [x] İncelenip kusur olmadığı görülen: açık yabancı sayfalarda Türkçe harfli malzeme adları yalnız özel adlar (kaşar, pastırma, erişte, çiğ köfte, tırnak pide), açıklamalı. Katalogdaki "Kırmızı Zwiebel", "Haşlanmış Kuru Fawasserlye/Faágualye" (Fasulye içinde su -> Wasser/água makine değişimi), "Taze Barbunya" vb. yalnız kapalı sayfalarda
- [x] (coni:malzeme 30698, 192 ad) Katalog çöpü: 34 malzemede yabancı ad Türkçeyle aynı ya da "Fawasserlye" bozuk; hepsi yalnız taslak tariflerde (yayında 0). 32 gerçek malzeme 6 dilde yazıldı (mercimek, mantar, pırasa, karnabahar, asma yaprağı, barbunya, haşlanmış kuru fasulye...; pt Brezilya). "Ana Malzeme" (162 kullanım) ve "Özel Baharat" (38) şablon yer tutucusu: bilerek çevrilmedi, o taslaklar yayına geçemesin
- [x] Yayın 070cfd0 (8e786c2 + 070cfd0): tam paket 1858/1858, release.sh exit 0. Canlıda veriyle 161 DE + 126 PT birimsiz satır, sayı uyumsuzluğu 0; HTML'de de/katmer "1 Ei", mercimek "1 Kartoffel", pt/katmer "1 ovo", pt/semifreddo "2 limões-sicilianos"; semifreddo tr "6 sa 31 dk", en/pt "6 h 31 min", de "6 Std. 31 Min." (rozet + meta), adım düğmesi "6 sa zamanlayıcıyı başlat", sunulan JS app-DCzEIQ4P.js
- [x] (IntegrityReport 30699, guard elle:limon-suyu-satiri) Limon suyu adetle yazılmıştı ("2 adet Taze Sıkılmış Limon Suyu" = 2 limonun suyu); yabancı dilde "2 frisch gepresster Zitronensaft", "2 freshly squeezed lemon juice" basılıyordu. 3 satır (8544 enginar dolması, 8574 kolokas kavurma, 8600 kuru bakla ezmesi) malzeme 205/220 -> Limon #7, not "suyu sıkılmış"; miktar ve 30/60 ml aynı. Açık diller korundu. Canlıda: "2 lemons, juiced", "2 Zitronen, ausgepresst", "Лимон — 2 шт., выжать сок", TR "2 adet suyu sıkılmış Limon". Geri alma: payload.once (ingredient_id, preparation_note null)
- [x] og:locale pt_PT -> pt_BR (3297b03); dil seçicideki bayrak kullanıcı kararıyla 🇧🇷 (db53443)
- [x] Yayın 3297b03: tam paket 1859/1859, release.sh exit 0. Canlıda 96 pt sayfada 61 notlu satır, mastar 0, Portekiz sözcüğü 0: "1 cebola, bem picada", "4 tomates, ralados", "750 g de vagem, limpa", "2 limões-sicilianos, raspas e suco"; og:locale pt_BR
- [x] (dördüncü tur: a7f0037 + coni:malzeme 30701) Sıradaki tur: EN "1 kapia peppers (sweet red)" (parantez Str::singular'ı engelliyor; yalnız tekilde parantezden önceki kısmı çek, çoğulda değil: Str::plural "tırnak pide" -> "pides" yapar). Damla sakızı #115 yalnız kibris-usulu-flaouna'da 3 adet: "3 mastic gums", "3 Mastix", "3 resina de mástique" -> coni:malzeme ile en "mastic tears", de "Mastixkörner", pt "lágrimas de mástique" + sayilabilir çiftleri de 'Mastixkorn' => 'Mastixkörner', pt 'lágrima de mástique' => 'lágrimas de mástique' (ad değişimi kodla aynı yayına)
- [ ] Bulunan, küçük: "3 dentes de alho, ralado" not alho'ya uyuyor (dentes'e uyması daha doğal); ru/pl mastar bilinçli

## 12 Ekim ikinci tur: tarif sadakati, süreler, EN bardak etiketi
Veri işleri canlıda yazıldı ve doğrulandı (yayın gerektirmez). Geri alma kayıtları IntegrityReport'ta.
- [x] sucuklu-yumurta (1500): 7 şablon adım gerçek adımlarla, özet ve açıklama listeyle uyumlu (coni:kaynak 30678); tuz "1 çay kaşığı" -> "1 tutam" (30679); süre 15+20 -> 3+7 (30681). Canlıda şablon metin 0, "⏱ 10 dk". İndekse açıldı, sonra geri kapatıldı (30682 geri_alindi): aynı yemeğin fotoğraflı, 7 dilde açık sürümü bakir-tavada-sucuklu-yumurta-z7w5b var; iki sayfa aynı aramada yarışırdı. noindex,follow kalıyor
- [x] pastirmali-yumurta tuz "1 çay kaşığı" -> "1 tutam" (adım "azıcık" diyor; 30681). 7 dilde 200 + index: tutam / pinch / Prise / щепотка / szczypta / pitada / نیشگون
- [x] 44 tek kelimelik TR adım başlığı (26 tarif) tarifin kendi başlık üslubuyla yazıldı (coni:kaynak 30678; "Pişir" -> "Fırında pişir", "Hazırlık" -> "Tavuğu ve sebzeleri doğrama"). Canlıda 27 sayfada 51 başlık var, yabancı sayfa kapanmadı
- [x] Yabancı dilde "Step 1: Adım 1 (Execution & Cooking)" kalıplı adım başlıkları (en 13, de 10, ru 11, pl 139, pt 139): hepsi kapalı sayfalarda, çeviri kapısı engelliyor; canlı kusur değil
- [x] Şablon dolgu açıklamaları (meta açıklama olarak da basılıyordu): limonlu-akdeniz-makarnasi, firin-sebzeli-taco-kasesi, nohutlu-akdeniz-kasesi, baklava-dilimi, sebzeli-tavuk-6tpev; TR çevirilerin anlamına eşitlendi (coni:kaynak 30684). Canlıda kalıp cümle 0, indeks korunuyor
- [x] Adımda geçip listede olmayan malzeme: 127 açık tarif tarandı, 32 isabetten 8'i yanlış alarm (ceviz büyüklüğünde, krema tereyağına döner, "tuz eklemeyin", Kuru kekik/Galeta Unu/Tam yağlı süt zaten listede). Yenibahar #264 açıldı (coni:malzeme 30685). 18 tarife 24 satır (coni:malzeme-ekle 30687): 15 tuz, karabiber, su, yoğurt (isteğe bağlı), lahana sarmasına zeytinyağı + toz tarçın + yenibahar + su, açmaya yumurta sarısı, bazlamaya tereyağı (isteğe bağlı), ayva tatlısına limon. Canlıda 126 istek: açık 105 sayfanın hepsi 200 + index, yeni satır her dilde kendi adıyla, Türkçe sızıntı 0
- [x] Süre alanı adımlarla çelişiyordu (30691): limonlu-semifreddo 25 -> 391 dk (6 saat dondurma), sutlac-kibris 50 -> 325 (4 saat buzdolabı, ~65 dk pişirme), cevizli-sarma-corek 155 -> 185 (ikinci mayalanma), peynirli-kraker 35 -> 65 (30 dk dinlenme)
- [x] Kod (f99f9f5, 0669a7b): EN "cup (200 ml)" sabitti, "½ cup (200 ml) olive oil" toplam gibi okunuyordu; etiket artık toplamı söyler ("½ cup (100 ml)"), hacmi söyleyen satırın altına ikinci "100 ml" basılmaz; tekil/çoğul görünen sayıdan ("1 cups" -> "1 cup", "1 onions" -> "1 onion"); Almancada sıfat miktardan sonra küçük harf ("½ Wasserglas ungesalzene Erdnüsse", 51 malzeme adı)
- [x] Lahmacun: eski /tarifler/lahmacun (Eylül'de Rusça 40 gösterim) ve aynı içerikli iki taslak nüsha (yjv2w, 885j7) 404'tü; üçü geleneksel-citir-lahmacun-23a2f'ye 301 (SlugRedirect 4172-4174, rapor 30693). Canlıda tr/ru/en/de her biri kendi dilindeki sürüme 301. lahmacun-durum-* farklı yemek, çöp içerik, 404 kalıyor
- [x] Başlık (c89469b, kullanıcı: "üst butonlar çok kötü"): masaüstünde 4 ayrı çerçeveli/gölgeli/yükselen kutu (TR, Tema, Yönetim, Hesabım) -> çerçevesiz, menü bağlantılarıyla aynı ağırlık; yalnız Hesabım ince çerçeveli, Üye Ol dolu (eski kural dolgusunu siliyordu); tema yalnız ikon. Telefonda 68 px tema hapı + koyu menü kutusu -> aynı boyda iki ince çerçeveli yuvarlak, 320 px'te taşma yok. Fotoğraflı tarif başlığında kontroller beyaz yazı + yarı saydam koyu zemin. Güneş ikonu 1,98 -> 4,77:1. Ana sayfa explore kapalıyken çift aynı düğme gizlendi. Yerelde 1280/375/320, açık/koyu, oturum açık/kapalı doğrulandı; hedefli 177/177
- [x] Yayın c89469b (f99f9f5 + 0669a7b + c89469b): tam paket 1856/1856, release.sh exit 0, /health c89469b. Canlıda: EN lahana sarması "½ cup (100 ml) olive oil", "3 cups (600 ml) water", 1 kişilik "⅛ cup (25 ml)" ikinci satır boş; DE "1 TL gemahlener Zimt"; ana sayfa başlığı 1280 px sade kontroller, 375 px iki eşit yuvarlak
- [ ] Bulunan: DE sayılabilir ad çoğullaşmıyor ("3 Zwiebel" -> "3 Zwiebeln"; adSayiyla yalnız EN); "⏱ 391 dk" uzun sürelerde saat olarak okunmalı ("6 sa 31 dk"); 60 kısa kart özeti slogan ("Kıbrıs'ın meşhur mahluta çorbası.") - şablon değil, düşük öncelik; ru "Яичные желтки — 1 шт." çoğul ad (Rus liste geleneğinde kabul edilir)
- [x] İncelenip kusur olmadığı görülen: panel admin/seo/opportunities kartında tanımsız --hc-surface-card (kart bilerek koyu, yedek #121212 doğru; token tanımlamak açık temada beyaz kart üstüne beyaz yazı yapardı); taşan (bounce) eğriler (azaltılmış hareket app.css:322'de site genelinde kapalı, üslup tercihi); eski hc-btn ailesi (aynı tokenlar, dokunmatik 44 px bloğunda)

## 12 Ekim: düğmeler (ön yüz denetimi ve sağlamlaştırma)
Ölçüm canlıda (ana sayfa, /tarifler, tarif, /blog; 1024 ve 375 px; açık ve koyu): 17 düğmede 10 farklı imza, birincil düğme 2,71:1, odak halkası 2,18-2,75:1, dokunmatik hedefler 24-40 px.
- [x] 8a00332: --hc-brand-contrast açıkta #171717 (6,28:1), --hc-focus-ring (#c2410c / #fb923c), (pointer: coarse) 44 px, yıldız 44 px, ham turuncu/kehribar düğmeler hc-v3-btn--primary, emoji/ok glifi yerine x-ui.icon, adım rozeti kalktı (de "Timer starten" basıyordu) + recipe.timer.start 7 dil, 5 dilde ters pişirme modu etiketi, SSS kartı tanımsız --hc-surface-card, POST çift gönderim kilidi
- [x] Yerelde (sqlite + seed) doğrulandı: 375 px dokunmatikte tarif/ana sayfa/tarifler/blog 0 küçük hedef, yatay taşma yok; açık ve koyu temada metinli düğmelerde 0 kontrast hatası; kilit POST'ta kapanır, GET ve önlenmiş formda kapanmaz, bfcache'te açılır
- [x] Tam paket 1856/1856; yayın 8a00332 (/health 8a00332). Canlıda: CSS tokenları ve (pointer:coarse) bloğu sunuluyor; ispanakli-borek TR/EN/DE/RU 200; de rozet "Timer starten" 0, aria "Timer für 10 Min. starten", "Bildschirm anlassen"; 1024 px açık ve koyu temada 41 düğmede 0 kontrast hatası, birincil #171717/#ff6b00, odak #c2410c/#fb923c; 375 px dokunmatikte tarif/tarifler/blog 0 küçük hedef, yatay taşma yok (ana sayfa sekmeleri hesaplanan 44 px, ölçülen 41 giriş animasyonu ölçeği)
- [ ] Bulunan: hareket katmanında taşan (bounce) eğriler (tema anahtarı, dil, karusel, sosyal, .hc-motion); eski hc-btn ailesi (30 kullanım) hc-v3-btn ile aynı ölçüde ama ayrı kural; admin/seo/opportunities tanımsız --hc-surface-card

## 11 Ekim üçüncü tur: malzeme satırı dilbilgisi, eksik katalog, TR adım başlıkları
- [x] Yayın 6a86487 (tam paket 1855/1855, /health 6a86487, invariants temiz). Canlıda izmir-sambali 7 dil: ru "Сахарный песок (для сиропа) — 3 стакана", pl "Cukier (do syropu) – 3 szklanki", pt "3 copos de açúcar", tr/en/de/fa değişmedi
- [x] 733b7f9: ru/pl malzeme satırı adı başa alır ("Сахарный песок — 3 стакана", "Cukier – 3 szklanki"); pt ölçüden sonra "de" ("3 copos de açúcar"). Canlıda ölçüldü: ru/pl ad yalın halde, pt "de" yok. pl adet "szt.", pt-BR birimler maço/pacote/punhado ("molho" Brezilya'da sos)
- [x] 6a86487: coni:malzeme Türkçe ad anahtarıyla katalogda olmayan malzemeyi açar; geri al kullanılmıyorsa siler
- [x] Canlıda: coni:malzeme 4 malzeme açtı #260 Çörek Otu, #261 Sumak, #262 Marul, #263 Kek, 24 ad (--geri-al=30637); coni:malzeme-ekle 4 satır (--geri-al=30638; önce bu geri alınır). 4 tarif x 7 dil 200/index, yeni satır her dilde görünüyor: peynirli-borek (çörek otu 1 çay k.), coban-salata (sumak, isteğe bağlı), kekli-ev-yapimi-supangle-cgxs3 (kek 100 g, isteğe bağlı), mercimek-koftesi-s57cm (marul 1 adet). cevizli-sarma-corek düştü: özet düzeltilmiş, adımda pudra şekeri yok
- [x] TR adım başlığı "Adım N": 127 açık tarifin 96'sında 463 adım. 4 yazar ajan + 2 bağımsız doğrulayıcı (456 başlıktan 4'ü düzeltildi, uydurma 0); mekanik kontrol 0 hata. coni:kaynak 95 tarif 461 alan (456 başlık + 5 adım dilbilgisi: "kabarın" -> "kabarmaya bırakın" x3, "kabarana" x1, adana "özdeşleştirin"), --geri-al=30639. Canlıda kalan "Adım N" yalnız sucuklu-yumurta (yer tutucu adımlar, noindex, site haritasında yok; atlandı). Açık yabancı sayfa 738 (dil başına 123) korundu
- [x] (12 Ekim ikinci tur, adımlar yazıldı) sucuklu-yumurta (id 1500): published+verified ama 7 adımı şablon metin ("Malzemeleri yıkayın ve hazırlayın"), sucuk/yumurta geçmiyor; noindex ama erişilebilir. AdSense incelemesi siteye bakıyor: TR kaynağı yazılmalı ya da yayından kaldırılmalı (kullanıcı kararı)
- [ ] Bulunan: (sıfat kısmı 12 Ekim 0669a7b ile düzeldi) de satırında sıfat büyük harf ("½ Wasserglas Ungesalzene Erdnüsse"); katalogda "(Şerbet için)" ekli ayrı malzemeler (Toz Şeker, Su)

## 11 Ekim ikinci tur: sadakat denetimi ve pt-BR
Çalışma klasörü: oturum scratchpad'i `denetim/` (girdi, cikti/<dil>, kontrol.py, DENETIM.md) ve `kaynak/` (TR asıl dışa aktarımı).
- [x] Workflow wf_e48ef093-b13 (34 ajan, 0 hata): 218 eski çift adım sadakat denetimi + bağımsız doğrulayıcı; pt sözlüğü Brezilya; 91 yeni pt sayfası Brezilya'ya hizalandı. Denetçi düzeltmesi: uydurma 92, yanlış anlam 64, dil 53, adım başlığı 40, düşen içerik 28, başlık/özet 16, pt-BR 32; doğrulayıcı 12 geri aldı, 7 düzeltti, 25 kaçırılanı ekledi
- [x] Canlıya yazıldı: coni:malzeme 96 pt adı (geri al: --geri-al=30629); coni:ceviri-toplu --zorla 218 çift (99 tarif), 0 hata, defterde 218 "Sadakat" kaydı. Yeniden dışa aktarım: 218 çiftte DB = dosya, 0 fark; açık sayfa 738 korundu (dil başına 123)
- [x] Canlı doğrulama: en/de mercimek-corbasi-vkztq "25 dk" var, paprikalı tereyağı yok; pt menemen "pimentão", semifreddo "limão-siciliano" ve "creme de leite fresco"; örnek en/de/pt/ru/fa/pl 200, doğru lang, index,follow, kanonik kendisi
- [x] coni:kaynak yayında (9b946f7; tam paket 1849/1849; /health 9b946f7)
- [x] TR kaynak düzeltmeleri (workflow wf_561bd311-178, 16 ajan): 39 tarif incelendi, 34 tarifte 49 TR alan coni:kaynak ile yazıldı (geri al: --geri-al=30630), aynı değişiklik 31 tarifte 181 dil çiftine coni:ceviri-toplu --zorla ile taşındı, 0 hata. Canlı yeniden dışa aktarım: 39 tarifte TR ve çeviri beklenenle birebir (0 fark), açık sayfa 738. Başlık değişiklikleri: Kıymalı Bezelye -> Etli Bezelye Yemeği, Sebzeli Teriyaki -> Soya Soslu Sebzeli Tavuk (tatlandırıcı yok), Kıbrıs Ceviz Macunu -> Kıbrıs Cevizli Sucuğu, Izgara Hellimli Köy Ekmeği -> Izgara Hellimli Tost, Kaymaklı Fırın Ayva -> Kaymaklı Ayva Tatlısı, Taze Naneli -> Naneli Hellimli Börek, Akdeniz Zeytinli -> Akdeniz Zeytinli Ekmeği. Canlıda H1 ve metin doğrulandı (TR/EN/RU/DE/PT)
- [ ] Malzeme listesi eksikleri (coni:kaynak düzenleyemez): adımda kullanılıp listede olmayan tuz/yağ/su/karabiber ~30 tarif; izmir-sambali şerbet malzemesi yok; flaouna kuru üzüm; kekli-supangle kek listede yok. Tam liste: .claude/raporlar/2026-10-11-kaynak-sorunlari.json (50 tarif, denetçi notları)
  Plan (tarandı, yazılmadı): `coni:malzeme-ekle {dosya} {--geri-al=}`. Dosya {"<slug>": [{"ingredient_id", "display_quantity", "display_measure_code", "is_optional", "scaling_behavior", "preparation_note"?}]}.
  Satırı admin akışıyla aynı kur: app/Http/Controllers/Admin/RecipeIngredientController.php::withCanonical (IngredientConversionService::resolve -> canonical_quantity/unit, grams, conversion id/confidence/method; grup "Malzemeler" firstOrCreate; sort_order max+1). Dönüşüm yoksa reddet.
  Yalnız tüm açık dillerde adı çevrilmiş mevcut malzemeyi kabul et (Tuz 27, Su 57, Karabiber 28, Zeytinyağı 5 vb.). Eklenen satır id'leri IntegrityReport'a, --geri-al siler. RecipeIngredientObserver besin değerini geçersiz kılar (beklenen).
  Ölçü kodlarını canlı `units` tablosundan al (is_active, is_user_visible). Miktar adımda yazıyorsa onu kullan, yoksa makul ev ölçüsü + scaling_behavior=taste_adjusted. Sonra workflow: tarif başına eklenecek satırlar + bağımsız doğrulayıcı; canlıda tarif sayfası malzeme listesi ve 738 açık sayfa kontrolü
- [x] coni:malzeme-ekle yazıldı (82ecb3f): panel akışı, dönüşüm yoksa nedenli elle kanonik (site kuralı: çay k. 5 ml, tatlı k. 10 ml, yemek k. 15 ml, çay b. 100 ml, su b. 200 ml; canlıda 737/771 satır bu kuralla), adı 6 dilde çevrilmemiş malzemeyi reddeder, açık sayfa kapanacaksa yazmaz, --dene. Ölçüm: dönüşüm tablosu seyrek (Tuz yalnız adet, Su/Sıvı Yağ yok); katalog 227 tam çevrili malzeme
- [x] Workflow wf_bf653ee5-040 (16 ajan, 0 hata): 50 tarifin 33'ünde 57 eksik satır; doğrulayıcılar hiçbir satırı değiştirmedi, 57'si elle de gözden geçirildi (adım miktarları birebir). Mekanik kontrol 0 HATA. Atlananlar: katalogda yok (kek, sumak, marul, çörek otu, pudra şekeri), yalnız açıklamada (flaouna kuru üzüm), servis eşliği
- [x] izmir-sambali-tatlisi-7708x (50'de yoktu): şerbet hiç yoktu. Bağımsız ajan TÜRKPATENT coğrafi işaret + 2 kaynakla: 3 su b. şeker, 2 su b. su, limon. Dosyalar scratchpad `izmir/` (kaynak.json: adım 1'e şerbet + TR adım başlıkları; aktar/: 6 dil adım 1). 3 satır ekle.json'da (toplam 34 tarif, 60 satır)
- [x] Canlıya yazıldı (6e510e5 yayında): coni:malzeme-ekle 34 tarif 60 satır (--geri-al=30632); izmir coni:kaynak (--geri-al=30633) + ceviri-toplu 6/6; pt blog 2 yazı (rapor 30634). Yeniden dışa aktarım birebir, açık sayfa 738, 238 canlı sayfa 200/index ve malzeme sayısı DB'ye eşit. Grup başlıkları canlıda çevrili (en Main ingredients/Syrup, fa مواد اصلی/شربت)
- [x] pt blog 3240 ve 3510: BlogOriginalityService \bTODO\b /i Portekizce "todo"yu yer tutucu sayıyordu (3240 bu oturumdan önce de kapalıydı). 0f08e54 ile düzeldi, canlı 5c525cf; iki /pt/blog sayfası index,follow, engeller() boş
- [ ] pt blog: yalnız 2 yazı pt'de açık (3240 Kıbrıs tatlısı, 3510 közlenmiş biber konservesi); ikisi de Avrupa Portekizcesi (frigorífico, equipa, tacho, pimento, beringela, pequeno-almoço) + "almosos", "vanilla". Workflow wf_cc421594-111 (4 ajan) pt-BR'ye çevirdi; mekanik kontrol 0 hata (başlık yapısı TR ile aynı, sayılar aynı, AB kelimesi yok). Eski 3240 çevirisi 2 paragraf düşürmüştü, yenisi 42/42. Yedek scratchpad `blogpt/yedek-pt-oncesi.json`; yazım betiği `blogpt/yaz.php` (IntegrityReport coni:yazi-ceviri, --geri-al). Blog çevirisi yazan komut yok
- [x] 6e510e5: çok gruplu tarifte grup başlığı yabancı dilde Türkçe basılıyordu (canlıda ölçüldü: 6 tarif, 30 açık sayfa; yalnız 3 ad: Ana malzemeler, Sos ve lezzet, Şerbet). lang/<dil>/malzeme_gruplari.php; karşılığı yoksa başlık basılmaz. Yayında doğrulanacak
- [ ] Karar bekleyen: 35 tek kelimelik adım başlığı (TR'si de tek kelime), 18 eski EN başlık "<TR ad> – ... Recipe" biçiminde değil
- [ ] pt blog yazı başlıkları Avrupa Portekizcesi ("pimento", tarif sayfasının altındaki ilgili yazılar); blog çevirileri bu turun dışında
- [ ] Yabancı tarif adresi /xx/tarifler/<slug> 301 ile yerel yola gider: /en/recipes, /de/rezepte, /pt/receitas, /ru/recepty, /pl/przepisy, /fa/recipes

## 11 Ekim çeviri turu (canlı 272ece2)
Karar: 1000 makine blog yazısı yayınlanmadı (16 Ağu olayı, 27 Eyl AdSense reddi, AGENTS.md "hiçbir ajan yazı yayınlamaz"). Yerine yayındaki gerçek tariflerin 6 dile gerçek çevirisi.
- [x] 272ece2: coni ceviri chef_tips yazar (TR'de ipucu varsa zorunlu), acildi sayacı ve IndexNow hasTranslation'a bakar. Tam paket 1847/1847
- [x] 91 tarif x 520 dil sayfası açıldı (ajan çevirisi + her partiye bağımsız gözden geçirme + mekanik doğrulama 0 hata). Açık tarif sayfası 218 -> 738; her dilde 123/127. Site haritası dil başına 123
- [x] Onarım: açık sayfalarda 62 tarif x dil "Step N" adım başlığı ve başka tariften kopya ipucu gerçeğiyle değiştirildi (--zorla, 0 hata)
- [x] IndexNow: 582 adres tek gönderim, HTTP 200
- [x] Canlı örnek (en/de/ru/fa/pl/pt): 200, doğru lang, index,follow, kanonik kendisi, 8 hreflang
- [ ] Kapalı kalan 4 tarif: firin-sebzeli-taco-kasesi (TR kaynağında pirinç/fasulye adımı yok, 210C süresiz; TR düzeltilmeden çevrilmez), sutlu-revani-5g74t, etli-kabak-dolmasi-f18sj, sucuklu-yumurta (TR noindex)
- [x] (13 Ekim ölçüldü: üçü de 11 Ekim onarımında düzelmiş; mercimek adım 4 "25 dakika" var, adım 5'te yağ yok; katmer ru adım 3 tam; yeşil mercimek arpa şehriye = orzo) Önceden açık 218 sayfanın adım sadakat denetimi: mercimek-corbasi-vkztq adım 5 paprika yağı uydurma (6 dil), adım 4 "25 dk" düşmüş; yesil-mercimek-corbasi "şehriye"=orzo ve sıra hatası (en/de/fa/pl/pt); katmer ru adım 3 eksik
- [ ] pt malzeme sözlüğü Avrupa Portekizcesi (borrego, sumo de limão, pimento, natas, beringela, sésamo, feijão-verde); "colorau" Brezilya'da annatto demek (tantuni, sucuklu yumurta). Eski pt adım metinleri de Avrupa; yeni metinler Brezilya. Sözlük Brezilya'ya çekilmeli
- [x] (13 Ekim ölçüldü: listedeki 18 tarifin hepsi coni:kaynak 30630/30639 ile düzeltilmiş, çevirileri doğru anlamda) TR kaynak düzeltmeleri (sayfada görünür; miktarlar DEĞİL, onlar doğru): başlık-içerik çelişkisi: izgara-hellimli-koy-ekmegi (tost ekmeği), kibris-ceviz-macunu (adımlar soutzoukos), kaymakli-firin-ayva-tatlisi (tencerede; "su eklenmez" ama su var), kiymali-bezelye (kuşbaşı), kibris-hellimli-taze-borek ("Taze Naneli" ama kuru nane), akdeniz-zeytinli (başlık yarım). Açıklamada olup tarifte olmayan: cevizli-sarma-corek tarçın, lor-peynirli-tepsi-boregi maydanoz/karabiber, su-boregi maydanoz, akdeniz-yassi ve soganli-corek baharat, havuc-corbasi "kremasız" ama krema. Çelişki: kilis-cennet-camuru soğuk/sıcak şerbet, iskender sos sırası, citir-sigara-boregi "8 üçgen". Listede olmayan malzeme (tuz/su/yağ/baharat) ~25 tarifte. Ayrıntı: scratchpad kaynak_sorunlari.md ve ajan raporları
- [ ] Not: dışa aktarımda pivot `unit` kanonik birimdir (g/litre); sayfa display_measure_code gösterir ("5 su bardağı"). Ham birimle miktar denetimi yanlış alarm verir

## Yayın 10 Ekim (blog ve politika: 762c7f4, 90760c9, 7c3de40)
- [x] 762c7f4: EN politika sayfalarında "Diğer politikalar" kartları İngilizce. Canlıda /en/gizlilik, /en/cerezler, /en/kullanim-kosullari doğrulandı; TR değişmedi
- [x] 90760c9: EN blog sayfalama /en/blog?page=N kalıyor (ana sayfa, kategori, etiket). Canlıda /en/blog?page=2 200; /blog TR sayfaları 200
- [x] 7c3de40: sayfa sayısını aşan blog adresi 404. Canlıda /en/blog?page=5 404 (EN son sayfa 4); /blog TR son sayfa 9 (9 yazı), page=10 404
- [x] Testler: LocalePagesOnlyShowTranslatedContentTest 8/8; tam suite 1846/1846
- [ ] Kategori ve etiket sayfalaması canlıda doğrulanamadı: çok sayfalı kategori ya da etiket yok. /en/blog/kategori/kibris-mutfagi 200 ama 0 yazı
- [ ] EN footer "Haftalık menü" bağlantısı Türkçe /haftalik-menu sayfasına gidiyor. Karar bekleniyor: EN'de gizle (Türkçeye özel hizmet bağlantılarıyla aynı kapı) ya da sayfayı çevir
