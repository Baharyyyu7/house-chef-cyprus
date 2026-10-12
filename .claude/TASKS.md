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
- [ ] **Yabancı sayfalarda kalan Türkçe** (28 Eylül ikinci tarama sonrası).
      Arayüz, çerez bandı, malzeme seçicisi, taksonomi, diyet adları, blog
      dizini ve SSS düzeldi; ticari bloklar yabancı dilde gizli. Kalan:
      blog kategori adları (BlogCategory çevrilmiyor), Farsça blog
      açıklamalarındaki kalıp ("بررسی تخصصی... در زمینه <Türkçe başlık>"),
      tarifin ipucu/sık hata/saklama alanlarının çevirisi (şimdilik yabancı
      dilde gizli). Özel şef/catering hizmetinin yabancı dilde sunulması
      kullanıcı kararı.
- [ ] **Tarif tutarlılığı**: adımda geçip listede olmayan malzeme. 28 Eylül'de
      31 tariften 15'i düzeltildi (27 satır, yedek
      `yedekler/2026-09-28-eksik-malzeme-eklenen.json`). Kalan 16'nın çoğu
      yanlış alarm ("krema kıvamı", "tereyağına döner"); gerçek olanlar:
      ezogelin tereyağı, babagannuş maydanoz, Ali Nazik ve mercimek köftesi
      salça, havuç çorbası dereotu, lahana sarması tarçın, ev açması yumurta,
      bazlama tereyağı, ayva tatlısı limon. Denetim betiği scratchpad'de
      (denetim2.php); `İ` küçültmesi "İrmik"i kaçırıyor.
- [ ] **100 tarifte Türkçe adım başlığı "Adım 1..N"** kalıbı. Çeviri
      partilerinde başlıklar gerçekleriyle yazılıyor; Türkçesi ayrıca
      düzeltilmeli.
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
- [ ] Eski blog kategori çevirisi (4aceb39) ve medya beyanı zamanları (1f2c561) bu yayınla birlikte gidiyor; canlıda kontrol edilmedi

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
- [ ] sucuklu-yumurta (id 1500): published+verified ama 7 adımı şablon metin ("Malzemeleri yıkayın ve hazırlayın"), sucuk/yumurta geçmiyor; noindex ama erişilebilir. AdSense incelemesi siteye bakıyor: TR kaynağı yazılmalı ya da yayından kaldırılmalı (kullanıcı kararı)
- [ ] Bulunan: de satırında sıfat büyük harf ("½ Wasserglas Ungesalzene Erdnüsse"); katalogda "(Şerbet için)" ekli ayrı malzemeler (Toz Şeker, Su)

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
- [ ] Önceden açık 218 sayfanın adım sadakat denetimi: mercimek-corbasi-vkztq adım 5 paprika yağı uydurma (6 dil), adım 4 "25 dk" düşmüş; yesil-mercimek-corbasi "şehriye"=orzo ve sıra hatası (en/de/fa/pl/pt); katmer ru adım 3 eksik
- [ ] pt malzeme sözlüğü Avrupa Portekizcesi (borrego, sumo de limão, pimento, natas, beringela, sésamo, feijão-verde); "colorau" Brezilya'da annatto demek (tantuni, sucuklu yumurta). Eski pt adım metinleri de Avrupa; yeni metinler Brezilya. Sözlük Brezilya'ya çekilmeli
- [ ] TR kaynak düzeltmeleri (sayfada görünür; miktarlar DEĞİL, onlar doğru): başlık-içerik çelişkisi: izgara-hellimli-koy-ekmegi (tost ekmeği), kibris-ceviz-macunu (adımlar soutzoukos), kaymakli-firin-ayva-tatlisi (tencerede; "su eklenmez" ama su var), kiymali-bezelye (kuşbaşı), kibris-hellimli-taze-borek ("Taze Naneli" ama kuru nane), akdeniz-zeytinli (başlık yarım). Açıklamada olup tarifte olmayan: cevizli-sarma-corek tarçın, lor-peynirli-tepsi-boregi maydanoz/karabiber, su-boregi maydanoz, akdeniz-yassi ve soganli-corek baharat, havuc-corbasi "kremasız" ama krema. Çelişki: kilis-cennet-camuru soğuk/sıcak şerbet, iskender sos sırası, citir-sigara-boregi "8 üçgen". Listede olmayan malzeme (tuz/su/yağ/baharat) ~25 tarifte. Ayrıntı: scratchpad kaynak_sorunlari.md ve ajan raporları
- [ ] Not: dışa aktarımda pivot `unit` kanonik birimdir (g/litre); sayfa display_measure_code gösterir ("5 su bardağı"). Ham birimle miktar denetimi yanlış alarm verir

## Yayın 10 Ekim (blog ve politika: 762c7f4, 90760c9, 7c3de40)
- [x] 762c7f4: EN politika sayfalarında "Diğer politikalar" kartları İngilizce. Canlıda /en/gizlilik, /en/cerezler, /en/kullanim-kosullari doğrulandı; TR değişmedi
- [x] 90760c9: EN blog sayfalama /en/blog?page=N kalıyor (ana sayfa, kategori, etiket). Canlıda /en/blog?page=2 200; /blog TR sayfaları 200
- [x] 7c3de40: sayfa sayısını aşan blog adresi 404. Canlıda /en/blog?page=5 404 (EN son sayfa 4); /blog TR son sayfa 9 (9 yazı), page=10 404
- [x] Testler: LocalePagesOnlyShowTranslatedContentTest 8/8; tam suite 1846/1846
- [ ] Kategori ve etiket sayfalaması canlıda doğrulanamadı: çok sayfalı kategori ya da etiket yok. /en/blog/kategori/kibris-mutfagi 200 ama 0 yazı
- [ ] EN footer "Haftalık menü" bağlantısı Türkçe /haftalik-menu sayfasına gidiyor. Karar bekleniyor: EN'de gizle (Türkçeye özel hizmet bağlantılarıyla aynı kapı) ya da sayfayı çevir
