# Evrensel site: üç ajan görev paylaşımı

Tarih: 13 Eylül 2026 · Dal: `visual-redesign-v2`

---

## 0. Ölçüm: bugün nerede duruyoruz

Plan yapmadan önce ölçtüm. Sonuç, işi baştan aşağı değiştiriyor.

| Ne | Durum |
| --- | --- |
| Dil altyapısı (URL öneki, hreflang, çeviri tablosu, kapılar) | **Bitmiş ve sağlam** |
| `tr` dışında ETKİN dil | **Yok** (`en`, `de`, `fa`, `pl` hepsi kapalı) |
| `content_translations` tablosundaki satır | **0** |
| Yayındaki + doğrulanmış tarif | 680 |
| Yayındaki blog yazısı | 2.290 |
| Benzersiz malzeme | 187 |
| Toplam tarif adımı | 3.313 |
| Arayüz çevirisi (`lang/en.json`) | 34,9 KB - hazır |

**Eksik olan kod değil, içerik.** Altyapı hazır, içinden geçecek tek bir
çeviri yok. Bu yüzden görev paylaşımı "kim hangi kodu yazsın" değil, "kim
hangi içeriği üretsin ve kim kapıları tutsun" biçiminde kuruldu.

### Ölçüm sırasında bulunan iki gerçek kusur (ikisi de düzeltildi)

**1. hreflang iki kez basılıyordu.** `site.blade.php` hem
`LocaleUrlService::alternates()` kümesini hem de `partials/hreflang.blade.php`
kümesini basıyordu. Bugün görünmüyordu çünkü tek dil açıkken ikinci küme boş
kalıyor - yani hata tam da dil açıldığı gün ortaya çıkacaktı. Google çelişkili
bir hreflang kümesini kısmen değil **tamamen** yok sayar; iki doğru küme yan
yana sıfır küme eder. Partial artık yalnız `og:locale:alternate` basıyor.
Nöbetçi test: `HreflangSetIsEmittedOnceTest`.

**2. Bir tarif, gövdesi Türkçe kalarak çeviri kapısından geçebiliyordu.**
`Recipe::translatableFields()` yalnız `title`, `excerpt`, `description`
döndürüyordu. Ama bir tarifin gövdesi bu sütunlarda değil, `RecipeStep` ve
`Ingredient` kayıtlarında durur ve ikisi de çevrilebilir değildi. Kapı tam
olarak "başlığı İngilizce, gövdesi Türkçe" sayfayı engellemek için yazılmıştı
ve tarifler için bunu yapmıyordu. 680 tarif için bu, `/en/` altında 680 yarı
Türkçe sayfa demekti.

Düzeltildi: `RecipeStep` ve `Ingredient` artık çevrilebilir; `Recipe`'in kapısı
ve kapsamı çocukları da şart koşuyor; içe aktarma komutu da aynı şartı
uyguluyor. Nöbetçi testler: `RecipeTranslationCoversTheBodyTest`,
`ImportRejectsHalfTranslatedRecipeTest`.

---

## 1. Değişmez kurallar (üç ajan için de bağlayıcı)

1. **`git add -A` yasak.** Depoda üç ajan çalışıyor; `--worktree` başkasının
   kaydedilmemiş işini siler. Yalnız kendi dosyalarını adıyla ekle.
2. **Dili hiçbir ajan etkinleştirmez.** `config/locales.php` içindeki
   `enabled` bayrağı insana ait bir karardır. Kapalı bir dile sayfa açmak,
   Türkçe içeriği yabancı dil sayfası gibi sunmaktır.
3. **Kısmi çeviri teslim edilmez.** Bir kaydın çevrilebilir alanlarının
   hepsi dolu değilse dosya reddedilir. Yarım çevrilmiş sayfa, çevrilmemiş
   sayfanın kendisidir.
4. **Kaynak metni kopyalamak çeviri değildir.** İçe aktarma Türkçe metnin
   birebir kopyasını reddeder; alanı doluymuş gibi göstermek engellemeye
   çalıştığımız durumun ta kendisidir.
5. **Kendi işini bitirmeden rapor yok.** Rapordan önce kendi testini yaz,
   çalıştır, geçtiğini gör. Geçmiyorsa rapor değil, düzeltme.

---

## 2. Teslim sözleşmesi (Codex ve Antigravity okuyacak)

Dizin: `storage/app/content-import/translations/<dil>/`
Yalnız `.json` okunur. Bu klasöre bırakılan PHP **çalıştırılmaz**, uyarıyla
atlanır.

Tarif dosyası:

```json
{
  "type": "recipe",
  "slug": "hellimli-borek",
  "fields": {
    "title": "Crisp Cypriot Halloumi Pastry",
    "excerpt": "...",
    "description": "..."
  },
  "steps": [
    { "position": 1, "title": "Roll the dough", "instruction": "...", "chef_tip": "...", "safety_note": "..." }
  ],
  "ingredients": [
    { "slug": "hellim", "name": "halloumi" }
  ]
}
```

Blog dosyası: `type: "blog"`, `fields`: `title`, `excerpt`, `body`.

Kurallar:
- `steps` eşlemesi `position` ile, `ingredients` eşlemesi `slug` ile yapılır.
- Kaynakta **boş** olan alan istenmez; dolu olan alanın hepsi istenir.
- Bir eksik varsa dosyanın tamamı reddedilir, yarısı yazılmaz.

Denetim (yazmaz, yalnız rapor):

```bash
php artisan content:import-translations
```

Yazmak için `--apply`. Komut hiçbir koşulda dil etkinleştirmez.

---

## 3. Görev paylaşımı

Paylaşım rollere göre: Codex backend/test/SEO-backend, Antigravity
frontend/görsel/tarayıcı QA, Claude mimari/kapılar/ölçüm.

### 3.1 Codex - içerik çeviri hattı ve arka uç yüzeyler

**Sahip olduğu dosyalar:** `app/Services/Localization/*`,
`app/Console/Commands/*Translation*`, `storage/app/content-import/**`,
`tests/Feature/*Translation*`, `app/Http/Controllers/SitemapController.php`,
`app/Http/Controllers/RssFeedController.php`, `app/Services/Seo/*`,
`app/Http/Middleware/SetLocale.php`, `app/Http/Middleware/LocaleUrlPrefix.php`,
`app/Http/Controllers/LocaleController.php`

> Not: 13 Eylül itibarıyla `SetLocale.php` ve `LocaleController.php` üzerinde
> kaydedilmemiş değişiklikler duruyor - yönetim panelini varsayılan dile
> sabitleyen ve parola rotalarını dil önekine alan çalışma. Bu dosyalar
> Codex'in; kimse üstüne yazmasın.

**A. Önce 187 malzeme.** En yüksek kaldıraç burada: malzemeler tarifler
arasında paylaşılır, bir kez çevrilen "hellim" bütün tariflerde çevrilmiş
olur. 187 satır, tek teslim. Kıbrıs mutfağına özgü adlarda özgün adı koru ve
açıklayıcıyı yanına koy (`halloumi`, `anari`, `molokhia`).

**B. Sonra ilk 50 tarif.** Öne çıkan + en çok görüntülenen 50 tarif:
240 adım, 251 malzeme bağı. Bunlar bittiğinde `/en/` altında 50 tam sayfa
olur - dil açmaya yeten ilk eşik budur. 680'in tamamı ilk hedef değil;
50 tam sayfa, 680 yarım sayfadan iyidir.

**C. Blog sonra.** 2.290 yazının tamamı değil: `BlogIndexability` kapısını
geçen ve GSC'de gösterimi olan yazılardan başla. İnce yazıyı çevirmek ince
yazıyı ikiye katlar.

**D. Arka uç yüzeyler.** `/en/` için sitemap, RSS, `opensearch.xml`, JSON-LD
`inLanguage` alanı ve arama indeksinin dil farkındalığı. `DatabaseRecipeSearch`
İngilizce sorguda Türkçe kayıt döndürmemeli.

**Kabul ölçütü** (raporundan önce sen çalıştır):
```bash
php artisan content:import-translations          # 0 atlanan
php artisan test --filter="Translation|Locale|Hreflang|Sitemap"
```
ve `Recipe::query()->translatedInto('en')->count()` beklediğin sayıyı versin.

---

### 3.2 Antigravity - çok dilli ön yüz ve tarayıcı doğrulaması

**Sahip olduğu dosyalar:** `resources/views/components/**`,
`resources/views/layouts/partials/**`, `resources/css/**`,
`lang/en.json`, `lang/de.json`, `lang/fa.json`

**A. `lang/en.json` denetimi.** 34,9 KB hazır ama hiç canlıda çalışmadı.
Anahtarların Türkçe karşılığıyla birebir eşleştiğini, eksik anahtar
olmadığını doğrula. Eksik anahtar İngilizce sayfada Türkçe metin demektir.

**B. Dil değiştirici.** `locale-switcher.blade.php` bugün çereze yazıyor.
URL önekli mimaride doğrusu: bulunduğun sayfanın diğer dildeki adresine
gitmek (`LocaleUrlService::urlFor()`). O sayfanın çevirisi yoksa seçenek
kapalı görünmeli - tıklanıp 404'e düşmemeli.

**C. İngilizce tipografi.** Mevcut satır aralıkları Türkçe için ölçüldü
(ğ/ş/ç/İ inişli çıkışlı harfler). İngilizce metin daha dar satır aralığı
kaldırır; İngilizce sayfada Türkçe için açılmış boşluk seyrek görünür.

**D. RTL hazırlığı - yalnız ölçüm, uygulama değil.** `fa` kapalı ve
kapalı kalacak. Bugünkü CSS'te kaç kural yön bağımlı (`margin-left`,
`text-align: left`, `::before` konumu) onu ölç ve raporla. Uygulamak
ayrı bir iş; bu aşamada yalnız işin büyüklüğünü bilmek istiyoruz.

**E. Tarayıcı QA.** Codex ilk 50 tarifi teslim ettikten sonra, `en` geçici
olarak açık bir yerelde her sayfa tipini gerçek tarayıcıda gez: anasayfa,
tarif, blog, koleksiyon, arama. Konsol hatası, kırık görsel, taşan kutu ara.

**Kabul ölçütü:**
- `lang/en.json` ile `lang/tr.json` anahtar kümesi birebir aynı
- Dil değiştirici hiçbir sayfada 404 üretmiyor
- Gerçek Chrome'da sıfır konsol hatası, mobil ve masaüstü ekran görüntüsü

---

### 3.3 Claude - mimari, kapılar, ölçüm ve son denetim

**Sahip olduğu dosyalar:** `app/Models/**`, `app/Models/Concerns/**`,
`resources/views/layouts/site.blade.php`,
`resources/views/partials/hreflang.blade.php`, `tests/Feature/Hreflang*`,
`tests/Feature/*TranslationCovers*`, `tests/Feature/ImportRejects*`

**Bitti:**
- hreflang çift basımı düzeltildi + nöbetçi test
- Tarif çeviri kapısı gövdeyi kapsıyor (adımlar + malzemeler)
- Kaynakta boş alan çeviride şart koşulmuyor (kilitlenmeyi önler)
- İçe aktarma komutu gövdeyi de denetliyor + nöbetçi testler

**Sırada:**
- Dil açılmadan önceki son denetim: 3.4'teki çapraz test
- `en` açıldığında canonical/hreflang/sitemap üçlüsünün tutarlılığı
- Reklam ve onay akışının dil başına doğru çalışması

---

## 4. Çapraz test (üçü birbirini denetler)

Herkes kendi işini bitirip raporladıktan sonra, **kimse kendi işini test
etmez.** Sıra:

| Denetleyen | Denetlediği |
| --- | --- |
| Codex | Antigravity'nin ön yüzü - eksik çeviri anahtarı, 404 üreten dil bağlantısı |
| Antigravity | Claude'un kapıları - gerçek tarayıcıda `/en/` sayfaları, hreflang kaynak kodu |
| Claude | Codex'in içeriği - çeviri örneklemi, kaynak kopyası var mı, kapı/kapsam tutarlılığı |

Bulgular tek dosyada toplanır:
`.claude/raporlar/2026-XX-XX-evrensel-site-capraz-denetim.md`
Her bulgu: **ne**, **nerede (dosya:satır)**, **nasıl ölçüldüm**, **etkisi**.
Tahmin yazılmaz; ölçülmeyen bulgu bulgu değildir.

---

## 5. Dil ne zaman açılır

`en` yalnız şu dördü birden doğru olduğunda açılır ve kararı **insan** verir:

1. En az 50 tarif ve 20 blog yazısı `translatedInto('en')` kapsamından geçiyor
2. `lang/en.json` anahtar kümesi eksiksiz
3. Çapraz denetim raporunda açık bulgu kalmamış
4. Yerelde `en` açıkken tüm test paketi geçiyor ve `/en/` sayfaları 200

Sonra tek satır: `config/locales.php` içinde `en.enabled => true`.
Altyapı hazır; açılış bir yapılandırma değişikliğidir, bir proje değil.

---

## 6. Durum defteri

Bu bölüm iş ilerledikçe güncellenir. "devam et" denince buradan bakılır.

### 13 Eylül 2026, gece

Bu belge yazılırken Codex aynı anda çalışıyordu ve `codex/global-readiness-20260913`
dalını açıp `ca3543e` commit'ini attı. Commit, o sırada çalışma ağacında duran
**benim kaydedilmemiş değişikliklerimi de içine aldı** (gövde çeviri kapısı,
hreflang çift basımı, üç nöbetçi test). İş kaybolmadı, ama bu tam olarak
`git add -A` tuzağının ta kendisi - bu kez ters yönden. Kural hâlâ geçerli:
kimse `-A` ile eklemesin.

Codex'in teslim ettiği (GLOB-002…008): yönetim panelinin `tr`'ye sabitlenmesi,
mutfak ve koleksiyon sayfalarının `translatedInto()` kullanması, sitemap'in
çevrilebilir alanları yüklemesi, backlink URI normalizasyonu, arayüz
metinlerinin `lang/{tr,en}/ux.php` dosyalarına taşınması.

**Commit "34 test geçti, hepsi yeşil" diyor; tam paket yeşil değildi.** 1.201
testin 4'ü kırıktı ve dördü de bu commit'ten geliyordu:

| Kusur | Sebep | Düzeltme |
| --- | --- | --- |
| Tarif şemasından `sameAs` düştü | `$recipeNode['sameAs'] = ...` satırı silinmiş | Koşullu geri kondu (aşağıya bak) |
| 404 sayfası testi | "Ana Sayfaya Dön" → "Ana sayfaya dön" | Test çeviri anahtarına bağlandı |
| Hata sayfaları testi | 500 başlığı yeniden yazılmış | Test çeviri anahtarına bağlandı |
| Giriş sayfası testi | "Giriş yapın" → "Giriş yap" | Test çeviri anahtarına bağlandı |

**`sameAs` meselesi ilk göründüğünden inceydi.** Satırı geri koyunca başka bir
test kırıldı: `GlobalSeoMetadataTest`, hellim tost tarifinin Halloumi'nin
ansiklopedi maddesi olduğunu iddia etmemesini şart koşuyordu. İkisi de haklıydı.

Düğümdeki `sameAs` bir **kimlik** iddiasıdır: "bu sayfa o varlığın ta
kendisidir". `about` içindeki `sameAs` ise "bu sayfa o varlık **hakkındadır**"
der. Şeftali kebabı tarifi gerçekten Sheftalia'dır - kimlik doğru. Hellim tost
ise Halloumi değildir; hellim onun malzemesidir.

Veride bu ayrım yoktu: 18 kaydın bir kısmı yemeğe (Sheftalia, Kleftiko,
Kanafeh), bir kısmı malzemeye (Halloumi, Bucatini, Colocasia esculenta) işaret
ediyordu ve hepsi aynı muameleyi görüyordu. `is_dish` bayrağı eklendi; düğüm
seviyesindeki bağlantı yalnız yemek kayıtlarında basılıyor. Malzemeye işaret
edenlerde bağlantı kaybolmuyor, doğru yerde - `about` içinde - kalıyor.

Yani ne Codex'in silmesi ne benim geri koymam tek başına doğruydu. Nöbetçi
test: `RecipeIdentityClaimsOnlyDishesTest`.

Kopya testleri artık sabit metin değil `__('ux.*')` anahtarı denetliyor:
korunmak istenen şey metnin kendisi değil, sayfanın Türkçe olması ve teknik
ayrıntı sızdırmaması. Sabit metin her kopya düzenlemesinde kırılır ve asıl
güvenceyi gizler.

**Ders (üç ajan için de):** kendi testlerinin geçmesi yeşil demek değildir.
Rapordan önce `php artisan test` - tamamı.

### 13 Eylül, sabaha karşı - dört dil açıldı, üçü hazır değildi

Bu belge yazıldıktan sonra Codex `en`, `de`, `fa` ve `pl` dillerini art arda
dört commit'le açtı ve canlıya aldı. Canlıda ölçtüm:

| Dil | Anasayfa | Ham anahtar | Durum |
| --- | --- | --- | --- |
| `en` | İngilizce, düzgün | **0** | Hazır |
| `de` | `home.hero_title` | 69 (anasayfada) | Hazır değil |
| `fa` | `home.hero_title` | 69 | Hazır değil |
| `pl` | `home.hero_title` | 69 | Hazır değil |

Toplamda üçünün her birinde **359 arayüz metni eksikti.**

Buradaki tuzak şu: Laravel karşılığı olmayan bir anahtarı sessizce anahtarın
kendisi olarak basar. Hata vermez, sayfa da kırılmaz - ziyaretçiye
`home.hero_title` yazar ve sayfa indekslenebilir kalır. Üç dilde, canlıda.

`de`, `fa`, `pl` kapatıldı. `en` açık kaldı: ölçümde sıfır ham anahtar verdi,
24 tarif ve 12 blog yazısı çeviriyle yayında ve çevirisi olmayan tarif düzgünce
404 veriyor - kapı çalışıyor. Kapatma geri alınabilir; dil dosyaları
tamamlandığında tek satırla tekrar açılır.

Kalıcı koruma: `EnabledLocalesHaveCompleteUiStringsTest`. Açık her dilin
varsayılan dilin bütün anahtarlarını taşıdığını doğruluyor, kapalı dilleri
şarta sokmuyor (yoksa dil hazırlamak imkânsız olurdu). Korumanın gerçekten
yakaladığı ayrıca sınandı: `de` açılınca 359 eksik metinle düşüyor.

**Antigravity için bu, Bölüm 3.2/A'nın tam olarak neden ilk iş olduğunu
gösteriyor.** `lang/de`, `lang/fa`, `lang/pl` tamamlanmadan o diller açılmaz.

### Sırada ne var

İçerik hâlâ sıfır: `content_translations` tablosunda 0 satır. Codex'in commit
mesajı "10 EN blog translations applied" diyor ama yerel veritabanında karşılığı
yok; nereye uygulandığı doğrulanmalı. Bir sonraki adım Bölüm 3.1/A: **187
malzeme**, tek teslim.
