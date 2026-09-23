# Blog SEO ve GEO Kalite Sistemi Tasarımı

Tarih: 23 Eylül 2026

## Amaç

House Chef Cyprus'taki 1.247 yayınlanmamış blog taslağını ölçülebilir SEO,
GEO ve içerik kalite kurallarına göre değerlendirmek, uygun olanları
iyileştirmek ve değişiklikleri yayın durumunu değiştirmeden production
ortamına aktarmak.

Bu çalışma sıralama, trafik veya backlink garantisi vermez. Amaç; arama
motorlarının tarayabildiği, kullanıcı niyetini karşılayan, kaynaklandırılmış,
özgün ve yapısal olarak alıntılanabilir içerik üretmektir.

## Değişmez sınırlar

- Hiçbir ajan blog yazısını yayınlamaz.
- `status` ve `published_at` alanları değiştirilmez.
- Yeni veya güncellenen içerik production ortamında taslak kalır.
- Dil etkinleştirilmez ve `config/locales.php` içindeki `enabled` alanına
  dokunulmaz.
- Yerel diskte blog teslim dosyası tutulmaz. İçerik teslimi yalnız sunucudaki
  `shared/storage/app/content-import/blog/` dizinine JSON olarak yapılır.
- Toplu içerik, gerçek arama talebi ve kalite kanıtı olmadan indekslenebilir
  hale getirilmez.
- Silme veya birleştirme gerekiyorsa önce yedekleme, doğrulama ve yönlendirme
  hazırlanır; ardından değişiklik uygulanır.

## Başarı ölçütleri

Bir taslak ancak aşağıdaki koşulların tümünü sağladığında "yayına hazır"
olarak işaretlenebilir:

1. En az 900 anlamlı kelime ve en az iki `##` alt başlık.
2. Tek, ölçülmüş ve içerikle uyumlu birincil anahtar kelime.
3. Üç ila altı ikincil anahtar kelime.
4. Arama niyetini doğrudan karşılayan özgün içerik amacı.
5. En az bir doğrulanmış tarif ilişkisi ve bağlama uygun iç bağlantı.
6. Üç ila altı gerçek kullanıcı sorusundan oluşan SSS.
7. En az bir güvenilir birincil veya otoriter kaynak ve fact-check notu.
8. Benzersiz meta başlık ve 140-158 karakterlik meta açıklama.
9. Başlık, özet ve gövde benzerliği kontrollerinden geçme.
10. Aynı arama niyetini hedefleyen başka sayfayla kanibalizasyon yaratmama.
11. Sağlık, beslenme, alerji ve gıda güvenliği iddialarında güvenilir kaynak
    ve uygun uzman uyarısı.
12. Editoryal, fact-check ve özgünlük durumlarının insan incelemesine uygun
    biçimde tamamlanması.

GEO için ayrıca her önemli bölüm doğrudan cevapla başlamalı, önemli iddialar
kaynaklandırılmalı ve içerik bağlamdan koparıldığında da anlaşılabilen kısa
cevap blokları içermelidir. Anahtar kelime doldurma yapılmaz.

## Mimari yaklaşım

### 1. Salt okunur envanter

Production veritabanındaki tüm taslaklar tek bir değerlendirme raporunda
toplanır. Her kayıt için kelime sayısı, meta uzunlukları, anahtar kelimeler,
SSS, kaynaklar, tarif ilişkileri, iç bağlantılar, editoryal durumlar ve
benzerlik sinyalleri hesaplanır.

Rapor içerikleri değiştirmez. Her taslağı şu sınıflardan birine yerleştirir:

- `ready`: bütün kalite eşiklerini karşılıyor.
- `upgrade`: gerçek arama talebi var ve eksikleri giderilebilir.
- `merge`: aynı niyeti hedefleyen daha güçlü bir sayfayla birleştirilmeli.
- `hold`: talep veya kaynak kanıtı yetersiz.
- `reject`: yanlış, spekülatif, alakasız ya da güvenli biçimde doğrulanamayan
  içerik.

### 2. Anahtar kelime ve kanibalizasyon haritası

Öncelik sırası şöyledir:

1. Search Console'da gösterim alan ve düşük tıklama oranına sahip sorgular.
2. House Chef Cyprus'un Kıbrıs mutfağı, hellim, yerel tarif ve mutfak tekniği
   uzmanlığıyla doğrudan ilgili sorgular.
3. Mevsimsel talebi doğrulanabilen içerikler.
4. Sitede güçlü bir tarif veya konu kümesine bağlanabilen içerikler.

Başlık benzerliği tek başına yeterli değildir. Birincil sorgu, arama niyeti,
mevcut sıralanan URL ve içerik kapsamı birlikte değerlendirilir. Aynı niyet
için tek kanonik hedef seçilir.

### 3. İçerik geliştirme hattı

`upgrade` sınıfındaki her yazı aşağıdaki sırayla işlenir:

1. Arama niyeti ve birincil sorgu doğrulanır.
2. Rakip sonuçların yalnız kapsam boşlukları çıkarılır; metin kopyalanmaz.
3. House Chef'e özgü değer belirlenir: tarif verisi, Kıbrıs bağlamı, ölçü,
   süre, sıcaklık, oran veya uygulama deneyimi.
4. Taslak baştan düzenlenir; gereksiz dolgu metni eklenmez.
5. Güvenilir kaynaklar ve fact-check notları kaydedilir.
6. İç bağlantılar gerçek tarif ilişkileri üzerinden kurulur.
7. SSS ve alıntılanabilir cevap blokları eklenir.
8. Meta verileri, başlık hiyerarşisi ve slug kontrol edilir.
9. Kalite ve benzerlik kapıları tekrar çalıştırılır.
10. Sonuç JSON teslim kutusuna yazılır ve taslak olarak içe alınır.

Bu hat, sırf 900 kelimeye ulaşmak için içerik uzatmaz. Konu 900 kelimelik
özgün değer taşımıyorsa `merge` veya `hold` sınıfına alınır.

### 4. URL ve yönlendirme güvenliği

Rastgele beş karakterli ek taşıyan slug'lar yalnız şu koşullarda temizlenir:

- Hedef slug benzersizdir.
- Eski URL'den yeni URL'ye tek adımlı kalıcı yönlendirme oluşturulur.
- Search Console gösterimi alan URL kaybedilmez.
- Yönlendirme zinciri veya döngüsü oluşmaz.

Taslak URL'ler public olmasa bile geçmişte indekslenmiş olma ihtimali ranked
URL raporuyla kontrol edilir.

### 5. Yapısal veri ve sayfa sunumu

Blog sayfası aşağıdaki verileri mevcut model alanlarından üretir:

- `Article` veya uygun alt türü.
- Görünür SSS ile birebir eşleşen `FAQPage` verisi.
- `BreadcrumbList`.
- Yazar, yayın/güncelleme tarihi ve kanonik URL.

Yapısal veri yalnız sayfada görünen ve doğrulanmış içerikten üretilir. GEO
için özel, Google tarafından istenmeyen bir işaretleme icat edilmez.

### 6. Backlink yaklaşımı

Backlink, yazı gövdesine eklenen dış bağlantı değildir. Üçüncü taraf bir
sitenin House Chef'e verdiği doğrulanmış bağlantıdır. Sistem:

- Yayıncı adaylarını konu ve coğrafi uygunluğa göre puanlar.
- Yalnız iletişim taslağı oluşturur; otomatik e-posta göndermez.
- Var olduğu iddia edilen bağlantıyı doğrulamadan kazanılmış saymaz.
- Ücretli, spam veya alakasız bağlantıları kalite sinyali kabul etmez.

Taslaklar yayınlanmadan backlink kazanımı beklenmez. Bu nedenle backlink
çalışması, insan tarafından yayınlanan güçlü içeriklerden sonra yürütülür.

## Kalite kapısı değişiklikleri

Mevcut otomatik kapı yalnız durum alanları ve karakter sayısına dayanıyor.
Yeni kapı aşağıdakileri zorunlu kılar:

- En az 900 kelime.
- Geçerli meta uzunlukları.
- Birincil ve ikincil anahtar kelimeler.
- En az üç SSS.
- Kaynak notu ve doğrulanmış kaynak bağlantısı.
- En az bir doğrulanmış tarif ilişkisi.
- Başlık/gövde yapısı.
- Kanibalizasyon ve benzerlik kontrolü.
- Sağlık ve güvenlik konularında ek inceleme.
- İnsan onayı kaydı.

Kapı başarısız olduğunda yazı yayınlanmaz ve her eksik açıklanabilir bir hata
olarak raporlanır. Toplu bypass seçeneği eklenmez.

## Dağıtım akışı

1. Sunucuda gerçek veritabanı yedeği alınır ve geri yüklenebilirliği kontrol
   edilir.
2. Kod değişiklikleri test ortamında çalıştırılır.
3. İçerik JSON dosyaları sunucudaki teslim dizinine yüklenir.
4. `blog:apply-editorial-content` önce dry-run olarak çalıştırılır.
5. Beklenen kayıtlar doğrulandıktan sonra taslak güncellemeleri uygulanır.
6. Kod mevcut release mekanizmasıyla deploy edilir.
7. SEO, sitemap, yönlendirme, schema ve public görünürlük testleri çalıştırılır.
8. Taslakların `status` ve `published_at` alanlarının değişmediği ayrıca
   doğrulanır.
9. Deploy başarısızsa önceki release'e dönülür; içerik verisi yedekten geri
   alınır.

## Test stratejisi

- Kalite servisi için her kuralın geçiş ve başarısızlık testi.
- 899 ve 900 kelime sınır testleri.
- Eksik kaynak, SSS, tarif ve insan onayı testleri.
- Kanibalizasyon ve benzerlik regresyon testleri.
- Slug değişiminde tek adımlı yönlendirme testi.
- Schema verisinin görünür içerikle eşleşme testi.
- Sitemap'in taslakları dışarıda tutma testi.
- İçerik içe aktarımının durumu değiştirmeme testi.
- Production dry-run çıktısının beklenen kayıt sayısıyla eşleşmesi.

## Uygulama sırası

Çalışma tek seferde 1.247 metni otomatik genişletmez. Önce kalite sistemi ve
raporlama düzeltilir, ardından Search Console fırsatına göre küçük, doğrulanmış
partiler işlenir. Her parti yedekleme, dry-run, içe aktarma ve doğrulama
adımlarından geçer. Böylece 16 Ağustos 2026'daki ölçekli düşük kaliteli içerik
olayı tekrarlanmaz.

## Teslim çıktıları

- Açıklanabilir taslak kalite raporu.
- Güçlendirme, birleştirme, bekletme ve ret listeleri.
- Güncellenmiş kalite kapısı ve testleri.
- Kanibalizasyon ve iç bağlantı haritası.
- Doğrulanmış içerik JSON partileri.
- Deploy ve production doğrulama raporu.
- İnsan yayın kararı için yayına hazır aday listesi.
