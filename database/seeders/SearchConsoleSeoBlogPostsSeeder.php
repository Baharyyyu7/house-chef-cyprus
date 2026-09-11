<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Media;
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

Fırında yemek pişirirken yapılan en büyük hatalardan biri yanlış sıcaklık derecesi seçmektir. Çok yüksek sıcaklık yemeğin dışını yakıp içini çiğ bırakırken, çok düşük sıcaklık yemeğin kurumasına sebze ve etlerin suyunu salmasına neden olur.

İşte profesyonel şeflerin kullandığı **en doğru fırın dereceleri tablosu**:

---

## 🎂 Hamur İşleri ve Tatlılar İçin Fırın Dereceleri

*   **Kekler ve Pandispanya:** 170°C - 180°C (Önceden ısıtılmış fırında, ortalama 35-45 dakika)
*   **Börekler ve Çörekler:** 180°C - 200°C (Alt-üst fanlı ayarda, 30-40 dakika)
*   **Kurabiyeler:** 160°C - 170°C (Hassas pişirme için, 12-18 dakika)
*   **Mayalı Ekmekler:** 200°C - 220°C (İlk 10 dakika buhar desteği ile)

---

## 🍗 Et, Tavuk ve Balık İçin Fırın Dereceleri

*   **Bütün Tavuk veya Baget:** 190°C - 200°C (Dışı çıtır, içi sulu kalması için 45-60 dakika)
*   **Kırmızı Et (Pirzola / Biftek):** 200°C - 220°C (Hızlı mühürleme ve pişirme)
*   **Kuzu İncik / Tandır:** 150°C - 160°C (Kısık ateşte, ağır ağır 2.5 - 3 saat)
*   **Balık Bütün (Levrek / Çupra):** 180°C - 190°C (Yağlı kağıt içinde 25-30 dakika)

---

## 🥦 Sebze Y Yemekleri ve Gratenler

*   **Fırın Sebze / Karnabahar Graten:** 180°C - 190°C (Üzeri kızarana kadar 25-35 dakika)
*   **Karnıyarık / Patlıcan Oturtma:** 190°C (Yaklaşık 30-40 dakika)

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
# 🫘 Kuru Bakliyatlar Buzdolabında Saklanır mı?

Evlerimizde sıklıkla tükettiğimiz mercimek, nohut, kuru fasulye gibi bakliyatların tazeliğini koruması mutfak ekonomisi açısından büyük önem taşır. Özellikle nemli ve sıcak yaz aylarında böceklenme ve güvelenme en sık karşılaşılan sorundur.

---

## ❄️ Kuru Bakliyat Buzdolabında veya Buzlukta Saklanır mı?

**Evet, saklanır!** Özellikle nem oranı yüksek veya sıcak bölgelerde (Kıbrıs ve Akdeniz iklimi gibi) kuru bakliyatları cam kavanozlarda buzdolabında saklamak güvelenmeyi tamamen engeller.

### 1. Kuru (Çiğ) Bakliyat Saklama
*   Çiğ bakliyatları hava geçirmez cam kavanozlara koyarak buzdolabının sebzelik veya raf bölümünde saklayabilirsiniz.
*   İçine **1-2 adet defne yaprağı** veya 1 tatlı kaşığı kaya tuzu eklemek nemlenmeyi ve böceklenmeyi önler.

### 2. Haşlanmış Bakliyatları Buzlukta (Dondurucuda) Saklama
*   Nohut ve kuru fasulyeyi önceden haşlayıp soğuttuktan sonra buzdolabı poşetlerine porsiyonlayarak dondurucuda **6 aya kadar** saklayabilirsiniz.
*   Bu yöntem, hafta içi pratik bir şekilde yemek pişirmenizi sağlar.

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
# 🍢 Geleneksel Kıbrıs Kebabı Tarifi

Kıbrıs mutfak kültürünün en gurur verici lezzetlerinden biri olan **Kıbrıs Kebabı**, genellikle taze kuzu gomene (terbiyeli gömlek yağı) sarılan kıyma ve özel baharat karışımı ile hazırlanır.

---

## 🛒 Kıbrıs Kebabı İçin Malzemeler

*   **Kıyma:** 500g kuzu ve dana karışımı orta yağlı kıyma
*   **Kuzu Gömleği (Gomen):** 1 adet taze kuzu gömlek yağı
*   **Soğan:** 2 adet rendelenmiş (suyu sıkılmış)
*   **Maydanoz:** 1 demet ince kıyılmış
*   **Baharatlar:** Tuz, karabiber, kimyon
*   **Ekmek:** Kıbrıs pidesi veya taze tırnak pide

---

## 👩‍🍳 Adım Adım Yapılışı

1.  **Gömlek Hazırlığı:** Kuzu gömlek yağını ılık ve sirkeli suda 15 dakika bekleterek yumuşatın ve durulayın.
2.  **Harcın Hazırlanması:** Kıyma, soğan, maydanoz ve baharatları derin bir kapta özleşene kadar yoğurun. Oval köfteler şekillendirin.
3.  **Sarma İşlemi:** Yumuşayan gömlek yağını düz bir zemine serin, köfteleri gömleğe sararak kesin.
4.  **Pişirme:** Odun kömürü ızgarasında veya döküm tavada gömlek yağı eriyip köfteler nar gibi kızarana kadar pişirin.

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
# ⏱️ Fırın Kaç Dakikada Isınır?

Yemek ve hamur işi pişirirken tariflerde sıkça gördüğümüz **"önceden ısıtılmış fırın"** kuralı, yemeğin kabarması ve iç sıcaklığının doğru dengelenmesi için şarttır.

---

## 🌡️ Derecelere Göre Fırın Isınma Süreleri

Standart bir ankastre veya mini fırının istenen dereceye ulaşma süreleri şöyledir:

*   **150°C - 160°C:** Yaklaşık **8 - 10 dakika**
*   **180°C (En Çok Kullanılan):** Yaklaşık **10 - 12 dakika**
*   **200°C - 220°C:** Yaklaşık **12 - 15 dakika**

---

## 💡 Neden Fırını Önceden Isıtmalıyız?

1.  **Kabarma Kontrolü:** Kek ve ekmek gibi hamur işleri soğuk fırına atılırsa kabarmaz ve söner.
2.  **Mühürleme:** Et ve balık yüksek sıcaklıktaki fırına girdiğinde dış yüzeyi hızla mühürlenir ve suyu içinde kalır.

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
# 👨‍🍳 Private Chef Cyprus: Evinizde Gurme Mutfak Deneyimi

Kıbrıs'ın benzersiz ikliminde, villanızda veya özel davetlerinizde unutulmaz bir gastronomi deneyimi yaşamak ister misiniz? **Private Chef Cyprus (Kıbrıs Özel Şef Hizmeti)**, restoran kalitesindeki lezzetleri kendi özel alanınıza taşır.

---

## 🍷 Özel Şef Hizmeti Neleri Kapsar?

*   **Kişiselleştirilmiş Menü Tasarımı:** Damak tadınıza, alerjenlerinize ve diyet tercihlerinize göre özel olarak hazırlanan menüler.
*   **Taze ve Yöresel Malzeme Tedariği:** Günlük Kıbrıs hellimi, taze deniz ürünleri ve yerel zeytinyağları ile hazırlık.
*   **Mutfak ve Servis Yönetimi:** Yemeklerin hazırlanması, sunumu ve etkinlik sonrası mutfağın tertemiz teslim edilmesi.

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
# 🧀 Ev Yapımı Çıtır Peynirli Kraker Tarifi

Marketlerde satılan paketli krakerler yerine evde doğal peynir ve tereyağı ile hazırlayabileceğiniz ağızda dağılan çıtır peynirli krakerler yapmaya ne dersiniz?

---

## 🛒 Malzemeler

*   **Peynir:** 1 su bardağı rendelenmiş sert kaşar veya eskitilmiş beyaz peynir
*   **Un:** 1.5 su bardağı buğday unu
*   **Tereyağı:** 75g (soğuk ve küp doğranmış)
*   **Soğuk Su:** 2-3 yemek kaşığı
*   **Tuz ve Baharat:** 1 çay kaşığı tuz, çörek otu, kekik veya toz kırmızı biber

---

## 👩‍🍳 Adım Adım Yapılışı

1.  **Hamur Yoğurma:** Mutfak robotunda un, soğuk tereyağı ve rendelenmiş peyniri kum kıvamına gelene kadar çekin.
2.  **Toparlama:** Soğuk suyu ekleyip hamuru hızlıca toparlayın. Streç filme sarıp buzdolabında 20 dakika dinlendirin.
3.  **Açma ve Şekil:** Hamuru merdane ile 3mm kalınlığında açın, rulet veya kurabiye kalıpları ile kesin.
4.  **Pişirme:** Önceden ısıtılmış **180°C fırında 12-15 dakika** hafif altın sarısı olana kadar pişirin.

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

Lokantalarda içtiğiniz mercimek çorbasının evde yapılanlardan en büyük farkı, pürüzsüz ipeksi dokusu ve un-tereyağı dengesidir. Çoğu ev tarifinde mercimek doğrudan sebzelerle haşlanıp blenderdan geçirilir; oysa lokanta usulü çorbanın sırrı doğru kavurma tekniği ve geleneksel süzme aşamasında saklıdır.

---

## 🍲 İpeksi Kıvamı Veren Dört Temel Adım

1. **Unu Kokusu Çıkana Kadar Kavurmak:** Tereyağı ve zeytinyağı karışımında 1 yemek kaşığı unun hafif fındık kokusu verene kadar kavrulması, çorbaya kadife gibi bir bağlayıcılık katar.
2. **Kırmızı Mercimeğin Nişastasından Arındırılması:** Mercimekler berrak su çıkana kadar en az 3-4 kez soğuk suyla yıkanmalıdır. Fazla nişasta çorbanın berraklığını bozar.
3. **Doğal Et veya Tavuk Suyu:** Yalnızca su yerine yarı yarıya süzülmüş kemik veya tavuk suyu kullanmak lokanta lezzetinin temel taşıdır.
4. **Tel Süzgeçten Geçirme:** Blender hava kabarcıkları oluşturup çorbanın rengini açabilir. Gerçek lokanta usulünde çorba kepçeyle tel süzgeçten geçirilerek pürüzsüzleştirilir.

---

## 🌶️ Servis ve Yağ Yakma Püf Noktaları

Çorba kaselere alındıktan sonra üzerine kızdırılmış tereyağı, tatlı toz kırmızı biber ve kuru nane gezdirilir. Yanında mutlaka çeyrek limon ve taze sıcak pide dilimleri servis edilir.

Mutfakta hemen denemek için [Klasik Kırmızı Mercimek Çorbası](/tarifler/klasik-kirmizi-mercimek-corbasi) ve [Ezogelin Çorbası](/tarifler/ezogelin-corbasi) tariflerimize göz atabilirsiniz.
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
            $addedCount++;
        }

        $this->command->info("✓ Toplam {$addedCount} adet Google Search Console hedefli yeni SEO makalesi başarıyla eklendi ve yayınlandı!");
    }
}
