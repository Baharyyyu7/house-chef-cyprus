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
