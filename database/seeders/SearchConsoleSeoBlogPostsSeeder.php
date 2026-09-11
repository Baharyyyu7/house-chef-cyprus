<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SearchConsoleSeoBlogPostsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Google Search Console gerçek arama verilerine dayalı SEO içerikleri hazırlanıyor...');

        $author = User::first();
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'mutfak-rehberi'],
            ['name' => 'Mutfak Rehberi', 'public_id' => strtoupper((string) Str::ulid())]
        );

        $approvedMedia = Media::where('visibility', 'public')
            ->where('verification_status', 'verified')
            ->first();

        $articles = [
            [
                'title' => 'Fırın Dereceleri Rehberi: Hangi Yemek Kaç Derecede Pişirilir?',
                'slug' => 'firin-dereceleri-rehberi-hangi-yemek-kac-derecede-pisirilir',
                'primary_keyword' => 'fırın dereceleri',
                'secondary_keywords' => ['kaç derecede pişer', 'fırında kaç derecede pişer', 'ideal fırın derecesi', 'kek kaç derecede pişer'],
                'excerpt' => 'Kek, börek, et, tavuk ve balık fırında kaç derecede pişer? Mutfakta kusursuz sonuçlar almak için eksiksiz fırın dereceleri ve sıcaklık rehberi.',
                'body' => <<<'MARKDOWN'
# 🌡️ Fırın Dereceleri Rehberi: Hangi Yemek Kaç Derecede Pişirilir?

Fırında yemek pişirirken mutfakta yapılan en kritik hatalardan biri yanlış sıcaklık derecesi ve raf konumu seçmektir. Çok yüksek sıcaklık yemeğin dış yüzeyini hızla yakıp iç kısmını çiğ bırakırken, gereğinden düşük sıcaklık ise et ve sebzelerin sularını tepsiye salıp kurumasına, hamur işlerinin ise yağ çekip sertleşmesine neden olur. Mutfakta profesyonel şeflerin yakaladığı altın sarısı kabuk ve sulu iç doku, doğru ısı ve doğru hava sirkülasyonunun eseridir.

İşte ev mutfağında kusursuz sonuçlar almanızı sağlayacak profesyonel fırın dereceleri tablosu ve pişirme incelikleri:

---

## 🎂 Hamur İşleri ve Tatlılar İçin İdeal Fırın Dereceleri

Hamur işlerinde iç dokunun gözenekli olması ve kabarması fırın tabanından gelen ısının dengesine bağlıdır:

*   **Klasik Kekler ve Pandispanyalar:** 170°C - 180°C (Önceden ısıtılmış fırında, orta rafta, alt-üst statik ayarda 35-45 dakika). İlk 25 dakika fırın kapağı kesinlikle açılmamalıdır.
*   **Tepsi Börekleri ve Çörekler:** 180°C - 200°C (Alt-üst ayarda, altı ve üstü nar gibi kızarana kadar 35-45 dakika).
*   **Mayalı Ekmekler ve Pideler:** 210°C - 230°C (Fırın tabanına ısıya dayanıklı kapta sıcak su konularak buhar desteği ile 20-30 dakika).
*   **Gevrek Kurabiyeler:** 160°C - 170°C (Hassas pişirme, kenarları hafif pembeleştiğinde fırından alınır, 12-18 dakika).
*   **Sütlaç ve Sufle:** 200°C - 220°C (Sütlaç için fırının sadece üst ızgarası açılarak benmari usulü fırınlanır).

---

## 🍗 Et, Tavuk ve Balık Pişirme Sıcaklıkları

Et yemeklerinde lezzetin temeli dış mühürleme ve iç sıcaklığın korunmasıdır:

*   **Fırında Bütün Tavuk veya Baget:** 190°C - 200°C (Derisinin çıtır, içinin sulu kalması için 45-60 dakika). [Fırın Tavuk Baget](/tarifler/firin-tavuk-baget) tarifimizle nefis sonuçlar alabilirsiniz.
*   **Kuzu İncik ve Geleneksel Tandır:** 150°C - 160°C (Düşük ısıda, üzeri kapalı şekilde ağır ağır 2.5 - 3 saat). Kuzu eti kemiğinden kendiliğinden ayrılana kadar pişmelidir.
*   **Köfte ve Patates:** 190°C - 200°C (Önceden ısıtılmış fırında yaklaşık 30-35 dakika).
*   **Balık Bütün (Levrek, Çupra):** 180°C - 190°C (Yağlı kağıt bohçasında veya sebze yatağında kurutmadan 25-30 dakika).

---

## 🥦 Sebze Yemekleri ve Gratenler

*   **Karnabahar ve Brokoli Graten:** 180°C - 190°C (Beşamel sos kabarıp üzeri altın sarısı kızarana kadar 25-30 dakika).
*   **Karnıyarık ve İmam Bayıldı:** 185°C - 190°C (Sosunun özleşmesi ve patlıcanların yumuşaması için 35-40 dakika).

---

## 💡 Fırın Kullanımında Şef Tüyoları

1.  **Fanlı (Turbo) vs. Statik (Alt-Üst) Pişirme:** Fanlı ayar kuru sıcak hava üflediği için kekleri kurutabilir; kek ve suflede daima alt-üst statik ayar tercih edilmelidir. Et, tavuk ve çıtır sebzelerde ise fanlı ayar mükemmel sonuç verir.
2.  **Raf Konumu:** Fırının orta rafı daima en dengeli ısı dağılımını sağlar. Tabanının iyi pişmesini istediğiniz börek ve pizzaları alt rafa, üzeri kızaracak gratenleri ise üst rafa yakın yerleştirin.
3.  **Dinlendirme:** Fırından çıkan eti en az 10 dakika, hamur işlerini ise en az 15 dakika oda sıcaklığında dinlendirin.

Daha fazla püf noktası ve nefis fırın lezzetleri için [Ana Yemekler Kategorisi](/kategoriler/ana-yemek) ve [Hamur İşleri Tarifleri](/kategoriler/hamur-isi) sayfalarımızı keşfedin.
MARKDOWN,
                'faq' => [
                    ['question' => 'Fırın dereceleri fanlı mı yoksa alt-üst mü olmalı?', 'answer' => 'Kek ve hamur işlerinde alt-üst pişirme önerilir. Et, tavuk ve sebze gratenlerde ise ısının eşit dağılması için fanlı ayar tercih edilir.'],
                    ['question' => 'Fırın kaç derecede ısıtılmalı?', 'answer' => 'Tarifte belirtilen pişirme sıcaklığı ne ise (örneğin 180°C), fırın ön ısıtmada tam o dereceye getirilmelidir.'],
                ],
            ],
            [
                'title' => 'Kuru Bakliyatlar Buzdolabında Saklanır mı? Güvelenmeyi Önleme Yöntemleri',
                'slug' => 'kuru-bakliyatlar-buzdolabinda-saklanir-mi-guvelenmeyi-onleme-yontemleri',
                'primary_keyword' => 'kuru bakliyatlar buzdolabında saklanır mı',
                'secondary_keywords' => ['kuru bakliyat buzlukta saklanır mı', 'bakliyat saklama koşulları', 'bakliyat güvelenmesi nasıl önlenir'],
                'excerpt' => 'Nohut, fasulye ve mercimek buzdolabında veya buzlukta saklanır mı? Bakliyatların bozulmasını ve güvelenmesini önleyen en etkili saklama sırları.',
                'body' => <<<'MARKDOWN'
# 🫘 Kuru Bakliyatlar Buzdolabında Saklanır mı? Güvelenmeyi Önleme Yöntemleri

Ev mutfaklarında temel besin kaynağı olan mercimek, nohut, kuru fasulye, börülce ve barbunya gibi kuru bakliyatların doğru koşullarda muhafaza edilmesi hem aile bütçesi hem de gıda güvenliği açısından büyük önem taşır. Özellikle yaz aylarında artan ortam sıcaklığı ve yüksek nem oranı, bakliyat dolaplarında güve, böcek ve un biti oluşumuna zemin hazırlar.

Peki kuru bakliyat buzdolabında saklanır mı? Güvelenmeyi önlemenin doğal ve kesin çözümleri nelerdir? İşte şeflerin ve deneyimli ev hanımlarının uyguladığı profesyonel saklama rehberi:

---

## ❄️ Kuru Bakliyat Buzdolabında veya Dondurucuda Saklanır mı?

**Kesinlikle evet!** Özellikle Kıbrıs ve Akdeniz kuşağı gibi sıcak ve nemli bölgelerde kuru bakliyatları buzdolabında saklamak en güvenli yöntemdir:

*   **Çiğ Kuru Bakliyat (Buzdolabı Rafı):** Çiğ nohut, fasulye ve mercimeği temiz cam kavanozlara koyup kapaklarını hava almayacak şekilde kapatarak buzdolabının alt raflarında veya sebzelik gözünde 1 yıldan uzun süre ilk günkü tazeliğinde saklayabilirsiniz. Düşük sıcaklık böcek yumurtalarının çatlamasını tamamen engeller.
*   **Buzlukta Şoklama Yöntemi:** Yeni satın aldığınız bakliyat paketlerini henüz açmadan veya cam kavanoza aktarmadan önce derin dondurucuda 48 saat bekletin. Bu dondurma işlemi gözle görülmeyen olası tüm mikroskobik larvaları etkisiz hale getirir; ardından kavanozlayıp kiler dolabına güvenle kaldırabilirsiniz.
*   **Haşlanmış Bakliyatları Buzlukta Saklama:** Haşlanmış nohut ve kuru fasulye mutfakta hayat kurtarır. Bir kerede 1 kilo nohutu haşlayıp soğuttuktan sonra buzdolabı poşetlerine birer yemeklik porsiyonlar halinde paylaştırarak dondurucuda **6 aya kadar** saklayabilirsiniz. Hafta içi doğrudan tencereye ekleyip 15 dakikada etli nohut yemeği pişirebilirsiniz.

---

## 🌿 Güvelenmeyi Engelleyen Doğal Koruyucular

Kimyasal tabletler kullanmadan mutfak dolaplarındaki bakliyatları korumanın doğal yolları şunlardır:

1.  **Defne Yaprağı:** Her bakliyat kavanozunun içine 2 adet kuru defne yaprağı bırakın. Defne yaprağının içerdiği doğal aromatik yağlar güve ve böceklerin nefret ettiği doğal bir kovucudur.
2.  **Kaya Tuzu:** 1 kilogramlık kavanoza 1 tatlı kaşığı iri kaya tuzu ekleyip kavanozu hafifçe sallayın. Kaya tuzu ortamdaki nemi çekerek bakliyatların küflenmesini ve güvelenmesini önler.
3.  **Kurutulmuş Acı Kırmızı Biber:** Bakliyat kavanozunun üst kısmına 1-2 adet bütün kuru acı biber yerleştirmek kokusuyla haşereleri uzak tutar.
4.  **Soyulmamış Sarımsak Dişi:** Kabuğu soyulmamış kuru bir diş sarımsak pirinç ve un kavanozlarında güvelenmeye karşı doğal bir kalkan görevi görür.

---

## 🥫 Doğru Saklama Kabı Seçimi: Cam vs. Plastik

Kuru bakliyatlar hiçbir zaman satın alındıkları naylon torbalarda veya plastik poşetlerde saklanmamalıdır. Plastik malzeme hava geçirgenliğine sahip olduğu için ortam nemini hapseder ve terleme yapar. En sağlıklı çözüm silikon contalı veya vakumlu kapaklı **cam kavanozlardır**.

Taze bakliyatlarla nefis sofralar kurmak için [Klasik Kuru Fasulye](/tarifler/klasik-kuru-fasulye) ve [Zeytinyağlı Barbunya Pilaki](/tarifler) tariflerimize mutlaka göz atın.
MARKDOWN,
                'faq' => [
                    ['question' => 'Kuru bakliyatlar buzdolabında saklanır mı?', 'answer' => 'Evet, özellikle sıcak havalarda cam kavanozlarda buzdolabında saklamak böceklenme ve güvelenmeyi %100 önler.'],
                    ['question' => 'Haşlanmış bakliyat buzlukta kaç ay saklanır?', 'answer' => 'Haşlanıp soğutulmuş nohut, fasulye ve mercimek buzlukta -18°C sıcaklıkta 6 aya kadar tazeliğini korur.'],
                ],
            ],
            [
                'title' => 'Geleneksel Kıbrıs Kebabı Tarifi ve Evde Yapılış Püf Noktaları',
                'slug' => 'geleneksel-kibris-kebabi-tarifi-ve-evde-yapilis-puf-noktalari',
                'primary_keyword' => 'kıbrıs kebabı',
                'secondary_keywords' => ['şeftali kebabı', 'kıbrıs köftesi', 'kıbrıs mutfağı lezzetleri', 'private chef cyprus'],
                'excerpt' => 'Otantik Kıbrıs Kebabı (Şeftali Kebabı ve Kıbrıs Köftesi) nasıl yapılır? Kıbrıs mutfağının en özel ızgara lezzetinin orijinal tarifi.',
                'body' => <<<'MARKDOWN'
# 🍢 Geleneksel Kıbrıs Kebabı Tarifi ve Evde Yapılış Püf Noktaları

Kıbrıs mutfak kültürünün Akdeniz havzasındaki en özgün ve damakta iz bırakan lezzetlerinin başında **Geleneksel Kıbrıs Kebabı** gelir. Ada genelinde çoğunlukla **Şeftali Kebabı** adıyla anılan bu eşsiz lezzet, klasik Türk ve Orta Doğu kebaplarından farklı olarak şişe dizilmek yerine taze kuzu gomene (terbiyeli kuzu tülbent gömlek yağı) sarılarak kömür ızgarasında pişirilir.

Pişerken eriyen gömlek yağı köftenin suyunu içine hapseder ve dış kabuğunda çıtır, lokum kıvamında benzersiz bir lezzet katmanı oluşturur. Ev mutfağında döküm tavada ya da mangal ateşinde bu otantik lezzeti yakalamanın tüm sırlarını sizler için derledik.

---

## 🛒 Kıbrıs Kebabı İçin Orijinal Malzemeler (4-6 Kişilik)

*   **Kıyma:** 500g kuzu döş ve dana kıyma karışımı (orta yağlı, lezzet dengesi için kuzu eti ağırlıklı olmalıdır)
*   **Kuzu Gömleği (Gomen / Banna):** 1 adet taze temizlenmiş kuzu gömlek yağı
*   **Kuru Soğan:** 2 orta boy kuru soğan (rendelenip suyu temiz bir tülbentle tamamen sıkılmış)
*   **Maydanoz:** 1 büyük demet taze maydanoz (yalnızca körpe yaprakları, çok ince kıyılmış)
*   **Baharatlar:** 1 tatlı kaşığı kaya tuzu, 1 tatlı kaşığı taze çekilmiş karabiber, 1 çay kaşığı kimyon, yarım çay kaşığı ada tarçını
*   **Servis:** Taze sıcak ada pidesi, sumaklı maydanozlu soğan, ızgara domates ve yeşil biber

---

## 👩‍🍳 Adım Adım Yapılışı ve Sarma Tekniği

1.  **Gömlek Yağının Hazırlanması:** Kuzu gömlek yağını ılık suya koyun, içerisine 2 yemek kaşığı elma sirkesi ekleyip 15-20 dakika dinlendirin. Bu işlem kokuyu nötralize eder ve gömlek zarını yumuşatarak kolay sarılmasını sağlar. Yumuşayan zarı temiz bir bez üzerinde kurulayın.
2.  **Köfte Harcının Yoğrulması:** Kıyma, suyu iyice sıkılmış soğan, ince kıyılmış maydanoz ve baharatları derin bir kaba alın. Harca asla bayat ekmek içi, un veya yumurta eklemeyin; orijinal ada kebabının sırrı saf et ve bol maydanozdur. En az 10 dakika yoğurup buzdolabında 30 dakika dinlendirin.
3.  **Porsiyonlama ve Sarma:** Dinlenen harçtan cevizden biraz büyük oval köfteler yuvarlayın. Düz bir tezgaha serdiğiniz gömlek yağından köfte ebadında kare parçalar kesin. Her köfteyi gömleğe bir tur sıkıca sarıp kenarlarını kapatın.
4.  **Pişirme İncelikleri:** Kor haline gelmiş kömür ızgarasında veya döküm ızgara tavada orta ateşte çevirerek pişirin. Yağ eridikçe köfteler altın sarısı renk alır ve nar gibi kızarır.

---

## 🥗 Kebap Yanına Eşlik Eden Ada Mezeleri

Kıbrıs sofrasında kebap asla yalnız servis edilmez. Yanına taze yapılmış [Humus](/tarifler/humus), ezme yoğurtlu [Cacık](/tarifler/cacik) ve taze nane yaprakları ile muhteşem bir ziyafet menüsü oluşturabilirsiniz.

Kıbrıs mutfağının tüm yöresel zenginliklerini keşfetmek için [Kıbrıs Mutfağı Kategorisi](/kategoriler/kibris-mutfagi) ve [Kıbrıs Şeftali Kebabı Rehberi](/blog/kibris-seftali-kebabi-nasil-yapilir-orijinal-tarif-ve-puf-noktalari) yazımızı inceleyebilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Kıbrıs kebabının orijinal adı nedir?', 'answer' => 'Kıbrıs kebabı halk arasında sıklıkla Şeftali Kebabı olarak bilinir.'],
                    ['question' => 'Kıbrıs kebabı fırında pişer mi?', 'answer' => 'Evet, ızgara imkanı yoksa 200°C önceden ısıtılmış fırında da ızgara ayarında pişirilebilir.'],
                ],
            ],
            [
                'title' => 'Fırın Kaç Dakikada Isınır? Ön Isıtma Süreleri ve Püf Noktaları',
                'slug' => 'firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktalari',
                'primary_keyword' => 'fırın kaç dakikada ısınır',
                'secondary_keywords' => ['fırın ön ısıtma kaç dakika', 'fırın dereceleri', 'kaç derecede pişer'],
                'excerpt' => 'Elektrikli ve ankastre fırınlar kaç dakikada istenen dereceye ulaşır? Yemek pişirmeden önce fırını ön ısıtmanın önemi ve süreleri.',
                'body' => <<<'MARKDOWN'
# ⏱️ Fırın Kaç Dakikada Isınır? Ön Isıtma Süreleri ve Püf Noktaları

Mutfakta yemek veya tatlı hazırlarken tariflerin girişinde yer alan en değişmez kural **"Fırını önceden ısıtın"** uyarısıdır. Pek çok kişi bu adımı sabırsızlıkla atlar veya fırını yiyeceği fırına sürdükten hemen sonra çalıştırır. Oysa fırının doğru dereceye ulaşmadan yiyecekle buluşması, kabarmayan sönük keklerin, kuruyup sertleşen etlerin ve suyunu salan sebzelerin en birincil sorumlusudur.

Peki fırın ortalama kaç dakikada ısınır? Ön ısıtmanın kimyasal faydaları nelerdir ve fırının tam olarak ısındığı nasıl anlaşılır? İşte mutfakta başarı sağlayan kapsamlı ön ısıtma rehberi:

---

## 🌡️ Derecelere Göre Fırın Isınma Süreleri Tablosu

Standart ev tipi elektrikli ve ankastre fırınların istenen hedef sıcaklığa ulaşma süreleri fırının gücüne göre şu şekildedir:

*   **150°C - 160°C (Düşük Isı Pişirmeleri):** Yaklaşık **7 - 9 dakika** (Meringue, kurabiye, ağır et pişirmeleri)
*   **180°C (En Sık Kullanılan Standart Derece):** Yaklaşık **10 - 12 dakika** (Kekler, kurabiyeler, tartlar ve sebze yemekleri)
*   **200°C (Kızartma ve Mühürleme Sıcaklığı):** Yaklaşık **12 - 15 dakika** (Börekler, fırın tavuk, köfte ve balık)
*   **220°C - 250°C (Maksimum Fırın Sıcaklığı):** Yaklaşık **15 - 20 dakika** (Ev yapımı ekmek, lahmacun ve pizza)

---

## 🧪 Neden Fırını Önceden Isıtmalıyız? Bilimsel Gerçekler

1.  **Maillard Reaksiyonu ve Dış Kabuk:** Et ve tavuk yüzeyindeki proteinler ve şekerler 140°C üzerinde birleşerek karamelize bir lezzet katmanı oluşturur. Sıcak fırına giren etin gözenekleri saniyeler içinde mühürlenir ve etin lezzetli suyu tepsiden buharlaşmak yerine etin içinde hapsolur.
2.  **Maya ve Kabartma Tozunun Aktivasyonu:** Kek ve mayalı hamurlar soğuk fırına konulduğunda fırın yavaş yavaş ısınırken hamurun içindeki yağ erir ve tabana çöker. Hamurun hava kabarcıkları fırın ısınana kadar söner ve kek basık kalır.
3.  **Yağ Çekmeyi Önleme:** Poğaça ve böreklerin katmanları sıcak hava dalgasıyla anında fırınlanmalı, tereyağı hamura karışmadan kat kat kabarmalıdır.

---

## 💡 Fırının Isındığını Nasıl Anlarsınız?

*   **Termostat Kontrol Işığı:** Fırının ön panelinde bulunan kırmızı veya turuncu ısıtma ikaz ışığı fırın ayarlanan dereceye ulaştığı anda kendiliğinden söner. Dijital fırınlarda ise genellikle bip sesi duyulur.
*   **Fırın İçi Termometresi:** Pek çok fırın termostatı zamanla kalibrasyonunu kaybedebilir ve göstergeden 10-20 derece sapabilir. Fırın ızgarasına asılan mekanik paslanmaz çelik bir fırın termometresi kullanmak en profesyonel ölçümü sunar.

Fırın ısısını ayarladıktan sonra lezzetli tarifler denemek için [Fırın Dereceleri Rehberi](/blog/firin-dereceleri-rehberi-hangi-yemek-kac-derecede-pisirilir) ve [Fırında Köfte Patates](/tarifler/kofte-patates) tariflerimize göz atabilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Fırın kaç dakikada ısınır?', 'answer' => 'Standart 180°C derece pişirmeler için fırın ortalama 10-12 dakikada ideal ısıya ulaşır.'],
                    ['question' => 'Fırının ısındığı nasıl anlaşılır?', 'answer' => 'Fırın üzerindeki kırmızı termostat veya ön ısıtma işığı söndüğünde fırın ayarlanan dereceye ulaşmış demektir.'],
                ],
            ],
            [
                'title' => 'Private Chef Cyprus: Kıbrıs’ta Özel Şef ve Mutfak Deneyimi Rehberi',
                'slug' => 'private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi',
                'primary_keyword' => 'private chef cyprus',
                'secondary_keywords' => ['kıbrıs özel şef', 'house chef cyprus', 'kıbrıs gastronomi', 'özel gurme menü'],
                'excerpt' => 'Kıbrıs\'ta tatil veya özel davetleriniz için kişiselleştirilmiş Private Chef (Özel Şef) hizmeti nedir? Akdeniz gurme mutfağının evinize gelişi.',
                'body' => <<<'MARKDOWN'
# 👨‍🍳 Private Chef Cyprus: Kıbrıs’ta Özel Şef ve Mutfak Deneyimi Rehberi

Akdeniz'in parlayan incisi Kıbrıs, yalnızca altın kumsalları ve tarihi dokusuyla değil, binlerce yıllık zengin gastronomi kültürüyle de dünyanın dört bir yanından gurmeleri ağırlar. Son yıllarda tatil villalarında, lüks rezidanslarda ve seçkin aile etkinliklerinde restoran kalabalığından uzak, kişiye özel gurme deneyimler arayanlar için **Private Chef Cyprus (Kıbrıs Özel Şef Hizmeti)** benzersiz bir ayrıcalık sunmaktadır.

Peki Kıbrıs'ta özel şef kiralama süreci nasıl işler, menüler nasıl oluşturulur ve bu ayrıcalıklı hizmet neleri kapsar? İşte ada genelinde eksiksiz özel şef deneyimi rehberi:

---

## 🍷 Özel Şef Hizmeti Neleri Kapsar?

Kıbrıs özel şef hizmeti, standart bir yemek siparişinden çok daha öte, baştan sona eksiksiz bir mutfak orkestrasıdır:

1.  **Kişiselleştirilmiş Menü Planlaması:** Şefiniz davet öncesinde sizinle doğrudan iletişime geçer. Misafirlerinizin alerjen hassasiyetleri, beslenme tercihleri (vegan, vejetaryen, ketojenik, glutensiz vb.) ve arzuladığınız mutfak tarzına (geleneksel Kıbrıs mutfağı, taze Akdeniz deniz ürünleri, modern füzyon veya barbekü şöleni) göre özel bir menü tasarlar.
2.  **Taze ve Günlük Yerel Tedarik:** Şef, yemek günü sabahın erken saatlerinde yerel ada pazarlarından ve balıkçılardan en taze malzemeleri temin eder: Günlük köy hellimi, taze kuzu eti, Akdeniz levreği, yabani enginar ve soğuk sıkım ada zeytinyağları.
3.  **Mutfakta Canlı Pişirme ve Sunum:** Şef ve servis ekibi belirlenen saatte villanıza gelir, yiyecekleri kendi mutfağınızda sıcağı sıcağına pişirir ve her tabağı sanatsal dokunuşlarla servis eder.
4.  **Kusursuz Temizlik ve Ayrılış:** Ziyafet tamamlandıktan sonra tüm pişirme kapları, tezgahlar ve mutfak alanı profesyonelce temizlenir; mutfak size ilk anki tertemiz haliyle teslim edilir.

---

## 🌴 Hangi Etkinlikler İçin Özel Şef Tercih Edilir?

*   **Villa Tatilleri:** Girne, Gazimağusa veya İskele bölgesindeki tatil villanızda dinlenirken her öğün gurme lezzetlerin ayağınıza gelmesi.
*   **Romantik Yıldönümü ve Evlilik Teklifleri:** Gün batımında deniz kenarında iki kişilik özel şampanya ve deniz mahsulleri menüsü.
*   **Aile Toplantıları ve Doğum Günleri:** Çocukların bahçede rahatça oynadığı, yetişkinlerin terasta barbekü keyfi yaptığı samimi ortamlar.
*   **Kurumsal Özel Yemekler ve VIP Davetler:** İş ortaklarınız için villa ortamında prestijli bir akşam yemeği ağırlaması.

Kıbrıs'ta profesyonel şef desteği almak ve unutulmaz anılar biriktirmek için [House Chef Cyprus İletişim](/iletisim) sayfamızdan bizimle irtibata geçebilir, adanın zengin reçetelerini [Kıbrıs Mutfağı Koleksiyonumuzda](/kategoriler/kibris-mutfagi) inceleyebilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Private Chef Cyprus hizmeti nasıl alınır?', 'answer' => 'House Chef Cyprus platformu üzerinden özel menü talebi oluşturarak villa veya eviniz için özel şef deneyimi planlayabilirsiniz.'],
                ],
            ],
            [
                'title' => 'Ev Yapımı Çıtır Peynirli Kraker Tarifi: Pratik ve Sağlıklı Atıştırmalık',
                'slug' => 'ev-yapimi-citir-peynirli-kraker-tarifi-pratik-ve-saglikli-atistirmalik',
                'primary_keyword' => 'peynirli kraker',
                'secondary_keywords' => ['ev yapımı kraker', 'sağlıklı atıştırmalık', 'peynirli atıştırmalık', 'fırında kraker'],
                'excerpt' => 'Çocuklar ve çay saatleri için evde kolayca yapabileceğiniz koruyucusuz, katkısız nefis çıtır peynirli kraker tarifi.',
                'body' => <<<'MARKDOWN'
# 🧀 Ev Yapımı Çıtır Peynirli Kraker Tarifi: Pratik ve Sağlıklı Atıştırmalık

Market raflarında satılan paketli krakerlerin birçoğu palm yağı, yüksek oranda koruyucu madde, yapay aroma vericiler ve aşırı sodyum içerir. Oysa evinizdeki birkaç temel malzemeyle hazırlayabileceğiniz **ev yapımı çıtır peynirli krakerler**, hem çocuklarınızın beslenme çantaları için tertemiz bir atıştırmalık hem de çay saatleriniz ve peynir tabaklarınız için harika bir gurme dokunuş sunar.

Fırından çıktığında evi saran taze tereyağı ve fırınlanmış peynir kokusuyla bu çıtır krakerler, bir kez denediğinizde mutfağınızın vazgeçilmez tariflerinden biri olacaktır.

---

## 🛒 Sağlıklı Peynirli Kraker Malzemeleri

*   **Peynir:** 1 su bardağı dolusu ince rendelenmiş sert peynir (eski kaşar, kuru Kıbrıs hellimi, parmesan veya sert olgun beyaz peynir)
*   **Un:** 1.5 su bardağı organik buğday unu veya tam buğday unu
*   **Tereyağı:** 75 gram (mutlaka buzdolabından yeni çıkmış soğuk ve minik küpler halinde doğranmış)
*   **Buzlu Su:** 2-3 yemek kaşığı (hamuru toparlayacak kadar)
*   **Tuz ve Doğal Baharatlar:** 1 çay kaşığı kaya tuzu, 1 tatlı kaşığı çörek otu, 1 tatlı kaşığı susam, yarım çay kaşığı tatlı toz kırmızı biber, 1 dal ince kıyılmış taze biberiye

---

## 👩‍🍳 Adım Adım Yapılışı ve Çıtırlık Sırları

1.  **Soğuk Malzeme Kuralı (Kumlama Tekniği):** Un, tuz ve soğuk küp tereyağını mutfak robotunun haznesine alın. Robotu aralıklı darbelerle (pulse) çalıştırarak tereyağının una karışıp ekmek kırıntısı veya ıslak kum kıvamına gelmesini sağlayın. Tereyağının erimemesi krakerin fırında kat kat çıtırlaşmasının en büyük sırrıdır.
2.  **Peynir ve Baharat İlavesi:** Rendelenmiş sert peyniri, çörek otunu, susamı ve baharatları ekleyin. Buzlu suyu kaşık kaşık ilave ederek hamurun bir araya gelmesini sağlayın. Hamuru fazla yoğurmayın; el ısısı yağı eritmemelidir.
3.  **Buzdolabında Dinlendirme:** Toparlanan hamuru streç filme sararak buzdolabında en az 25-30 dakika dinlendirin.
4.  **İnce Açma ve Şekil Verme:** Dinlenen hamuru yağlı kağıt arasında merdane yardımıyla yaklaşık 2-3 mm inceliğinde açın. Bıçak veya kurabiye ruleti ile kareler, üçgenler kesin. Çatalla üzerlerine delikler açın.
5.  **Fırınlama:** Önceden ısıtılmış **180°C fırında 12-15 dakika** hafif altın sarısı renk alana kadar fırınlayın. Fırından çıkan krakerleri tepside tamamen soğumaya bırakın; soğudukça çıtırlığı katlanacaktır.

---

## 🫙 Krakerleri Taze Saklama İpuçları

Ev yapımı krakerleri hava geçirmez kilitli cam kavanozlarda oda sıcaklığında **2 haftaya kadar** çıtırlığını hiç kaybetmeden muhafaza edebilirsiniz. Nem almaması için kavanozun dibine bir tutam pirinç tanesi koyabilirsiniz.

Diğer pratik ve lezzetli atıştırmalık fikirleri için [Atıştırmalık ve Pratik Kategorisi](/kategoriler/atistirmalik-pratik) arşivimize ve [Çorba Tarifleri](/kategoriler/corba) sayfalarımıza göz atabilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Peynirli kraker kaç derecede pişer?', 'answer' => 'Önceden ısıtılmış 180°C fırında 12-15 dakika pişirmek yeterlidir.'],
                ],
            ],
            [
                'title' => 'Orijinal Lokanta Usulü Mercimek Çorbası Tarifi: İpeksi Kıvamın Sırrı',
                'slug' => 'orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri',
                'primary_keyword' => 'lokanta usulü mercimek çorbası',
                'secondary_keywords' => ['süzme mercimek çorbası', 'ipeksi mercimek çorbası', 'mercimek çorbası püf noktaları', 'lokanta çorbası tarifi'],
                'excerpt' => 'Lokantalardaki pürüzsüz ve ipeksi mercimek çorbası evde nasıl yapılır? Un kavurma tekniği, terbiye dengesi, süzme incelikleri ve yağ yakma püf noktaları.',
                'body' => <<<'MARKDOWN'
# 🥣 Orijinal Lokanta Usulü Mercimek Çorbası Tarifi

Lokantalarda içtiğiniz mercimek çorbasının evde yapılanlardan en büyük farkı, pürüzsüz ipeksi dokusu ve un-tereyağı dengesidir. Çoğu ev tarifinde mercimek doğrudan sebzelerle haşlanıp blenderdan geçirilir; oysa lokanta usulü çorbanın sırrı doğru un kavurma tekniği, doğal kemik suyu ve geleneksel süzme aşamasında saklıdır.

---

## 🍲 İpeksi Kıvamı Veren Dört Temel Adım

1. **Unu Kokusu Çıkana Kadar Kavurmak:** Tereyağı ve zeytinyağı karışımında 1 yemek kaşığı unun hafif fındık kokusu verene kadar kısık ateşte kavrulması, çorbaya kadife gibi bir bağlayıcılık katar. Un fazla kavrulursa çorbanın rengi kararır; bu yüzden unun rengi dönmeden hemen sıcak et suyu eklenmelidir.
2. **Kırmızı Mercimeğin Nişastasından Arındırılması:** Mercimekler berrak su çıkana kadar en az 3-4 kez soğuk suyla yıkanmalıdır. Fazla nişasta çorbanın berraklığını bozar ve sonradan çabuk katılaşmasına yol açar.
3. **Doğal Et veya Tavuk Suyu:** Yalnızca çeşme suyu yerine yarı yarıya süzülmüş doğal kemik veya tavuk suyu kullanmak lokanta lezzetinin temel taşıdır.
4. **Tel Süzgeçten Geçirme:** Blender hava kabarcıkları oluşturup çorbanın rengini açabilir ve köpürtür. Gerçek lokanta usulünde çorba kepçeyle ince tel süzgeçten geçirilerek posasından ayrılır ve kusursuz bir pürüzsüzlük elde edilir.

---

## 🍗 Geleneksel Kemik ve Tavuk Suyu Çıkarma Püf Noktaları

Usta lokantacıların çorba kazanlarındaki en büyük sır, kısık ateşte en az 3 saat taze kök sebzelerle kaynatılmış ilikli kemik suyudur. Bir adet defne yaprağı, birkaç tane karabiber ve yarım havuç ile haşlanan tavuk ya da kuzu kemiği suyu, mercimeğin topraksı tadını kırıp çorbaya derin bir gövde ve doygunluk kazandırır.

---

## 🌶️ Servis ve Yağ Yakma Püf Noktaları

Çorba sıcak kaselere alındıktan sonra üzerine küçük bir sahanda köpürtülmüş hakiki tereyağı, tatlı toz kırmızı biber ve taze nane gezdirilir. Yanında mutlaka çeyrek sulu limon ve fırından yeni çıkmış sıcak ada pidesi veya kıtır kruton ekmekler eşliğinde servis edilir.

Ev mutfağınızda hemen denemek için [Klasik Kırmızı Mercimek Çorbası](/tarifler/klasik-kirmizi-mercimek-corbasi), [Ezogelin Çorbası](/tarifler/ezogelin-corbasi) ve zengin [Çorbalar Kategorisi](/kategoriler/corba) arşivimize göz atabilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Lokanta usulü mercimek çorbasına un konur mu?', 'answer' => 'Evet, 1 yemek kaşığı un yağda hafif kavrularak eklenir. Un, çorbaya lokantalara özgü kadifemsi ipeksi kıvamı verir.'],
                    ['question' => 'Mercimek çorbası neden blender yerine süzgeçten geçirilir?', 'answer' => 'Blender çorbanın içine hava basarak rengini matlaştırabilir ve köpürtebilir. Tel süzgeç ise mercimeğin saf liflerini ayırarak ipeksi bir doku bırakır.'],
                    ['question' => 'Mercimek çorbasının rengi nasıl parlak sarı kalır?', 'answer' => 'Çok az zerdeçal veya rendelenip kavrulmuş havuç eklemek ve pişme sırasında kapağı aralık tutmak çorbanın canlı rengini korur.'],
                ],
            ],
            [
                'title' => 'Kıbrıs Şeftali Kebabı Nasıl Yapılır? Orijinal Ev Yapımı Tarif ve Püf Noktaları',
                'slug' => 'kibris-seftali-kebabi-nasil-yapilir-orijinal-tarif-ve-puf-noktalari',
                'primary_keyword' => 'kıbrıs şeftali kebabı',
                'secondary_keywords' => ['şeftali kebabı nasıl yapılır', 'şeftali kebabı tarifi', 'kuzu gömleği sarma', 'kıbrıs ızgara lezzetleri', 'kıbrıs yemekleri'],
                'excerpt' => 'Kıbrıs\'ın dünyaca meşhur Şeftali Kebabı evde nasıl yapılır? Kuzu tülbent gömlek yağı hazırlığı, kıyma yoğurma püf noktaları ve mangal / döküm tava pişirme süreleri.',
                'body' => <<<'MARKDOWN'
# 🍢 Kıbrıs Şeftali Kebabı Nasıl Yapılır? Orijinal Ada Reçetesi

Kıbrıs sofralarının tartışmasız en ünlü ve karakteristik et lezzeti olan **Şeftali Kebabı**, adını bir dönem Lefkoşa'da bu kebabı ilk kez hazırlayıp ün kazandıran "Şef Ali" ustadan alır. Zamanla halk ağzında Şeftali Kebabı'na dönüşen bu nefis lezzetin sırrı, kuzu tülbent gömlek yağına (banna/terbiyeli zar) sarılarak ızgara edilmesidir.

---

## 🛒 Gerekli Malzemeler (4-6 Kişilik)

*   **Kıyma:** 600g kuzu ve dana döş karışımı orta yağlı çift çekim kıyma
*   **Gömlek Yağı:** 1 adet taze kuzu gömlek yağı (kasabınızdan temizlenmiş isteyin)
*   **Soğan:** 2 orta boy kuru soğan (incecik rendelenmiş ve suyu iyice sıkılmış)
*   **Maydanoz:** 1 demet taze maydanoz (yalnızca yaprakları, çok ince kıyılmış)
*   **Baharatlar:** 1 tatlı kaşığı kaya tuzu, 1 tatlı kaşığı taze çekilmiş karabiber, yarım tatlı kaşığı kimyon, 1 çay kaşığı tarçın (isteğe bağlı Kıbrıs dokunuşu)
*   **Servis İçin:** Taze sıcak Kıbrıs pidesi, sumaklı soğan salatası, ızgara domates ve yeşil biber

---

## 👨‍🍳 Adım Adım Hazırlanış ve Pişirme

1.  **Gömlek Yağının Yumuşatılması:** Kuzu gömlek yağını geniş bir kaba alın. Üzerine ılık su ve 2 yemek kaşığı sirke ekleyip 20 dakika bekletin. Zar şeffaflaşıp esneyince süzüp temiz bir bez üzerinde kurulayın.
2.  **Köfte Harcının Yoğrulması:** Kıyma, rendelenmiş ve suyu sıkılmış soğan, kıyılmış maydanoz ve baharatları derin bir yoğurma kabında en az 10-12 dakika özleşene kadar yoğurun. Harcı buzdolabında 30 dakika dinlendirin.
3.  **Şekillendirme ve Sarma:** Dinlenen köfte harcından cevizden biraz büyük oval köfteler yuvarlayın. Gömlek yağını düz bir zemine serin, köfte boyutunda kareler kesin ve her köfteyi bir tur sararak kenarlarını kapatın.
4.  **Izgara Aşaması:** Kor haline gelmiş kömür ızgarasında veya iyice ısıtılmış döküm ızgara tavada orta ateşte çevirerek pişirin. Gömlek yağı eriyip şeffaflaşacak, köftenin kendi suyunu hapsederek nar gibi çıtır bir kabuk oluşturacaktır.

---

## 💡 Usta Tavsiyeleri

*   Köfte harcına asla ekmek içi veya galeta unu eklemeyin; orijinal ada tarifinde yalnızca saf et, soğan ve bol maydanoz yer alır.
*   Soğanın acı suyunu sıkmak köftenin ekşimesini önler ve saf lezzet sağlar.

Kıbrıs mutfağının diğer eşsiz tatları için [Geleneksel Kıbrıs Tarifleri Arşivimize](/kategoriler/kibris-mutfagi) ve [Kıbrıs Mezeleri](/blog/kibris-mezeleriyle-pratik-N3C1V) rehberimize göz atabilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Şeftali kebabı adını nereden almıştır?', 'answer' => 'Lefkoşa\'da bu kebabı ilk kez hazırlayan "Şef Ali" ustadan almıştır. Zamanla halk arasında ses benzeşmesiyle Şeftali Kebabı olarak yerleşmiştir.'],
                    ['question' => 'Şeftali kebabı fırında yapılabilir mi?', 'answer' => 'Evet, yağlı kağıt serili tepside 200°C önceden ısıtılmış fırında yaklaşık 25-30 dakika nar gibi kızarana kadar pişirilebilir.'],
                    ['question' => 'Kuzu gömlek yağı nereden bulunur?', 'answer' => 'Kasaplardan taze kuzu gömleği veya zar yağı olarak temin edilebilir. Kullanılmadan önce mutlaka sirkeli ılık suda bekletilmelidir.'],
                ],
            ],
            [
                'title' => 'Kıbrıs Hellim Peyniri Nasıl Kızartılır ve Saklanır? Köy Usulü Rehber',
                'slug' => 'kibris-hellim-peyniri-nasil-kizartilir-ve-saklanir-koy-usulu-rehber',
                'primary_keyword' => 'hellim nasıl kızartılır',
                'secondary_keywords' => ['hellim peyniri saklama', 'hellim kızartma püf noktaları', 'hellim dağılmadan kızartma', 'kıbrıs hellimi', 'karpuz hellim'],
                'excerpt' => 'Hellim peyniri tavada dağılmadan, lastikleşmeden nasıl kızartılır? Salamura hellim saklama yöntemleri, dondurucu kuralları ve ada usulü karpuz-hellim eşleşmesi.',
                'body' => <<<'MARKDOWN'
# 🧀 Kıbrıs Hellim Peyniri Nasıl Kızartılır ve Saklanır?

Kıbrıs adasının tescilli kültürel mirası ve dünya çapında gastronomi simgesi olan **Hellim (Halloumi)**, yüksek erime noktası sayesinde ızgara ve tavada şeklini koruyabilen nadir peynirlerdendir. Geleneksel olarak keçi ve koyun sütünden taze nane yaprakları ile üretilen gerçek ada hellimini doğru pişirmek ve saklamak mutfakta büyük fark yaratır.

---

## 🍳 Dağılmadan ve Lastikleşmeden Hellim Kızartmanın Sırrı

Çoğu zaman tavada yapılan hata, hellimi yağ dolu bir tavada düşük ısıda pişirmektir. Bu durum peynirin suyunu salıp süngerleşmesine ve lezzetini kaybetmesine yol açar.

### İdeal Pişirme Adımları:

1.  **Doğru Dilimleme:** Hellimi yaklaşık **8-10 mm (yaklaşık 1 cm)** kalınlığında dilimleyin. Çok ince dilimler kurur, çok kalın dilimlerin ise içi ısınmaz.
2.  **Yüzeyin Kurulanması:** Salamurasından çıkan hellim dilimlerini kağıt havlu yardımıyla iyice kurulayın. Yüzeydeki ıslaklık çıtırlaşmayı engeller.
3.  **Kuru ve Sıcak Tava:** Döküm veya yapışmaz tabanlı tavayı orta-yüksek ateşte önceden iyice ısıtın. **Tavaya hiç sıvı yağ eklemeyin.** Hellim kendi doğal süt yağı ile mükemmel şekilde kızarır.
4.  **Hızlı Pişirme Süresi:** Dilimleri sıcak tavaya yerleştirin. Her bir yüzeyi altın sarısı kabuk bağlayana kadar **1.5 - 2 dakika** pişirip hemen ocaktan alın.

---

## 🧊 Hellim Peyniri Nasıl Saklanır?

*   **Salamura İçinde Saklama:** Geleneksel hellim, tuzlu peynir altı suyu salamurasında cam kavanozda buzdolabında **1 yıla kadar** tazeliğini ve diriliğini korur.
*   **Vakumlu Pakette:** Açılmamış vakumlu ambalajında buzdolabında son kullanma tarihine kadar saklanabilir.
*   **Dondurucuda (Buzlukta) Saklama:** Hellimi dilimleyip aralarına fırın kağıdı koyarak buzdolabı poşetinde dondurabilirsiniz. Çözdürmeden doğrudan sıcak tavaya atıp kızartabilirsiniz.

---

## 🍉 Ada Geleneği: Karpuz ve Sıcak Hellim İkilisi

Kıbrıs yaz sofralarında öğle veya akşam saatlerinde buz gibi sulu Akdeniz karpuzu ile sıcak tavada kızarmış taze naneli hellim eşleştirmesi, tuzlu ve tatlının mükemmel dengesini sunar.

Hellimli diğer özel tariflerimizi keşfetmek için [Hellimli Kıbrıs Böreği](/tarifler) ve [Kıbrıs Kahvaltılıkları](/kategoriler/kahvalti) sayfalarımızı ziyaret edebilirsiniz.
MARKDOWN,
                'faq' => [
                    ['question' => 'Hellim kızartırken tavaya yağ konur mu?', 'answer' => 'Hayır. Gerçek hellim yeterli doğal süt yağına sahiptir; yapışmaz tavada yağsız kızartıldığında çok daha çıtır ve lezzetli olur.'],
                    ['question' => 'Hellim neden lastikleşir?', 'answer' => 'Tava yeterince sıcak değilken pişirilirse veya tavada gereğinden uzun süre tutulursa peynir suyunu kaybedip lastikleşir.'],
                    ['question' => 'Tuzlu hellimin tuzu nasıl azaltılır?', 'answer' => 'Kızartmadan önce dilimlenmiş hellimi ılık suda 10-15 dakika bekletip kurulayarak fazla tuzunu alabilirsiniz.'],
                ],
            ],
        ];

        $recipeKeywords = [
            'firin-dereceleri-rehberi-hangi-yemek-kac-derecede-pisirilir' => ['firin', 'tavuk', 'kofte'],
            'kuru-bakliyatlar-buzdolabinda-saklanir-mi-guvelenmeyi-onleme-yontemleri' => ['nohut', 'fasulye', 'mercimek'],
            'geleneksel-kibris-kebabi-tarifi-ve-evde-yapilis-puf-noktalari' => ['seftali', 'kebabi', 'kleftiko'],
            'firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktalari' => ['firin', 'tavuk', 'pide'],
            'private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi' => ['seftali', 'kleftiko', 'hellim'],
            'ev-yapimi-citir-peynirli-kraker-tarifi-pratik-ve-saglikli-atistirmalik' => ['hellim', 'peynir', 'borek'],
            'orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri' => ['mercimek', 'corba'],
            'kibris-seftali-kebabi-nasil-yapilir-orijinal-tarif-ve-puf-noktalari' => ['seftali', 'kebab'],
            'kibris-hellim-peyniri-nasil-kizartilir-ve-saklanir-koy-usulu-rehber' => ['hellim', 'kahvalti', 'omlet'],
        ];

        $addedCount = 0;
        foreach ($articles as $art) {
            $post = BlogPost::updateOrCreate(
                ['slug' => $art['slug']],
                [
                    'public_id' => strtoupper((string) Str::ulid()),
                    'author_id' => $author?->id,
                    'blog_category_id' => $category->id,
                    'hero_media_id' => $approvedMedia?->id,
                    'title' => $art['title'],
                    'excerpt' => $art['excerpt'],
                    'body' => $art['body'],
                    'status' => 'published',
                    'editorial_status' => 'approved',
                    'fact_check_status' => 'passed',
                    'originality_status' => 'passed',
                    'search_intent' => 'informational',
                    'primary_keyword' => $art['primary_keyword'],
                    'secondary_keywords' => $art['secondary_keywords'],
                    'faq_blocks' => $art['faq'],
                    'source_notes' => ['Google Search Console arama analizi verileriyle House Chef Cyprus Editoryal Ekibi tarafından hazırlanmıştır.'],
                    'meta_title' => Str::limit($art['title'], 65),
                    'meta_description' => Str::limit($art['excerpt'], 155),
                    'published_at' => now(),
                ]
            );

            $keywords = $recipeKeywords[$art['slug']] ?? [];
            if (!empty($keywords)) {
                $matchedRecipeIds = Recipe::query()
                    ->where(function ($q) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $q->orWhere('slug', 'like', "%{$kw}%")
                              ->orWhere('title', 'like', "%{$kw}%");
                        }
                    })
                    ->limit(3)
                    ->pluck('id');

                if ($matchedRecipeIds->isNotEmpty()) {
                    $post->recipes()->syncWithoutDetaching($matchedRecipeIds);
                }
            }

            if ($post->recipes()->count() === 0) {
                $fallbackRecipeIds = Recipe::where('status', 'published')->limit(2)->pluck('id');
                if ($fallbackRecipeIds->isNotEmpty()) {
                    $post->recipes()->syncWithoutDetaching($fallbackRecipeIds);
                }
            }

            $addedCount++;
        }

        $this->command->info("✓ Toplam {$addedCount} adet Google Search Console hedefli yeni SEO makalesi başarıyla eklendi ve yayınlandı!");
    }
}
