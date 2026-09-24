# Teknik SEO denetimi ve Kıbrıs hub düzeltmesi

Tarih: 2026-09-16. Ajan: AI-Worker-3. Canlı HTTP çıktıları ve yerel kaynak kod incelendi. Başlangıç salt okunur denetimdi; koordinatör daha sonra yalnız CuisineHubController, cuisines/show ve CuisineLandingPagesTest değişikliklerini görevlendirdi. Deploy, yayınlama, dil etkinleştirme ve medya onayı yapılmadı.

## Sonuç

Site tümüyle Google dışında değil. Koordinatörün aynı oturumda canlı `seo:morning-brief --json` ölçümünden aktardığı GSC verisi: 2026-08-16..2026-09-12 döneminde 8 tıklama, 247 gösterim, 15.502 ortalama konum, %3.2389 CTR, 28 sorguluk örnek; 2026-09-16 06:47 gözlemi, API bağlı. Eski bağlantısızlık raporları bu güncel verinin yerine kullanılmamalı. Bu toplamlar hedef sorgularda birinciliği veya ülke/cihaz bazlı konumu kanıtlamaz.

## Uygulanan dar düzeltme

- `app/Http/Controllers/CuisineHubController.php`: Kıbrıs özel başlık/açıklama Türkçe locale ile sınırlandı. FAQ tek veri kaynağına indirildi; görünür soru/yanıt ile JSON-LD aynı metni kullanıyor. Sabit 65 sayısı yerine gerçek koleksiyon sayısı kullanılıyor. Kanıtlanmamış üstünlük, denenmişlik, coğrafi işaret/patent ve arama başarısı iddiaları kaldırıldı.
- `resources/views/cuisines/show.blade.php`: anahtar kelime sıralamaları, SEO jargonlu paragraflar, karışık dilli/bozuk metinler ve doğrulanmamış coğrafi iddialar kaldırıldı. İç linkler mevcut yayınlanmış koleksiyondan üretiliyor; sabit elle yazılmış tarif slugları yok. Normal tarif kartları ve koleksiyon gezinmesi korundu.
- `tests/Feature/CuisineLandingPagesTest.php`: görünür FAQ-schema eşitliği (scriptler çıkarılmış HTML üzerinden), yayınlanmamış tariflerin dışlanması, tarif bağlantılarının gerçek koleksiyon üyeliği ve İngilizce sayfada Türkçe özel içeriğin olmaması kontrol ediliyor.
- Doğrulama: `php artisan test --compact tests/Feature/CuisineLandingPagesTest.php`: **16 test, 72 assertion geçti**. Uygulama sonrası deploy yapılmadı; canlı doğrulama ayrı adım.

## Canlıda doğrulanan iyi durum

- `/` ve `/mutfaklar/kibris-mutfagi` HTTP 200, index/follow, self canonical. Hub başlığı Kıbrıs yemek tariflerini hedefliyor.
- `/kibris` ve `/kibris-yemekleri` canonical hub'a ulaşıyor; route tanımı 301.
- `/robots.txt` taramaya açık ve gerçek `/sitemap.xml` dizinini bildiriyor. Sitemap index dört alt haritaya bağlanıyor.
- `/sitemaps/recipes.xml` HTTP 200 ve gerçek tarif URL'leri içeriyor; çevirili örneklerde kendine işaret eden tr ve x-default ile dil alternatifleri mevcut.
- `/en/tarifler/katmer-WJXS9` canonical İngilizce URL'ye işaret ediyor; çapraz dile canonical sorunu bu örnekte yok.
- Recipe, BreadcrumbList ve CollectionPage JSON-LD sunucu HTML'inde gerçekten mevcut. Şema yokluğu iddia edilmedi; Google Rich Results Test veya tam site taraması yapılmadı.

## Öncelikli açık bulgular

### P1: Kurum kimliği ile konu kimliği karıştırılıyor

Canlı Recipe/hub sayfalarındaki LocalBusiness JSON-LD `sameAs` listesi kurumun yanında Cyprus, Cypriot cuisine, Halloumi Wikipedia/Wikidata kimliklerini içeriyor. HouseChefCyprus bu yer veya yemek değildir. Kaynak `app/Services/Seo/GeoSeoService.php`. `sameAs` yalnız doğrulanmış kurum profilleri olmalı; konu ilgisi gerekiyorsa `about`/`knowsAbout` ile tanımlanmalı. Kaynakta üstünlük iddiası da var: "primary authoritative source". Koordinatöre bildirildi; bu ajanın değişiklik alanı dışında.

### P1: Tarif görseli gerçeğe uygunluğu

`/tarifler/kibris-usulu-firin-YDQEZ` Recipe.image değeri `images.unsplash.com/photo-1555396273-367ea4eb4db5?...` olarak dönüyor. Tarif kimliğine uygun onaylı fotoğraf olduğu doğrulanamadı. Gerekli image alanını doldurmak için jenerik fotoğraf kullanımı temsil doğruluğunu sağlamaz. `SeoSchemaService` ve medya uygunluk kapısı incelenmeli; gerçek eşleşmiş görsel ile değiştirilene kadar jenerik görsel tarif fotoğrafı diye işaretlenmemeli. Görsel eksikliği sayfanın HTML indekslenmesini tamamen kapatmak zorunda değildir.

### P1: Çeviri kayıtlarının kalite kapısı yetersiz

Canlı `/en/tarifler/katmer-WJXS9` HTTP 200/indexable. Recipe talimatı İngilizce jenerik cümlelerin ortasında kesilmiş Türkçe içerik taşıyor: "Un, tuz ve şekerin yarısını karıştırın. Yumurta ve ılık su e...". Bu, alanın dolu olmasının tam ve kullanılabilir çeviri olduğunu göstermediğine somut örnek. Türkçe kaynak metni ve yayın durumunu koruyarak sorunlu çevirilerin envanteri, editör kontrolü ve gerçek çeviri teslimi gerekli. Dil enabled ayarına dokunulmadı. Tek örnek tüm çevirilerin aynı olduğunu kanıtlamaz.

### P2: IndexNow yanlış başarılı raporlayabiliyor

`IndexNowService::submit` HTTP durumunu okuyor ama 4xx/5xx yanıtında başarısız olmuyor; döngü sonunda sabit success true/status 200 dönüyor. Ayrıca bildirilen `/sitemap-recipes.xml` gibi yollar gerçek `/sitemaps/recipes.xml` düzeniyle ayrışıyor. Anahtar konumu robots'ta `/.well-known/key.txt`, gönderim kodunda `/{key}.txt`. Koordinatör canlı sağlık raporunda configured/enabled true fakat public key validation nedeniyle ready false gördü. Tek doğrulanmış anahtar konumu ve gerçek HTTP durum raporlaması gerekli. IndexNow, Google sıralama garantisi değildir.

### P2: Ölçümsüz performans iddiasından kaçınma

Hub HTTP yanıtı yaklaşık 343 bin karakter ve 91 öğe listeliyor; kart sayısı sayfa yükünü büyütebilir. Bu tek başına Core Web Vitals başarısızlığı kanıtı değildir. Mobil saha LCP/INP/CLS verileri GSC veya CrUX ile ölçülmeli. Bu oturumda saha verisi alınmadı; rastgele performans puanı üretilmedi.

## Güncel birincil kaynaklar

- [Google yapılandırılmış veri kuralları](https://developers.google.com/search/docs/appearance/structured-data/sd-policies): görünür içerik ile tutarlılık, doğru temsil ve ilgili görsel gerekliliği.
- [Google Recipe belgeleri](https://developers.google.com/search/docs/appearance/structured-data/recipe): tarif işaretlemesi ve görsel koşulları.
- [Google AI arama optimizasyonu](https://developers.google.com/search/docs/fundamentals/ai-optimization-guide): teknik SEO ve özgün yararlı içerik temeli; görünürlük garantisi yok.
- [Google spam politikaları](https://developers.google.com/search/docs/essentials/spam-policies): arama manipülasyonu amacıyla ölçeklenen içerik ve bağlantı düzenlerinden kaçınılmalı.
- FAQ belgesi URL'si bu oturumda [Google güncellemelerine](https://developers.google.com/search/updates) yönlendi. FAQ verisini eklemenin otomatik zengin sonuç veya birincilik sağlayacağı söylenmemeli.

## Bitirme sınırı

Hedef sorgular için ülke/cihaz bazlı GSC serisi, manuel işlem raporu, tam site taraması, gerçek backlink envanteri ve Core Web Vitals bu dar denetimin dışında kaldı. Her zaman birinci sıra veya rakiplerin geçememesi sağlanabilir bir teknik garanti değildir. Gösterim, nitelikli tıklama, indekslenmiş doğru sayfa oranı ve doğru fotoğraf kapsamı üzerinden ilerleme izlenmeli.
