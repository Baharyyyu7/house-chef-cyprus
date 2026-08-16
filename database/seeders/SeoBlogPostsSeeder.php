<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeoBlogPostsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();
        if (!$author) {
            $author = User::create([
                'name' => 'HouseChef Cyprus',
                'email' => 'info@housechefcyprus.com',
                'password' => bcrypt('password123'),
            ]);
        }

        $category = BlogCategory::firstOrCreate(
            ['slug' => 'mutfak-sirlari'],
            ['name' => 'Mutfak Sırları', 'public_id' => strtoupper((string) Str::ulid())]
        );

        $body = <<<'HTML'
# 🥣 Lokanta Usulü Mercimek Çorbası Tarifi: İpeksi Kıvamın Bilimsel Sırrı

Dışarıda içtiğiniz o pürüzsüz, ipeksi ve derin lezzete sahip süzme mercimek çorbalarını evde birebir yakalamak aslında çok kolay. Ancak ev yapımı çorbaların çoğunda yapılan büyük bir hata var: Kıvam vermesi için çorbaya patates ve havuç eklemek. 

Modern gastronomi bilimi ve gıda teknolojisi araştırmaları, bu geleneksel yöntemin çorbanın tüm dengesini bozduğunu kanıtlıyor. İşte Google arama sonuçlarında "en iyi tarif" olarak parlayacak, **termodinamik ve reolojik olarak optimize edilmiş** gerçek lokanta usulü süzme mercimek çorbası tarifimiz!

---

## ❌ Neden Çorbaya Patates ve Havuç Eklememelisiniz?

Birçok ev tipi tarifte viskoziteyi (kıvamı) artırmak için patates ve havuç gibi nişastalı sebzeler kullanılır. Ancak bu sebzeler pişerken içlerindeki **fruktoz ve sakkaroz** şekerlerini açığa çıkarır. Bu durum, mercimek çorbasının kendine has asil ve tuzlu tat profilini bozarak tatlımsı bir aroma verir ve süzme işlemini zorlaştırır. 

Gerçek lokanta usulü çorbalarda kıvam kararlılığı, sebzelerle değil; un ve tereyağının kontrollü termal dekstrinizasyonu, yani **roux (meyane)** reaksiyonu ile sağlanmalıdır.

---

## 🛒 Tam Ölçülü Lokanta Usulü Süzme Mercimek Çorbası Malzemeleri

Her evde bulunan basit ama bilimsel olarak bir araya getirilmiş en kararlı malzeme listesi:

*   **Kırmızı veya Sarı Mercimek:** 1 su bardağı (yaklaşık 200 g)
*   **Kuru Soğan:** 1 adet (orta boy)
*   **Sarımsak:** 1 diş (iri boy)
*   **Buğday Unu:** 1 yemek kaşığı (tepeleme)
*   **Tereyağı:** 2 yemek kaşığı (30 g)
*   **Bitkisel Sıvı Yağ:** 2 yemek kaşığı (30 ml)
*   **Sıcak Su veya Kemik/Tavuk Suyu:** 1.2 litre (yaklaşık 6 su bardağı)
*   **Baharatlar:** 1 çay kaşığı zerdeçal (altın sarısı renk için), tuz, karabiber, kimyon

---

## 👩‍🍳 Adım Adım Süzme Mercimek Çorbası Nasıl Yapılır?

### 1. Nişasta Arındırma (Yıkama Aşaması)
Kırmızı mercimeklerinizi soğuk su altında, ovalayarak süzgeçte yıkayın. **Durulama suyunun tamamen berraklaşması çok önemlidir.** Bu işlem, serbest amiloz tozunu uzaklaştırır; böylece çorbanız pişerken tencerenin tabanına yapışmaz ve aşırı köpürme yapmaz.

### 2. Termal Dekstrinizasyon (Meyane ve Kavurma)
Kalın tabanlı bir tencerede tereyağı ve sıvı yağ karışımını eritin. Unu ekleyerek unun çiğ kokusu fındıksı aromaya dönene kadar yaklaşık **110°C** sıcaklıkta kavurun. Ardından dörde böldüğünüz soğanı, sarımsağı ve yıkanmış mercimeği ekleyip 2 dakika boyunca kavurmaya devam edin. Bu adım, lipidlerin (yağların) nişasta taneciklerini kaplamasını sağlayarak topaklanmayı tamamen önler.

### 3. Pişirme ve Termal Süreç
Kavrulan malzemelerin üzerine 1.2 litre sıcak suyu (veya et suyunu) ve zerdeçalı ekleyin. **95°C** civarında, kısık ateşte mercimeklerin hücre çeperleri tamamen parçalanıp yumuşayana kadar yaklaşık 25 dakika pişmeye bırakın.

### 4. Reolojik Pürüzsüzlük (Mikronize Etme ve Süzme)
Çorbanız piştikten sonra ocaktan alın. Yüksek devirli bir el blenderı yardımıyla **en az 3 dakika boyunca** kesintisiz olarak mikronize edin. Ardından, lokanta usulü çorbaların o ipeksi akışkanlığını yakalamak için çorbanızı ince gözenekli bir süzgeçten (chinois) geçirin. 

### 5. Servis Dokunuşu
Küçük bir tavada kızdırdığınız pul biberli tereyağını altın sarısı çorbanızın üzerine gezdirerek, yanında taze limon dilimleri ve çıtır ekmek küpleriyle servis edin.
HTML;

        // 1. Gerçek SEO Post
        BlogPost::firstOrCreate(
            ['slug' => 'lokanta-usulu-suzme-mercimek-corbasi-tarifi'],
            [
                'public_id' => strtoupper((string) Str::ulid()),
                'author_id' => $author->id,
                'blog_category_id' => $category->id,
                'title' => 'Orijinal Lokanta Usulü Mercimek Çorbası Tarifi: İpeksi Kıvamın Sırrı',
                'excerpt' => 'Gerçek lokanta usulü süzme mercimek çorbası nasıl yapılır? Patates ve havuç koymadan, tam ölçülü ve ipeksi kıvamda çorba pişirmenin bilimsel sırları burada!',
                'body' => $body,
                'status' => 'draft', // Sistemde var olsun ama public olmasın (isteğe bağlı)
                'editorial_status' => 'approved',
                'meta_title' => 'Orijinal Lokanta Usulü Mercimek Çorbası Tarifi: İpeksi Kıvamın Sırrı',
                'meta_description' => 'Gerçek lokanta usulü süzme mercimek çorbası nasıl yapılır? Patates ve havuç koymadan, tam ölçülü ve ipeksi kıvamda çorba pişirmenin bilimsel sırları burada!',
                'published_at' => now(),
            ]
        );

        // 2. 999 Adet Mock SEO Post
        $faker = \Faker\Factory::create('tr_TR');
        $this->command->info('1 SEO blog eklendi, şimdi kalan 999 SEO blog taslağı oluşturuluyor...');
        
        $posts = [];
        for ($i = 0; $i < 999; $i++) {
            $title = $faker->sentence(rand(4, 8));
            $posts[] = [
                'public_id' => strtoupper((string) Str::ulid()),
                'author_id' => $author->id,
                'blog_category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(5),
                'excerpt' => $faker->paragraph(2),
                'body' => $faker->paragraphs(rand(5, 10), true),
                'status' => 'draft',
                'editorial_status' => 'draft',
                'meta_title' => Str::limit($title, 65),
                'meta_description' => Str::limit($faker->paragraph(1), 155),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Her 100 kayıtta bir DB'ye yazalım (Memory limit yememek için)
            if (count($posts) >= 100) {
                BlogPost::insert($posts);
                $posts = [];
                $this->command->line("  - " . ($i + 1) . " blog eklendi.");
            }
        }
        
        if (count($posts) > 0) {
            BlogPost::insert($posts);
        }
        
        $this->command->info('Toplam 1000 SEO Blog gönderisi başarıyla sisteme eklendi!');
    }
}
