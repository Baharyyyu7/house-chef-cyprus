# Kıbrıs arama niyeti, içerik ve editoryal ilişki fırsatları

Tarih: 16 Eylül 2026 · Sorumlu: AI-Worker-4 · Durum: salt okunur araştırma tamamlandı.

Bu iç denetim raporudur; blog teslimi, yayımlanmış içerik veya edinilmiş backlink değildir. Site, veritabanı, kuyruk ve sunucu değiştirilmedi; kimseye mesaj gönderilmedi. `seo-audit` ve `ai-seo` becerileri ile proje çalışma ve blog teslim kuralları okundu. Skill içindeki platform iddiaları gerektiğinde birincil kaynakla kontrol edildi.

## Kanıtın sınırı ve mevcut durum

- Web araması 16 Eylül'de yapıldı; bunlar kişiselleştirilmemiş bir araştırma aracının sonuçlarıdır, Türkiye/Kıbrıs konumlu Google ilk 10 sıralama ölçümü değildir. Sonuç sırası Google sırası olarak raporlanmadı. Arama hacmi, backlink sayısı ve domain otoritesi ölçülmedi.
- Ana ajan canlı GSC bağlantısını ve **8 tıklama, 247 gösterim, ortalama konum 15,5** bildirdi. Bu ajan GSC'yi doğrudan sorgulamadı; tarih aralığı, ülke ve sorgu kırılımı alınmadan bu toplamlar Kıbrıs kelimelerinin sıralaması sayılmaz. Eski belgelerdeki “GSC bağlı değil” bilgisi kullanılmamalı.
- [Ana sayfa](https://www.housechefcyprus.com/) araştırma aramasında bulundu: “Google'da hiç yokuz” kesin yargısı desteklenmiyor. Tüm hedef sorgularda görünürlük ise ayrıca ölçülmeli.
- [Kıbrıs hub](https://housechefcyprus.com/mutfaklar/kibris-mutfagi) ve [Hellim malzeme sayfası](https://housechefcyprus.com/malzemeler/hellim) doğrudan HTTP kontrolünde 200 döndü. Web okuyucunun bu iki URL için verdiği “Internal Error” sitenin 404 olduğu anlamına gelmiyor.
- Üç mevcut blog canlı açıldı: [başlangıç rehberi](https://housechefcyprus.com/blog/kibris-mutfagina-baslangic-rehberi), [meze sofrası](https://housechefcyprus.com/blog/kibris-mezeleriyle-pratik-sofra), [temel malzemeler](https://housechefcyprus.com/blog/kibris-sofralarinda-temel-malzemeler). Her biri bir dakikalık, birkaç kısa paragraftan oluşan içerik sunuyor. Sorun yalnız uzunluk değil: okuyucunun uygulayacağı ölçülü menü, karşılaştırma, deneme kaydı ve kaynaklı yerel ayrıntı az.

## Arama niyeti eşlemesi

| Sorgu kümesi | Gözlenen/çıkarılan niyet | Sahip olacak mevcut URL | Karar |
|---|---|---|---|
| Kıbrıs yemekleri; Kıbrıs yemek tarifleri; Kıbrıs yemek tarif | Yemek isimlerini keşfetme ve evde pişirme | `/mutfaklar/kibris-mutfagi` | Tek güçlü koleksiyon; yazım varyantı başına sayfa açma |
| Kıbrıs yemek; yemek Kıbrıs | Karışık: yöresel yemek, restoran ve sipariş | Aynı hub; evde tarif niyeti açık anlatılmalı | Restoran/sipariş sunulmuyorsa bu hizmetler varmış gibi davranma |
| Kıbrıs'ta ne yenir | Seyahat sırasında tat seçimi ve kültürel bilgi | `/blog/kibris-mutfagina-baslangic-rehberi` | Tarif hub'ını kopyalamadan yemek tanıma ve malzeme rehberi |
| Kıbrıs mezeleri | Sofra kurma, çeşit seçme, hazırlanma sırası | `/blog/kibris-mezeleriyle-pratik-sofra` | Kişi sayısı, alışveriş miktarı ve zamanlama |
| Hellim nasıl pişirilir; Kıbrıs malzemeleri | Malzeme kullanımı ve teknik | `/malzemeler/hellim`; `/blog/kibris-sofralarinda-temel-malzemeler` | Tekniği malzeme sayfasına, genel alışverişi bloga ayır |
| Kıbrıs | Çok geniş coğrafya, haber ve seyahat niyeti (çıkarım) | Marka ana sayfası ve mutfak hub'ı yalnız uygun yemek alt niyetinde | Tarif sitesinin bu sözcükte sürekli birinci olacağı iddiası kurulamaz |

Somut arama rekabeti: [Yemek.com Kıbrıs tarif kategorisi](https://yemek.com/tarif/dunya-mutfaklari/kuzey-kibris-turk-cumhuriyeti/) ve [Lezzetler Kıbrıs koleksiyonu](https://yoresel.lezzetler.com/kibris-yemekleri-1) tarif niyetini; [Kipra ne yenir rehberi](https://www.kiprarent.com/rehber/kibrista-ne-yenir/) gezi niyetini; [Yemeksepeti KKTC](https://www.yemeksepeti.com/city/kktc?r=1) sipariş niyetini temsil ediyor. Bunlar bu araştırmada görülen rakip örneklerdir; ilk üç Google sonucu oldukları iddia edilmiyor.

## Öncelikli 12 fırsat

P0: güven/doğruluk ön koşulu. P1: ilk editoryal çalışma. P2: veri ve saha çalışması hazır olduğunda.

| No | Öncelik | Fırsat / doğrulanmış kaynak | Mevcut hedef URL | Özgün kullanıcı yararı ve kabul ölçütü |
|---|---|---|---|---|
| 1 | P0 | Hub'da kanıtsız doğrulama ve tescil iddialarını temizleme; [Google insan odaklı içerik ilkeleri](https://developers.google.com/search/docs/essentials) | `/mutfaklar/kibris-mutfagi` | Her tarifin gerçekten denenip denenmediği dürüst gösterilmeli. Tüm yemekleri “patentli/PDO” sayma. Okuyucuya SEO terimi yerine yemek seçme yardımı ver. Teknik ajana aktarıldı; bu rapor düzeltildiğini doğrulamaz. |
| 2 | P1 | Tarif koleksiyonunu seçim aracına dönüştürme; [Yemek.com kategorisi](https://yemek.com/tarif/dunya-mutfaklari/kuzey-kibris-turk-cumhuriyeti/) | `/mutfaklar/kibris-mutfagi` | Süre, zorluk, temel malzeme ve vejetaryen uygunluğuyla seçilebilen doğru etiketli tarifler; onaylı tarife özel görsel; isim ve malzeme eşleşmesi. Ayrı anahtar kelime sayfaları üretme. |
| 3 | P1 | Başlangıç rehberini kullanılabilir yerel sözlüğe dönüştürme; [mevcut rehber](https://housechefcyprus.com/blog/kibris-mutfagina-baslangic-rehberi) | Aynı blog URL'si | Molohiya/molehiya, pilavuna/flaouna gibi ad varyantları yerel uzmanla teyit edilir; her yemek için “nedir, nasıl servis edilir, hangi mevcut tarife gider” açıklanır. Kuzey Kıbrıs yorumu ve ada genelindeki varyantlar birbirine karıştırılmaz. |
| 4 | P1 | Meze sofrasında gerçek pişirme planı; [mevcut yazı](https://housechefcyprus.com/blog/kibris-mezeleriyle-pratik-sofra) | Aynı blog URL'si | Denenmiş dört kişilik alışveriş miktarı, önceki gün/servisten önce hazırlık sırası ve sıcak-soğuk tabak planı. Deney yapılmadan süre veya “test edildi” iddiası yazılmaz. |
| 5 | P1 | Hellim tekniğinde özgün deneme; [Hellim sayfası](https://housechefcyprus.com/malzemeler/hellim) | `/malzemeler/hellim` | Aynı hellimde farklı dilim kalınlığı/tava/süre denemelerinin tarihli kaydı, gerçek sonuç fotoğrafı ve sonuç tablosu. Yapay zekâ görseli gerçek deney fotoğrafı diye sunulmaz. |
| 6 | P2 | Malzeme rehberinde satın alma ve ikame karşılaştırması; [mevcut malzeme rehberi](https://housechefcyprus.com/blog/kibris-sofralarinda-temel-malzemeler) | Aynı blog URL'si | Hellim/anari, taze-kuru ot farkları ve denenmiş ikamelerin tat/doku etkisi. Tarihsiz fiyat tahmini yerine tarihli saha gözlemi gerekiyorsa ayrı ölçüm. |
| 7 | P1 | DAÜ Turizm Fakültesiyle gerçek eğitim/editoryal iş birliği; [resmî fakülte](https://tourism.emu.edu.tr/en), [resmî iletişim](https://tourism.emu.edu.tr/en/contact) | Başlangıç rehberi ve hub | Yerel yemek adları ve tarif varyantlarını gözden geçiren, katılımcısı ve yöntemi açıklanan küçük atölye. Üniversite uygun bulursa kendi etkinlik raporunda kaynak gösterebilir. Link talebi karşılığında ücret/karşılıklı link şartı yok; ilişki kurulmuş değildir. |
| 8 | P1 | Kuzey Kıbrıs Turkcell GNÇGurme editoryal katkısı; [mevcut yemek yazısı](https://www.kktcell.com/turkcell-blog/yemek/kibrista-mutlaka-tatman-gereken-5-efsane-lezzet) | Başlangıç rehberi ve uygun mevcut tarifler | Yeni gelen öğrenciler için ev mutfağında magarına bulli/hellim hazırlama eki; gerçekten denenmiş ölçülerle editöre yararlı kaynak. Yayıncının bağlantı politikası ve katkı kabulü teyit edilmedi; gönderim yapılmadı. |
| 9 | P1 | Kipra seyahat rehberine eve dönüşte pişirme kaynağı; [ne yenir yazısı](https://www.kiprarent.com/rehber/kibrista-ne-yenir/) | Başlangıç rehberi → doğrulanmış tarifler | Seyahatte tanınan yemeği evde yapma, alternatif yerel adlar ve bulunamayan malzeme için denenmiş seçenek. Hedef kişisel editoryal ilişki; kiralık backlink değildir. |
| 10 | P2 | Cyprus Tatil yemek rehberiyle tamamlayıcı kaynak; [mevcut rehber](https://www.cyprustatil.com/?id=13&s=icerik) | Hub ve meze sofrası | Tatil sonrası aynı sofrayı kurmaya yarayan uygulanabilir menü ve kaynaklı ad sözlüğü. Mevcut listeyi yeniden yazmak yerine eksik uygulama değerini üret. Link verileceği veya teklif kabul edileceği bilinmiyor. |
| 11 | P2 | Gezmek Lazım yeme içme rehberiyle özgün evde hazırlama katkısı; [mevcut yazı](https://gezmeklazim.com/kibris-yeme-icme-rehberi/) | Meze sofrası ve başlangıç rehberi | Gezginin tattığı tabakların ev uyarlaması; gerçek deneme, ölçü ve malzeme ikamesi. Üç seyahat yayıncısına aynı metni seri dağıtmak yok; her biri için farklı gerçek yarar gerekir. |
| 12 | P2 | Ada genelindeki mutfak anlatısını resmî gastronomi kaynağıyla çapraz kontrol; [Visit Cyprus gastronomi kitabı](https://www.visitcyprus.com/wp-content/uploads/2016/05/Food_from_the_heart_of_Cyprus_5_2013_EN.pdf) | Başlangıç rehberi ve temel malzemeler | Ortak yemek adları ve bölgesel yorumları kaynaklandırma. Bu eski yayın güncel gıda güvenliği veya tescil otoritesi değildir. Önce kaynak doğruluğu; turizm kurumuyla olası ilişki ancak özgün çalışma oluşursa. Backlink elde edildiği varsayılmaz. |

## GEO uygulama notları

1. Önce açık, doğru, insanın işine yarayan içerik ve erişilebilir kanonik URL. Google AI özellikleri için özel AI dosyası veya ayrı içerik zorunlu değil. Sırf `llms.txt` eklemek atıf veya sıralama garantisi oluşturmaz. [Google AI özellikleri](https://developers.google.com/search/docs/appearance/ai-features)
2. Başlıklar gerçek soruyu cevaplamalı; yemeğin kısa tanımı, malzeme karşılaştırması ve adımlar anlamlı bağlam içinde kalmalı. Yapay zekâ için parçalara ayrılmış ayrı sayfalar veya aynı makalenin onlarca varyantı yok.
3. Gerçek yazar/uzman, açık kaynak ve gerçek inceleme tarihi kullanılmalı. İncelenmeden tarih güncellemek ya da gerçekleşmemiş tadım/deney atfetmek yok.
4. Skill'deki bir hataya dikkat: **Google-Extended, Google Search'e katılımı veya Search sıralamasını etkilemez**. AI Overviews için Googlebot ile eğitim/grounding kontrolleri karıştırılmamalı. [Google resmî tarayıcı açıklaması](https://developers.google.com/crawling/docs/crawlers-fetchers/google-common-crawlers)
5. Şema eksikliği yalnız metin okuyucuyla tespit edilemez; tarayıcı veya Rich Results Test ile kontrol gerekir. Bu alt çalışma şema testini yapmadı. Tarife ait olmayan fotoğraf, gerçekleşmemiş değerlendirme veya uydurma puanla zengin sonuç hedeflenmez.
6. Organik editoryal bağlantılar; ücretli sıralama linkleri, link ağları, sahte profil/yorum ve otomatik toplu yayın yok. [Google spam politikaları](https://developers.google.com/search/docs/essentials/spam-policies)

## Ölçüm ve sürdürülebilir otomasyon şartnamesi

- Haftalık GSC sorgu/URL/ülke/cihaz kırılımı: Kıbrıs kümesi, marka ve marka dışı ayrı. Son 28 gün önceki 28 günle karşılaştırılır; son günlerin gecikmesi dikkate alınır. Düşük hacim yüzünden tek haftalık değişim başarı/başarısızlık hükmü sayılmaz.
- Hedef URL gösterim, tıklama, CTR, ortalama konum ve indekslenme durumu takip edilir. Link edinme başarısı bağlantı sayısı değil ilgili yayın, gerçek yönlendirme ziyareti ve kullanıcıya yarar üzerinden değerlendirilir.
- AI atıf örneklemi için sabit soru, tarih, platform ve ülke kaydı gerekir; atıf, marka anılması ve tavsiye ayrı kaydedilir. Bu çalışmada AI yanıt görünürlüğü ölçülmedi.
- GSC veya izinli veri kaynağı kullanılır; Google sonuçlarına izinsiz otomatik sıralama kazıması yapılmaz. Otomasyon değişmeyen durumda sessiz kalır; erişim/indeks/görsel kırılması veya anlamlı performans değişiminde uyarır.
- Tek URL–tek içerik sahibi kaydı; mevcut slug kilitlenmeden üretim başlamaz. Önce ölçüm → mevcut sayfa iyileştirme → insan editörün yayın kararı → yeniden ölçüm. İçeriğin “mitoz” şeklinde çoğalması hedef değil.
- Yeni veya güncellenmiş blog metni gerekirse sunucuda `shared/storage/app/content-import/blog/{slug}.json` konumuna yalnız JSON taslak olarak teslim edilir. Bu rapor blog taslağı üretmedi veya yayınlamadı.

## Açık teslim sınırı

Araştırma ve fırsat listesi tamamlandı. Yazıların genişletilmesi, gerçek mutfak deneyleri, uzman görüşmeleri, editoryal iletişim ve link kazanımı henüz yapılmadı. Sürekli birincilik veya rakiplerin geçemeyeceği sonuç taahhüt edilemez. Google, temel şartlar sağlansa dahi tarama/indeks/gösterimin garanti olmadığını açıklıyor. [Search Essentials](https://developers.google.com/search/docs/essentials)
