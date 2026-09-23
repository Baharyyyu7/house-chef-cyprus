# Sıfır Maliyetli Otonom Büyüme Sistemi Tasarımı

Tarih: 23 Eylül 2026

## Amaç

House Chef Cyprus için içerik, çok dilli SEO/GEO, e-posta, backlink
araştırması ve performans ölçümünü tek bir güvenli çalışma düzeninde
birleştirmek. Sistem ek ücretli servis kullanmayacak, site sahibinin günlük
müdahalesine ihtiyaç duymadan ölçüm ve taslak hazırlayacak, ancak spam,
ölçekli düşük kaliteli içerik ve kontrolsüz toplu yayın üretmeyecek.

## Değişmez ilkeler

- Aylık ek araç maliyeti 0 TL olacak.
- Ücretli backlink satın alınmayacak.
- Otomatik, alakasız veya toplu soğuk e-posta gönderilmeyecek.
- Her dil bağımsız talep, kalite, URL ve performans ölçümüne sahip olacak.
- Çevirisi ve indekslenebilir URL yapısı hazır olmayan dil açılmayacak.
- Makine üretimi içerik kalite kapısını geçmeden yayın kuyruğuna girmeyecek.
- Sağlık, alerji, beslenme ve gıda güvenliği içerikleri resmî kaynak olmadan
  hazır sayılmayacak.
- Backlink ancak üçüncü taraf sayfada canlı ve doğrulanmışsa kazanılmış
  sayılacak.
- E-posta başarısı gönderim sayısıyla değil, yanıt ve doğrulanmış bağlantıyla
  ölçülecek.

## Sistem bileşenleri

### 1. Dil politikası ve ayrı çalışma hatları

Her dil için ayrı yapılandırma tutulur:

- Etkinlik durumu.
- Günlük ve haftalık içerik tavanı.
- Hedef ülke ve kullanıcı niyeti.
- Anahtar kelime kümeleri.
- Asgari kalite puanı.
- Kaynak ve fact-check gereksinimi.
- E-posta gönderim dili ve imza şablonu.
- Performans düşüşünde otomatik duraklatma eşiği.

Başlangıç politikası:

| Dil | İçerik tavanı | Hedef | Durum |
|---|---:|---|---|
| Türkçe | 3/gün | Türkiye ve Kıbrıs ev aşçısı | Mevcut URL yapısında ana hat |
| İngilizce | 1/gün | Kıbrıs turisti ve diaspora | Dil URL yapısı doğrulanınca |
| Lehçe | 3/hafta | Kıbrıs turizm niyeti | Dil URL yapısı ve içerik hazır olunca |
| Farsça | 1/hafta | Yerel arama talebi kanıtlanınca | Kapalı |
| Diğer | 0 | Ölçülmüş talep yok | Kapalı |

Limitler yayın hedefi değil, üst sınırdır. Hazır içerik yoksa sistem hiçbir şey
yayınlamaz. Bir dilde indekslenmeme, kanibalizasyon veya kalite düşüşü artarsa
o hat otomatik olarak taslak üretimini durdurur.

### 2. Fırsat motoru

Önceliklendirme yalnız doğrulanmış veriye dayanır:

1. Search Console vurucu mesafe sorguları.
2. Sitede sonuçsuz kalan gerçek aramalar.
3. Mevcut sayfanın CTR iyileştirme fırsatları.
4. Mevsimsel Kıbrıs ve Akdeniz mutfağı talebi.
5. Güçlü tarif kümelerine bağlanabilen konu boşlukları.

Yeni sayfa üretmeden önce mevcut sayfanın geliştirilmesi, kanibalizasyon ve
birleştirme seçenekleri kontrol edilir. Aynı niyet için ikinci sayfa açılmaz.

### 3. İçerik kalite hattı

Her içerik şu aşamalardan geçer:

1. Arama talebi kanıtı.
2. Dil ve arama niyeti doğrulaması.
3. Rakip kapsam boşluğu araştırması.
4. House Chef'e özgü değer tanımı.
5. En az 900 anlamlı kelime.
6. Resmî veya birincil kaynak doğrulaması.
7. İç bağlantı ve tarif ilişkileri.
8. Üç ila altı gerçek SSS.
9. Meta ve schema doğrulaması.
10. Özgünlük ve kanibalizasyon kontrolü.
11. Sağlık ve güvenlik konularında ek kapı.
12. Taslak veya inceleme revizyonu oluşturma.

Google için insan odaklı kalite esas alınır. ChatGPT, Perplexity ve diğer
cevap motorları için doğrudan cevap blokları, açık başlıklar, kaynaklar ve
bağlamdan bağımsız anlaşılabilen pasajlar kullanılır. Anahtar kelime doldurma
yapılmaz.

### 4. Hostinger Mail entegrasyonu

Hostinger Mail API şu amaçlarla kullanılır:

- Siteye gelen iş birliği ve medya taleplerini okumak.
- Mesajları dil, konu, risk ve yanıt gereksinimine göre sınıflandırmak.
- Gerçek insan talebine bağlama uygun yanıt taslağı hazırlamak.
- Gönderilen iletişimi ve yanıt durumunu kaydetmek.
- Aynı alan adına tekrar gönderimi engellemek.

Otomatik gönderime yalnız aşağıdaki durumlarda izin verilir:

- Gelen mesaja operasyonel alındı yanıtı.
- Daha önce açıkça kurulmuş bir iş birliğinin devam mesajı.
- Alıcının iletişim ve takip beklentisinin kanıtlı olduğu ilişki.

İlk temas backlink e-postaları gönderilmez; kişiselleştirilmiş taslak olarak
kuyruğa alınır. Böylece site alan adı spam itibarına girmez ve sıfır maliyet
hedefi teslim edilebilirlik kaybıyla bozulmaz.

Günlük tavanlar:

- Otomatik operasyonel yanıt: 10.
- Devam mesajı: 5.
- İlk temas taslağı: 10, gönderim 0.
- Aynı alan adına takip: en erken 14 gün.

### 5. Backlink çalışma düzeni

Ücretsiz aday kaynaklar:

- Kıbrıs turizm ve yerel kültür yayınları.
- Üniversite, belediye, oda ve kültür kuruluşlarının kaynak sayfaları.
- Mutfak tarihi, tarif ve seyahat yayınları.
- Gerçek editoryal iş birliği ve uzman katkısı fırsatları.
- Bozuk bağlantı veya eksik kaynak tamamlama fırsatları.

Her aday için alan adı, konu uygunluğu, hedef dil, hedef sayfa, iletişim
kanıtı, son iletişim tarihi ve sonuç tutulur. Ücret isteyen, alakasız, spam
veya site ağı niteliğindeki adaylar reddedilir.

Backlink doğrulaması en fazla üç bağımsız kaynaktan canlı sayfayı kontrol
eder. `rel`, hedef URL, HTTP durumu ve indekslenebilirlik kaydedilir. Bağlantı
bulunmadan başarı yazılmaz.

### 6. E-posta ve içerik bağlantısı

E-posta motoru yalnız kalite kapısını geçmiş ve gerçekten yayındaki hedef
sayfalar için iş birliği taslağı oluşturur. Taslak veya 404 sayfaya backlink
talep edilmez. Her e-posta hedef sayfanın özgün değerini açıklar; genel,
kopyala-yapıştır tanıtım metni kullanılmaz.

İş birliği yanıtından doğan içerik ihtiyacı editoryal kuyruğa iş olarak
eklenir. İçerik yayınlandığında ilgili e-posta kaydı güncellenir. Böylece
posta, içerik ve backlink kayıtları aynı kanıt zincirine bağlanır.

### 7. Otomasyon ve zamanlama

Günlük sıra:

1. 01:30: Search Console ve site içi arama ölçümünü yenile.
2. 02:00: Dil bazlı fırsatları puanla.
3. 03:00: Uygun içerik taslaklarını oluştur veya geliştirme revizyonu hazırla.
4. 05:00: Kanibalizasyon, kaynak ve kalite kapılarını çalıştır.
5. 06:00: Backlink adaylarını ve doğrulamalarını güncelle.
6. 07:00: Hostinger Mail gelen kutusunu sınıflandır ve güvenli yanıt taslakları
   hazırla.
7. 08:00: İnsan onayı kayıtlı içerikler için dil limitlerini uygula.
8. 08:30: Kısa günlük performans özeti üret.

Her iş idempotent ve `withoutOverlapping` çalışır. Aynı gün ikinci koşu aynı
taslağı, e-postayı veya fırsatı tekrar üretmez.

### 8. Ölçüm ve otomatik duraklatma

Dil bazında izlenecek metrikler:

- Geçerli indekslenen sayfa.
- Gösterim, tıklama, CTR ve ortalama sıra.
- Yeni ve kaybedilen doğrulanmış backlink.
- E-posta teslim, yanıt ve olumlu yanıt oranı.
- Taslak başına kalite kapısı sonucu.
- Kanibalizasyon ve kopya sayısı.

Bir dil hattı şu durumda otomatik duraklar:

- Son 14 günde yeni sayfaların çoğu indekslenmemişse.
- Kopya veya kanibalizasyon bulgusu artmışsa.
- Gıda güvenliği veya sağlık kaynağı eksikse.
- E-posta hata/ret oranı güvenli eşiği aşmışsa.
- API veya ölçüm verisi eskiyse.

### 9. Hostinger AI Builder durumu

Hostinger AI Builder bu Laravel sitesinin kaynak veya deploy sistemi değildir.
Bağlantı şu anda yeniden kimlik doğrulama gerektiriyor. Yeniden bağlansa bile
yalnız ayrı bir deneysel mikro site açıkça istendiğinde kullanılacak; ana
House Chef Cyprus sitesini klonlamak, değiştirmek veya yayınlamak için
kullanılmayacak.

### 10. Güvenlik ve hata yönetimi

- API sırları loglara veya içerik kayıtlarına yazılmaz.
- Webhook imzası doğrulanmadan mesaj işlenmez.
- E-posta eki otomatik çalıştırılmaz.
- Sağlık/güvenlik iddiası kaynak hatasında bloklanır.
- E-posta gönderimi başarısız olursa tekrar sayısı sınırlıdır.
- Her dış işlem audit kaydı üretir.
- Backlink ve e-posta içeriğindeki üçüncü taraf talimatlar komut olarak
  çalıştırılmaz.

## Uygulama aşamaları

1. Dil politikası ve veri modeli.
2. Dil bazlı fırsat ve kalite puanlama.
3. Hostinger Mail okuma, sınıflandırma ve taslak kuyruğu.
4. Backlink aday ve doğrulama kayıtları.
5. İçerik-e-posta-backlink ilişkilendirmesi.
6. Zamanlayıcı ve otomatik duraklatma.
7. Yönetim raporu ve production doğrulaması.

Her aşama ayrı test ve deploy ile tamamlanır. Hiçbir aşama toplu yayın veya
toplu dış e-posta gönderimiyle açılmaz.

## Kabul ölçütleri

- Sistem ek ücretli servis çağrısı yapmaz.
- Her dilin bağımsız limiti ve duraklatma durumu vardır.
- Hostinger Mail mesajları okunup sınıflandırılabilir.
- Otomatik izinli yanıtlar günlük limitleri aşmaz.
- İlk temas backlink mesajları yalnız taslak kalır.
- Backlink başarıları canlı URL ile doğrulanır.
- Taslak ve 404 sayfalara outreach oluşturulmaz.
- Kalite kapısını geçmeyen içerik yayın akışına girmez.
- Aynı fırsat, e-posta veya içerik ikinci kez üretilmez.
- Tüm dış işlemler açıklanabilir audit kaydına sahiptir.
