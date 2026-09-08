# House Chef Cyprus: harcamasız organik büyüme

8 Eylül 2026. Google Ads çalışması durduruldu; bu planda reklam bütçesi, ücretli API, backlink satın alma ve dijital kit satışı yok.

## Başlangıç ölçümü

Search Console arayüzünden 8 Eylül'de okundu. Mülk: `https://housechefcyprus.com/`, arama türü Web, dönem 10 Ağustos - 6 Eylül 2026 (28 gün).

| Ölçüm | Değer | Kaynak/sınır |
| --- | --- | --- |
| Google arama tıklaması | 5 | Ziyaretçi sayısı değildir |
| Gösterim | 91 | Search Console |
| CTR | %5,5 | Küçük örneklem, deney sonucu değil |
| Ortalama konum | 24,2 | Tek bir kelimenin sırası değil |
| Görünen sorgu satırı | 19 | Tüm sorguları veya Semrush anahtar kelime kapsamını temsil etmez |
| Dizine alınan sayfa | 60 | Genel Bakış raporu, raporlama gecikmeli |
| Dizine alınmayan sayfa | 180 | Hepsi hata değildir; neden ve URL bazında ayrılmalı |
| Açık uygulama SEO sorunu | 1 | Üretim `seo:health --json`, 8 Eylül |
| Açık içerik fırsatı | 8 | Üretim `seo:morning-brief --json` |
| Otomatik GSC içe aktarımı | Hazır değil | Komut mevcut ama OAuth/servis hesabı bağlantısı yok |
| GSC dış bağlantılar | Raporda 0 / veri yok | İnternette hiç bağlantı yok demek değildir |
| GSC iç bağlantılar | 353 | Dış backlink sayısıyla toplanmaz |

GSC 403 örnekleri `tarifler?cuisine=akdeniz-mutfagi` ve `tarifler?cuisine=balkan-mutfagi`: son taramalar 25-26 Ağustos, 8 Eylül standart HTTP kontrolünde ikisi de 200. Bu kontrol Googlebot erişiminin veya indekslemenin tamamlandığının kanıtı değildir. Filtre sayfalarının noindex politikası korunur.

GSC 404 örnekleri `/tarifler/firinda-baharatli-tavuk` ve `/tarifler/firinda-patatesli-kofte`: ikisi de bugün 404, üretimde bu sluglarda tarif kaydı yok. Aynı tarife ait gerçek karşılık belirlenmediği için rastgele yönlendirme eklenmedi. Kaynak seeder içinde ikinci slug için eski ilişki referansları var; güncel yayın bağlantılarında bulunup bulunmadığı ayrıca denetlenmeli.

Kullanıcının Semrush ekranı: sağlık %83, trafik tahmini 0, anahtar kelime 21, backlink 23. Bu sayılar farklı veri kaynakları ve dönemlere aittir. GSC tıklamalarıyla birebir karşılaştırılmaz. İç bağlantı denetimindeki `backlinks` alanı site içi bağlantıdır, dış referans alan adı değildir.

GSC sorgu tablosunda görülen ilk fırsatlar: peynirli kraker (5 gösterim), fırın dereceleri (4), nohutlu pilav (2), bakliyat nasıl saklanır (2), Kıbrıs usulü şeftali kebabı tarifi (1), kuru bakliyatlar buzdolabında saklanır mı (1). Bunlar mevcut sayfaları geliştirmek için erken sinyal; yeni seri sayfa üretmek veya arama hacmi iddia etmek için yeterli değil.

## İlk öncelikler

1. **Ölçüm döngüsü:** haftalık aynı 28 günlük dönem mantığıyla GSC tıklama/gösterim/sorgu/sayfa ölç. API erişimi kurulana kadar mevcut oturumdan salt okunur ölçüm; gizli tarayıcı tokenları alınmaz. Eksik veri sıfır yazılmaz. Otomatik import tamamlandı gibi gösterilmez.
2. **Kalite engeli:** `/blog/private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi-BH3DZ` için mevcut editoryal kontrol başarısız. İçerik, hizmet iddiaları, özgünlük ve kaynaklar incelenmeli. Kontrol damgası sırf SEO için geçirilmez; revizyon taslak olur.
3. **Tarama doğrulaması:** GSC'deki 404/403 örneklerini tek tek güncel HTTP yanıtıyla karşılaştır. Özel hesap alanlarının engeli korunur. Kaldırılan içerik için ilgili karşılık varsa 301; tüm 404'leri ana sayfaya yönlendirme yok. Sitemap yalnız mevcut merkezi indekslenebilirlik kapılarını kullanır.
4. **Mevcut kazanan adaylar:** yukarıdaki altı sorgunun mevcut URL eşlemesini çıkar. Aynı soruya ikinci kopya sayfa açmak yerine gerçek eksik cevabı tamamla. Başlık/açıklama değişimini tarihli kaydet; düşük örneklemden CTR zaferi ilan etme.
5. **Bağlantı kazanımı:** sayıyı değil ilgili, gerçek ve kalıcı kaynak alan adını artır. Önce link verilmeye değer kaynak, sonra ilgili muhatap.

## Ücretsiz bağlantı kazanma programı

| Kaynak adayı | Sunulacak gerçek değer | Başarı olayı |
| --- | --- | --- |
| Kıbrıs gastronomi/seyahat yayınları | Kaynaklı yerel yemek rehberi ve özgün tarif | Yayının kendi tercihiyle kaynak bağlantısı |
| Yerel üreticiler | Ürün kullanımıyla ilgili doğru tarif | Üretici sitesindeki ilgili kullanım rehberinden atıf |
| Restoran/şef/otel siteleri | Birlikte doğrulanmış yerel mutfak anlatımı | Gerçek ortak çalışma atfı |
| Yemek yazarları | Hatalı/eksik kaynak yerine daha yararlı rehber | İçerikle ilgili editoryal bağlantı |
| Mevcut kurumsal profiller | Tutarlı ad, doğru site adresi ve açıklama | Gerçek profil bağlantısı, sahte işletme oluşturma yok |

Her aday için kaynak URL, alaka gerekçesi, hedef içerik, iletişim durumu ve sonuç tutulur. Ücretli dizin, toplu yorum, sahte değerlendirme, link değişim ağı, binlerce otomatik e-posta yok. Sponsor bağlantısı editoryal backlink diye raporlanmaz; ticari ilişki açıklanır. Kimseye mesaj gönderildiği veya backlink alındığı henüz iddia edilmiyor.

Bağlantı isteği taslağı: “Merhaba, [belirli yazınız] içinde [konu] anlatılıyor. [URL] kaynağımızda [gerçek ek değer] bulunuyor. Okuyucunuz için yararlıysa kaynak olarak değerlendirebilirsiniz.” Her mesaj gerçek sayfaya göre tek tek hazırlanır; otomatik gönderim yok.

### İlk doğrulanmış araştırma adayları

- [Visit North Cyprus gastronomi](https://www.visitncy.com/discover-category/gastronomy/): hellim, pilavuna ve şeftali kebabı gibi içeriklerle doğrudan konu yakınlığı. Önce kaynaklı ve özgün bir tarif katkısı hazırlanmalı; bağlantı kabulü bilinmiyor.
- [Visit Cyprus hellim sayfası](https://www.visitcyprus.com/discover-cyprus/food-drink/local-produce/halloumi-cheese/): yerel ürün anlatımı için birincil kaynak ve konu karşılaştırması. Bölgesel hizmet/kurum farklılıkları korunmalı; otomatik ortaklık veya backlink varsayılmaz.
- [Kıbrıs Türk Ticaret Odası](https://www.ktto.net/iletisim/): gerçek yerel üretici ve işletme ağını araştırmak için kurumsal temas noktası. Ücretsiz dizin/üyelik veya bağlantı sağladığı doğrulanmadı; ücretli üyelik başlatılmaz.

Bu üç aday 8 Eylül'de web kaynaklarından bulundu; mesaj gönderilmedi. Bunlardan link almak garanti değildir.

## Otonom olan ve olmayan

Mevcut sunucu zamanlayıcısı günlük SEO/sitemap/iç bağlantı denetimi, anonim sıfır sonuç taleplerinden fırsat çıkarma ve sosyal taslak hazırlama yapıyor. Yeni sistem bunları yeniden kurup çoğaltmaz.

Bu turdaki kod: günlük sabah özeti koşu geçmişine yazılır; en yüksek öncelikli beş sorunu ve ölçüm bağlantısındaki boşluğu gösterir. Bilinmeyen trafik/sorgu/dış bağlantı sayısı `null` kalır. İnsan tarafından değerlendirilmiş fırsatlar günlük taramayla yeniden açılmaz. Geçmiş mevcut 90 günlük saklama mekanizmasına tabidir.

Otomatik yapılmaz: içerik yayınlama, dil açma, kalite onayını atlama, dış mesaj gönderme, backlink üretme/satın alma, ücretli API çağrısı, reklam bütçesi. Otomasyon sıralama garantisi değildir; teşhis ve editoryal iş sırasını sürekli besler.

## 30/60/90 gün

- **İlk 7 gün:** güncel GSC URL örnekleriyle tarama engellerini ayır; yukarıdaki içerik kalite hatasının revizyonunu hazırla; ilk altı sorguyu mevcut sayfalara eşle; gerçek backlink kaynaklarını envanterle.
- **Gün 8-30:** ilk üç içerikte özgün katkı ve yararlı iç bağlantılar; iki link verilebilir kaynak; beş gerçek ve ilgili iş birliği adayı. İçerik revizyonları taslak/onay sürecinde kalır. Bu adetler çalışma kapsamıdır, sıralama vaadi değil.
- **Gün 31-60:** aynı dönemleri karşılaştır; gösterim alan ama tıklanmayan sayfaları incele; gerçek editoryal bağlantı fırsatlarını ilerlet. Sonuç vermeyen işler listeden çıkarılır.
- **Gün 61-90:** kanıtlanan konu kümelerini derinleştir. İlgili sponsor/yönlendirme ortağı varsa önceki harcamasız gelir planı kullanılır. Ziyaretçi sayısı veya başvuru gelir yazılmaz.

## Başarı ölçüsü

Tek bir “100/100” hedefi yerine: indekslenebilir URL'lerde yanlış engel/kırık bağlantı sıfır; tüm iddialarda gerçek kanıt; GSC 28 günlük tıklama ve gösterim trendi; alakalı sorguların görünürlüğü; gerçek dış referans alan adları; tarif kullanımına geçen ziyaretler. Düşük hacimde haftalık yüzde değişim yerine ham sayılar gösterilir. Organik büyüme haftalar/aylar alabilir; hedeflenen sıraya ulaşma garantisi yoktur.

## Kaynaklar

GSC metrikleri ve farklı toplama yöntemleri: [Google performans raporu](https://support.google.com/webmasters/answer/7576553?hl=en). Bağlantı raporu bir dış kaynak envanteri başlangıcıdır: [Google Links report](https://support.google.com/webmasters/answer/9049606?hl=en). Link manipülasyonu ve kullanıcı yararı olmayan ölçekli içerik bu planın dışında: [Google spam politikaları](https://developers.google.com/search/docs/essentials/spam-policies).
