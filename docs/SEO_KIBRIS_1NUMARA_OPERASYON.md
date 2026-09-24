# SEO Kıbrıs 1 Numara Operasyonu — koordinasyon, harita, paket, log

Hedef sorgular: `kıbrıs`, `kıbrıs yemek`, `kıbrıs yemekleri`, `kıbrıs yemek tarifleri`,
`yemek kıbrıs`, `kıbrıs mutfağı` (+ EN: `Cypriot recipes`, `Cyprus traditional food`).
Başlangıç ölçümü: GSC 28 gün (10 Ağu–6 Eyl 2026): 5 tıklama, 91 gösterim, konum 24,2.
Gözlemlenen ilk sayfa (Eyl 2026): nefisyemektarifleri, lezzet.com.tr, yemek.com,
tatilbudur, jollytur (TR); visitcyprus, cyprus-spirit, cypruspassion, wikipedia (EN).
housechefcyprus.com gözlemlenen ilk sayfalarda yok.

Dürüst sınır: tek kelime `kıbrıs` gıda-dışı niyet taşır (ülke/seyahat/haber);
orada kalıcı 1 numara gerçekçi hedef değildir. Gerçek hedef Kıbrıs YEMEK
niyetli tüm sorgularda ilk sayfa, sonra 1 numara. Sıralama garantisi verilmez.

---

## 1. Anahtar kelime haritası (varyant → tek kanonik)

| Varyant | Kanonik URL | Not |
|---|---|---|
| kıbrıs yemekleri, kıbrıs yemek, yemek kıbrıs, kıbrıs yemek tarif | `/mutfaklar/kibris-mutfagi` | Hub; 5 varyant tek adreste, 301 alias aktif |
| kıbrıs mutfağı | `/mutfaklar/kibris-mutfagi` | Aynı hub (niyet aynı) |
| kıbrıs usulü + yemek adı | ilgili `/tarifler/{slug}` | Tekil tarif niyeti |
| Cypriot recipes, Cyprus traditional food | `/en/` hub + EN çeviriler | İngilizce boşluk en büyük fırsat |
| hellim, şeftali kebabı, molehiya, kolokas, kleftiko | ilgili tarif + hub malzeme sözlüğü | EN kaynak yok denecek kadar az |

Kural: aynı niyete ikinci sayfa açılmaz. Yeni sayfa yalnız gerçek içerik
boşluğuna (EN çeviri, video, özgün rehber) açılır ve taslak → insan onayı.

## 2. Paralel ajan lane planı (çakışma önleme)

| Lane | Sahip | Dokunur | Dokunmaz |
|---|---|---|---|
| Uygulama kodu, tasarım, test, deploy | Claude Code | `app/`, `resources/views/`, `routes/`, `config/` (locales.enabled hariç) | medya onayı, çeviri kayıtları |
| Görsel üretim + teslim | Antigravity | `media:queue` claim/complete, `upload-recipe-image.sh`, sunucu `media-import/recipes/` | kod, onay durumu |
| Tarif kayıtları, çeviri altyapısı | Codex | tarif seed/çeviri kayıtları, `content:translation-readiness` | `config/locales.php` enabled, yayın kararı |
| Medya onayı, yayın kararı | İnsan | `/yonetim/medya`, blog/tarif yayın | — |

Kilit kuralları:
- Aynı dosyayı iki ajan aynı anda düzenlemez; girmeden önce `git log --oneline -5`.
- Commitlemeden önce `git status --short`: yalnız kendi lane dosyaların
  stage'lenir (`git add <dosya>`), tüm ağaç asla (`git add -A` yasak).
  2026-09-16'da tüm kirli ağaç tek committe karıştı (6f20d24); tekrarlanmaz.
- `config/locales.php` içindeki `enabled` alanına hiçbir ajan dokunmaz.
- Hiçbir ajan yazı yayınlamaz, dil açmaz, toplu onay vermez.
- `recipe-images:generate-fallbacks` yerelde çalıştırılmaz.
- Silme sırası: önce yükle → doğrula → sonra sil.
- Kilitli iş 15 dkda havuza döner; başkasının `claimed` işine dokunulmaz.

## 3. Meşru backlink paketi (otomatik gönderim YOK)

Yasak: otomatik backlink, dizin bombardımanı, link takası, satın alınmış
link, toplu e-posta. Bunlar link şemasıdır; cezası manuel işlemdir. Site
geçmişte ölçekli üretimden 3.649 sayfalık spam olayı yaşadı; tekrarı
yasaktır. Tüm temaslar insan tarafından, tek tek, şeffaf yapılır.

| Fırsat | Neden uygun | Durum |
|---|---|---|
| visitcyprus.com/discover-cyprus/gastronomy | resmi turizm hubı, tamamlayıcı tarif kaynağı | gönderilmedi |
| cyprusfoodmuseum.com tarif arşivi | müze-blog kaynak iş birliği | gönderilmedi |
| publications.gov.cy 2026 tarif kitabı | devlet kaynağına TR/EN tamamlayıcı | gönderilmedi |
| the-northcyprus.com, northcyprus-guide, cyprus-spirit, cypruspassion | destinasyon-tarifi tamamlayıcı | gönderilmedi |
| kibris.com.tr, sondakikacyprus | bölgesel çapraz referans / uzman alıntısı | gönderilmedi |
| r/cyprus yemek başlıkları (3 adet) | soruya gerçek yanıt + tarif linki (nofollow) | gönderilmedi |
| Wikipedia Cypriot_cuisine / Kıbrıs_Türk_mutfağı | yalnız tartışma sayfasında şeffaf kaynak önerisi, çıkar çatışması beyanıyla | gönderilmedi |
| Kurumsal profiller (tutarlı ad + adres) | gerçek profil bağlantısı | gönderilmedi |

Mesaj şablonu: "Merhaba, [belirli yazınız] içinde [konu] anlatılıyor.
[URL] kaynağımızda [gerçek ek değer] bulunuyor. Okuyucunuz için
yararlıysa kaynak olarak değerlendirebilirsiniz." Sponsorluk varsa açıklanır.

Ön koşul (BACKLINK_STRATEJISI.md sırası): 1) görseller tamamlansın
(`media:gap-queue`, 59 eksik zengin sonuç engeli), 2) Search Console
bağlansın, 3) en iyi 30-40 tarif İngilizceye çevrilsin (çeviri Codex lane).

## 4. Otomasyon sınırı (mitoz yok)

Otomatik: ölçüm (`seo:health`, `seo:morning-brief`), tarama denetimi,
sitemap (`media:gap-queue` 01:13, `seo:morning-brief` 06:59,
`seo:autonomous-growth` 02:45 scheduler'da).
Otomatik OLMAZ: içerik yayınlama, dil açma, kalite onayı atlama, dış mesaj
gönderme, backlink üretme/satın alma, ücretli API çağrısı. Otomasyon
sıralama garantisi değildir.

## 5. Oturum logu — 2026-09-16 (Claude Code lane)

Yapılanlar:
- `app/Http/Controllers/RecipeController.php`: `index()` artık
  `SeoSchemaService` alıyor; anasayfaya `homeSchema` (WebSite+SearchAction)
  ve son 3 yayında yazı (`homeBlogPosts`) geçiyor.
- `resources/views/recipes/index.blade.php`: layout çağrısına
  `:structured-data="$homeSchema ?? null"` eklendi (arşiv etkilenmez).
- `resources/views/home/index.blade.php`: blog-hub şeridi eklendi
  (`x-blog-card` × 3, yazı yoksa bölüm basılmaz). 7 dilde yeni anahtar
  gerekmedi (`nav.blog`, `home.nav_kitchen_guides`, `blog.show.read_more`).
- `app/Services/Seo/SeoSchemaService.php::webSite()`: SearchAction hedefi
  ölü `/arama` adresinden gerçek `recipes.index?q=` adresine düzeltildi.
- `docs/SEO_EXECUTION_MASTERPLAN.md`: `/okf/` satırı UYGULANMADI olarak
  işaretlendi; `seo:autonomous-cycle` → `seo:autonomous-growth` düzeltildi.
- Kilitlenen görsel işi (`...kolokas-S8VGF`) geri bırakıldı: bu oturumda
  görsel üretim aracı yok, havuz bloke edilmedi.

Doğrulama: `Seo|Schema` 85/85 geçti, `Home` 34/34 geçti.
`BlogIndexabilityTest::test_a_post_that_passed_its_gates_is_indexable`
temiz ağaçta da düşüyor (önceden var olan arıza, bu oturumdan değil).

Yapılmayanlar (bilinçli): sahte telefon/saat ile LocalBusiness zenginleştirme
(`config/brand.php` içinde gerçek NAP verisi yok; uydurma NAP zarar verir),
toplu içerik üretimi/yayını, otomatik outreach, dizin bombardımanı.

Açık konular: 59 tarifte onaylı görsel eksik (zengin sonuç engeli, görsel
lane), Search Console API bağlantısı yok (kör uçuş), `/okf/` kararı
(implemente et ya da masterplan'dan düşür), yukarıdaki test arızası.

## 6. Devam logu — 2026-09-16 öğleden sonra (Claude Code lane)

- `BlogIndexabilityTest` arızası çözüldü: sayfa doğru şekilde
  `index,follow, max-image-preview:large` basıyordu, test eski tam-eşleşme
  `content="index,follow"` arıyordu. Repo konvansiyonuna
  (`RecipeCollectionLandingPagesTest:25`) uygun önek eşleşmeye çevrildi.
  Test-dışı kod değişmedi. `BlogIndexability` 6/6 geçti.
- `/okf/` kararı: DÜŞÜRÜLDÜ. Doğrulanmış AI-arama sinyali yok;
  `/llms.txt` + `/llms-full.txt` canlı ve yeterli. Masterplan güncellendi.
- Search Console durumu (salt-okunur, `seo:morning-brief --json`):
  enabled+configured ama `ready:false` — OAuth/hizmet hesabı içe aktarımı
  insan adımı bekliyor. IndexNow da `ready:false` (alan adı + anahtar dosya
  doğrulaması bekliyor). Bilinmeyen değer sıfır yazılmadı.
- Açık yerel sorun: 0 (`open_seo_issues:0`), içerik fırsatı: 298 (sinyal,
  otomatik yayın yok).

## 7. Kapanış turu — 2026-09-16 akşam (Claude Code lane)

- Lane kuralı eklendi: commit öncesi `git status`, yalnız kendi dosyalar
  stage'lenir (`git add -A` yasak).
- Disk kontrolü: `storage/app/private/backups` 204M (yerel test yedekleri),
  `fallbacks` 520 dosya/2M, `media-import/recipes` boş. Silinmedi: paralel
  ajanlar aktifken başkasının dosyasını silmek yasak bölge; temizlik
  komutları insanda (`rm -rf storage/app/private/backups/*` + `.gitignore`
  korunur, `rm -rf storage/app/public/fallbacks`).
- Görsel kuyruğu: 3/366 tamamlandı (%0,8), 0 kilitli, 363 boşta. Görsel
  üretimi görsel lane'de sürüyor; bu oturumda üretim aracı yok.
- İnsan blokları (kodla bitmez): GSC OAuth içe aktarımı, IndexNow alan
  adı+anahtar doğrulaması, backlink temasları, yayın onayları.
