<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeProvenance;
use App\Models\RecipeStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KlasikTariflerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Klasik Tarifler Seeder başlatılıyor...');

        // Categories
        $categories = [
            'corba' => Category::firstOrCreate(
                ['slug' => 'corba'],
                ['name' => 'Çorbalar', 'description' => 'Sıcak başlangıçlar', 'public_id' => (string) Str::ulid()]
            ),
            'ana-yemek' => Category::firstOrCreate(
                ['slug' => 'ana-yemek'],
                ['name' => 'Ana Yemekler', 'description' => 'Doyurucu ana yemekler', 'public_id' => (string) Str::ulid()]
            ),
            'zeytinyagli-sebze' => Category::firstOrCreate(
                ['slug' => 'zeytinyagli-sebze'],
                ['name' => 'Zeytinyağlılar ve Sebzeler', 'description' => 'Hafif ve sağlıklı', 'public_id' => (string) Str::ulid()]
            ),
            'salata-meze' => Category::firstOrCreate(
                ['slug' => 'salata-meze'],
                ['name' => 'Salatalar ve Mezeler', 'description' => 'Sofranın tamamlayıcıları', 'public_id' => (string) Str::ulid()]
            ),
            'hamur-isi' => Category::firstOrCreate(
                ['slug' => 'hamur-isi'],
                ['name' => 'Hamur İşleri', 'description' => 'Lezzetli hamur işleri', 'public_id' => (string) Str::ulid()]
            ),
            'tatli' => Category::firstOrCreate(
                ['slug' => 'tatli'],
                ['name' => 'Tatlılar', 'description' => 'Tatlı krizine son', 'public_id' => (string) Str::ulid()]
            ),
        ];

        // Cuisines
        $cuisines = [
            'turk-mutfagi' => Cuisine::firstOrCreate(
                ['slug' => 'turk-mutfagi'],
                ['name' => 'Türk Mutfağı', 'country' => 'Türkiye', 'public_id' => (string) Str::ulid()]
            ),
            'kibris-mutfagi' => Cuisine::firstOrCreate(
                ['slug' => 'kibris-mutfagi'],
                ['name' => 'Kıbrıs Mutfağı', 'country' => 'Kıbrıs', 'public_id' => (string) Str::ulid()]
            ),
        ];

        // Ingredient Helper
        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;

        $recipesData = [
            // ÇORBALAR
            [
                'title' => 'Klasik Kırmızı Mercimek Çorbası',
                'slug' => 'klasik-kirmizi-mercimek-corbasi',
                'excerpt' => 'Her sofranın vazgeçilmezi şifa kaynağı çorba.',
                'description' => 'Geleneksel yöntemlerle hazırlanan, içinizi ısıtacak nefis kırmızı mercimek çorbası tarifi.',
                'category_id' => $categories['corba']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 10,
                'cook' => 25,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Kırmızı Mercimek', 'qty' => 1.5, 'unit' => 'su bardağı', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Havuç', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Patates', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 150, 'canon_unit' => 'g'],
                    ['name' => 'Tereyağı', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 15, 'canon_unit' => 'g'],
                    ['name' => 'Sıcak Su', 'qty' => 6, 'unit' => 'su bardağı', 'canon_qty' => 1200, 'canon_unit' => 'ml'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Soğanı, havucu ve patatesi iri parçalar halinde doğrayın.', 'tip' => 'Sebzeleri püre yapacağımız için şekilleri çok önemli değil.', 'duration' => 300],
                    ['instruction' => 'Tencereye zeytinyağı ve tereyağını alıp soğanları hafifçe kavurun.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Havuç ve patatesleri ilave edip 2-3 dakika daha kavurmaya devam edin.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Yıkayıp süzdüğünüz kırmızı mercimekleri ve sıcak suyu tencereye ekleyin. Kaynayana kadar yüksek ateşte, kaynadıktan sonra kısık ateşte sebzeler ve mercimekler yumuşayana kadar yaklaşık 25 dakika pişirin.', 'tip' => null, 'duration' => 1500],
                    ['instruction' => 'Çorba piştikten sonra pürüzsüz bir kıvam alana kadar blenderdan geçirin. Tuz ve baharatlarını ekleyip bir taşım daha kaynatın.', 'tip' => 'Kıvamı koyu gelirse bir miktar daha sıcak su ekleyebilirsiniz.', 'duration' => 300],
                ],
            ],
            [
                'title' => 'Ezogelin Çorbası',
                'slug' => 'ezogelin-corbasi',
                'excerpt' => 'Bulgur ve nanenin eşsiz uyumu.',
                'description' => 'Yöresel lezzetlerimizden olan, hafif acılı ve doyurucu ezogelin çorbası.',
                'category_id' => $categories['corba']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 10,
                'cook' => 30,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Kırmızı Mercimek', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'İnce Bulgur', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 80, 'canon_unit' => 'g'],
                    ['name' => 'Pirinç', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 80, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Domates Salçası', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Biber Salçası', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Nane', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 2, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Mercimek, pirinç ve bulguru güzelce yıkayıp süzün.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'İnce doğranmış soğanı sıvı yağ ve tereyağı ile tencerede kavurun.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Salçaları ve naneyi ekleyip kokusu çıkana kadar kavurmaya devam edin.', 'tip' => 'Nane salçayla kavrulduğunda lezzeti daha iyi ortaya çıkar.', 'duration' => 120],
                    ['instruction' => 'Yıkanmış bakliyatları tencereye ilave edip 1-2 dakika daha kavurun.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Sıcak suyunu ekleyip, bakliyatlar iyice yumuşayana kadar yaklaşık 30 dakika kısık ateşte pişirin. İsteğe göre blenderdan hafifçe geçirebilirsiniz.', 'tip' => null, 'duration' => 1800],
                ],
            ],
            [
                'title' => 'Tarhana Çorbası',
                'slug' => 'tarhana-corbasi',
                'excerpt' => 'Kış aylarının şifası.',
                'description' => 'Ev yapımı tarhana ile hazırlanan geleneksel tarhana çorbası.',
                'category_id' => $categories['corba']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 5,
                'cook' => 15,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Toz Tarhana', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'g'],
                    ['name' => 'Tereyağı', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 15, 'canon_unit' => 'g'],
                    ['name' => 'Domates Salçası', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Su', 'qty' => 5, 'unit' => 'su bardağı', 'canon_qty' => 1000, 'canon_unit' => 'ml'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'çay kaşığı', 'canon_qty' => 3, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Tarhanayı 1 su bardağı soğuk su ile bir kasede karıştırarak çözdürün.', 'tip' => 'Topaklanmaması için soğuk su kullanılması önemlidir.', 'duration' => 120],
                    ['instruction' => 'Tencerede tereyağını eritin ve salçayı hafifçe kavurun.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Kalan 4 bardak suyu tencereye ekleyin ve kaynamaya bırakın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Kaynayan suya çözdürdüğünüz tarhanayı azar azar ve sürekli karıştırarak ilave edin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Çorba kıvam alıp kaynayana kadar sürekli karıştırarak 10-15 dakika pişirin. Tuzunu ayarlayıp sıcak servis yapın.', 'tip' => null, 'duration' => 900],
                ],
            ],

            // ANA YEMEK (ET VE TAVUK)
            [
                'title' => 'Klasik Kuru Fasulye',
                'slug' => 'klasik-kuru-fasulye',
                'excerpt' => 'Milli yemeğimiz etli kuru fasulye.',
                'description' => 'Pilavın en iyi dostu, lokanta usulü tam kıvamında etli kuru fasulye.',
                'category_id' => $categories['ana-yemek']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 60,
                'rest' => 720, // 1 gece önceden ıslatma
                'servings' => 4,
                'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Kuru Fasulye', 'qty' => 2, 'unit' => 'su bardağı', 'canon_qty' => 350, 'canon_unit' => 'g'],
                    ['name' => 'Kuşbaşı Et', 'qty' => 200, 'unit' => 'gram', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Domates Salçası', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 40, 'canon_unit' => 'g'],
                    ['name' => 'Sıvı Yağ', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Kuru fasulyeleri bir gece önceden bol suda ıslatın.', 'tip' => 'Islatma suyuna biraz tuz eklerseniz daha iyi şişer.', 'duration' => 300],
                    ['instruction' => 'Ertesi gün fasulyelerin suyunu süzüp, hafif diri kalacak şekilde haşlayın.', 'tip' => null, 'duration' => 1800],
                    ['instruction' => 'Tencereye sıvı yağı alıp etleri suyunu salıp çekene kadar kavurun.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Yemeklik doğranmış soğanları ekleyip pembeleşene kadar kavurmaya devam edin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Salçayı ilave edip kokusu çıkana kadar kavurun.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Haşlanmış fasulyeleri ve üzerini geçecek kadar sıcak suyu ekleyip, fasulyeler ve etler tamamen yumuşayana kadar kısık ateşte pişirin.', 'tip' => null, 'duration' => 2400],
                ],
            ],
            [
                'title' => 'Fırın Tavuk Baget',
                'slug' => 'firin-tavuk-baget',
                'excerpt' => 'Patatesle buluşan pratik lezzet.',
                'description' => 'Fırında nar gibi kızarmış, özel soslu tavuk baget ve patates yemeği.',
                'category_id' => $categories['ana-yemek']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 45,
                'rest' => 0,
                'servings' => 3,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Tavuk Baget', 'qty' => 6, 'unit' => 'adet', 'canon_qty' => 900, 'canon_unit' => 'g'],
                    ['name' => 'Patates', 'qty' => 3, 'unit' => 'adet', 'canon_qty' => 450, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 60, 'canon_unit' => 'ml'],
                    ['name' => 'Sarımsak', 'qty' => 2, 'unit' => 'diş', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Kekik', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 3, 'canon_unit' => 'g'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Zeytinyağı, ezilmiş sarımsak, kekik, tuz ve karabiberi geniş bir kapta karıştırarak marinasyon sosunu hazırlayın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Tavuk bagetleri sosa bulayın. Vaktiniz varsa 1-2 saat buzdolabında dinlendirin.', 'tip' => 'Bagetlerin üzerine çizikler atarsanız sosu daha iyi çeker.', 'duration' => 300],
                    ['instruction' => 'Patatesleri elma dilimi şeklinde doğrayın ve kalan sosa bulayın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Tavukları ve patatesleri fırın tepsisine dizin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Önceden ısıtılmış 200 derece fırında üzerleri nar gibi kızarana kadar yaklaşık 45 dakika pişirin.', 'tip' => null, 'duration' => 2700],
                ],
            ],
            [
                'title' => 'Kıbrıs Usulü Kleftiko (Kuzu Tandır)',
                'slug' => 'kibris-usulu-kleftiko',
                'excerpt' => 'Kıbrıs\'ın meşhur fırın kebabı.',
                'description' => 'Ağır ağır pişen, lokum gibi kuzu eti ve sebzelerin eşsiz uyumu.',
                'category_id' => $categories['ana-yemek']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 20,
                'cook' => 180,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Kuzu But', 'qty' => 1, 'unit' => 'kg', 'canon_qty' => 1000, 'canon_unit' => 'g'],
                    ['name' => 'Patates', 'qty' => 4, 'unit' => 'adet', 'canon_qty' => 600, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Domates', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Sarımsak', 'qty' => 4, 'unit' => 'diş', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Defne Yaprağı', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 1, 'canon_unit' => 'g'],
                    ['name' => 'Biberiye', 'qty' => 1, 'unit' => 'dal', 'canon_qty' => 2, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Kuzu etini iri parçalar halinde kesin veya bütün bırakıp üzerine derin çizikler atın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Zeytinyağı, ezilmiş sarımsak, tuz ve karabiber ile eti iyice ovalayın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Patatesleri dörde bölün, soğanları ve domatesleri iri iri doğrayın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Fırın tepsisine veya toprak güvece etleri ve sebzeleri yerleştirin. Aralarına defne yaprağı ve biberiye dallarını koyun.', 'tip' => 'Fırın kağıdı ve alüminyum folyo ile hava almayacak şekilde kapatırsanız daha iyi pişer.', 'duration' => 300],
                    ['instruction' => 'Önceden ısıtılmış 160-170 derece fırında 2.5 - 3 saat kadar ağır ağır pişirin. Son 20 dakika üzerini açıp etlerin kızarmasını sağlayın.', 'tip' => null, 'duration' => 10800],
                ],
            ],

            // ZEYTİNYAĞLI VE SEBZE
            [
                'title' => 'Zeytinyağlı Taze Fasulye',
                'slug' => 'zeytinyagli-taze-fasulye',
                'excerpt' => 'Yaz aylarının vazgeçilmezi.',
                'description' => 'Zeytinyağının hafifliğiyle buluşan, bol domatesli taze fasulye.',
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 40,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Taze Fasulye', 'qty' => 500, 'unit' => 'gram', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Domates', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 0.5, 'unit' => 'çay bardağı', 'canon_qty' => 50, 'canon_unit' => 'ml'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Taze fasulyelerin uçlarını kırıp kılçıklarını temizleyin, istenilen boyutta doğrayın.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Soğanı yemeklik doğrayın, domateslerin kabuklarını soyup küp küp kesin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Tencereye zeytinyağını alıp soğanları pembeleşene kadar kavurun.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Fasulyeleri ekleyip rengi dönene kadar kavurun, ardından domatesleri ilave edin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Tuz, şeker ve çok az sıcak su ekleyip, tencerenin kapağını kapatarak kısık ateşte fasulyeler yumuşayana kadar pişirin. Soğuk veya ılık servis yapın.', 'tip' => 'Zeytinyağlı yemeklerin kendi suyuyla pişmesi lezzetini artırır.', 'duration' => 2400],
                ],
            ],
            [
                'title' => 'Zeytinyağlı Enginar',
                'slug' => 'zeytinyagli-enginar',
                'excerpt' => 'Baharın müjdecisi hafif lezzet.',
                'description' => 'Garnitürlü, limonlu ve zeytinyağlı enfes enginar yemeği.',
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 30,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Temizlenmiş Enginar', 'qty' => 4, 'unit' => 'adet', 'canon_qty' => 4, 'canon_unit' => 'adet'],
                    ['name' => 'Garnitür (Havuç, Bezelye, Patates)', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 150, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 0.5, 'unit' => 'çay bardağı', 'canon_qty' => 50, 'canon_unit' => 'ml'],
                    ['name' => 'Limon Suyu', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 40, 'canon_unit' => 'ml'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Enginarları kararmaması için limonlu suda bekletin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Soğanı yemeklik doğrayıp zeytinyağında hafifçe soteleyin.', 'tip' => null, 'duration' => 240],
                    ['instruction' => 'Garnitürü (havuç, bezelye, patates karışımı) ekleyip 1-2 dakika daha çevirin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Geniş bir tencereye enginarları çukur kısımları üste bakacak şekilde dizin. İçlerine hazırladığınız garnitürlü harcı paylaştırın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Üzerine limon suyu, şeker, tuz ve sıcak suyu ekleyip kısık ateşte enginarlar yumuşayana kadar pişirin.', 'tip' => 'Sıcak değil soğuk servis edilmesi önerilir.', 'duration' => 1800],
                ],
            ],
            [
                'title' => 'İmam Bayıldı',
                'slug' => 'imam-bayildi',
                'excerpt' => 'Zeytinyağlı patlıcan yemeklerinin şahı.',
                'description' => 'Bol soğan ve domates harcıyla doldurulmuş nefis zeytinyağlı patlıcan.',
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 20,
                'cook' => 45,
                'rest' => 0,
                'servings' => 6,
                'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Patlıcan', 'qty' => 6, 'unit' => 'adet', 'canon_qty' => 750, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 3, 'unit' => 'adet', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Domates', 'qty' => 4, 'unit' => 'adet', 'canon_qty' => 450, 'canon_unit' => 'g'],
                    ['name' => 'Sarımsak', 'qty' => 6, 'unit' => 'diş', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 1, 'unit' => 'çay bardağı', 'canon_qty' => 100, 'canon_unit' => 'ml'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 15, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Patlıcanları alacalı soyup acısının çıkması için tuzlu suda bekletin, ardından kurulayıp hafifçe sıvı yağda kızartın (veya fırınlayın).', 'tip' => 'Fırınlamak daha hafif bir sonuç verecektir.', 'duration' => 1200],
                    ['instruction' => 'Soğanları piyazlık (yarım ay) doğrayın. Domateslerin kabuklarını soyup küp küp doğrayın. Sarımsakları incecik dilimleyin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Zeytinyağında soğanları ve sarımsakları yumuşayana kadar kavurun. Domatesleri, tuz ve şekeri ekleyip domatesler suyunu çekene kadar pişirin.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Kızarmış patlıcanların ortasını bir kaşık yardımıyla açın ve hazırladığınız iç harcı bolca doldurun.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Tencereye dizip dibine çok az su ilave edin ve kısık ateşte yaklaşık 20-25 dakika pişirin. Soğuduktan sonra servis yapın.', 'tip' => null, 'duration' => 1500],
                ],
            ],

            // SALATA - MEZE
            [
                'title' => 'Atom (Acılı Ezme)',
                'slug' => 'atom-acili-ezme',
                'excerpt' => 'Kebapların ayrılmaz parçası.',
                'description' => 'Bol malzemeli, hafif acılı, tam kıvamında lokanta usulü ezme.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 0,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Domates', 'qty' => 4, 'unit' => 'adet', 'canon_qty' => 400, 'canon_unit' => 'g'],
                    ['name' => 'Sivri Biber', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Maydanoz', 'qty' => 0.25, 'unit' => 'demet', 'canon_qty' => 15, 'canon_unit' => 'g'],
                    ['name' => 'Nar Ekşisi', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 30, 'canon_unit' => 'ml'],
                    ['name' => 'Pul Biber', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Domateslerin kabuklarını soyun. Biberlerin çekirdeklerini çıkarın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Domates, biber, soğan ve maydanozu zırhla veya keskin bir bıçakla olabildiğince ince kıyın.', 'tip' => 'Mutfak robotu kullanırsanız malzemeler çok sulanır, bıçakla doğramak en iyisidir.', 'duration' => 600],
                    ['instruction' => 'Doğranmış malzemeleri bir süzgece alıp fazla suyunun süzülmesini bekleyin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Geniş bir kasede malzemeleri birleştirin. Üzerine zeytinyağı, nar ekşisi, pul biber ve tuzu ekleyin.', 'tip' => null, 'duration' => 60],
                    ['instruction' => 'Tüm malzemeleri iyice karıştırıp servis tabağına alın. Tercihen soğuk servis yapın.', 'tip' => null, 'duration' => 60],
                ],
            ],
            [
                'title' => 'Humus',
                'slug' => 'humus',
                'excerpt' => 'Nohut ve tahinin muhteşem uyumu.',
                'description' => 'Ortadoğu ve Türk mutfağının vazgeçilmez mezesi, pürüzsüz kıvamlı humus.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 10,
                'cook' => 0,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Haşlanmış Nohut', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Tahin', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'g'],
                    ['name' => 'Limon', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 40, 'canon_unit' => 'ml'],
                    ['name' => 'Sarımsak', 'qty' => 2, 'unit' => 'diş', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Kimyon', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 4, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 30, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Haşlanmış nohutların kabuklarını daha pürüzsüz bir kıvam için tek tek soyun.', 'tip' => 'Kabukları soymak humusun pürüzsüzlüğü için çok önemlidir.', 'duration' => 600],
                    ['instruction' => 'Nohutları mutfak robotuna alın, üzerine ezilmiş sarımsak, limon suyu, tahin, kimyon ve tuzu ekleyin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Tüm malzemeleri krema kıvamına gelene kadar robottan geçirin.', 'tip' => 'Çok katı olursa azar azar nohutun haşlama suyundan veya buzlu su ekleyebilirsiniz.', 'duration' => 180],
                    ['instruction' => 'Servis tabağına yayıp, ortasında hafif bir çukur oluşturun.', 'tip' => null, 'duration' => 60],
                    ['instruction' => 'Üzerine sızma zeytinyağı gezdirip, toz kırmızı biber veya kimyon ile süsleyerek servis yapın.', 'tip' => null, 'duration' => 60],
                ],
            ],
            [
                'title' => 'Patlıcan Salatası (Babaganuş)',
                'slug' => 'patlican-salatasi-babaganus',
                'excerpt' => 'Köz patlıcanın en lezzetli hali.',
                'description' => 'Közlenmiş patlıcan, tahin ve sarımsakla hazırlanan nefis meze.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 20,
                'rest' => 0,
                'servings' => 4,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Patlıcan', 'qty' => 3, 'unit' => 'adet', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Tahin', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 30, 'canon_unit' => 'g'],
                    ['name' => 'Limon', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 40, 'canon_unit' => 'ml'],
                    ['name' => 'Sarımsak', 'qty' => 2, 'unit' => 'diş', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Patlıcanlara bıçakla birkaç delik açın ve ocakta veya fırında iyice közleyin.', 'tip' => 'Ocakta közlemek isli tadı artırır.', 'duration' => 1200],
                    ['instruction' => 'Közlenen patlıcanların kabuklarını soyun ve ince ince kıyın veya ezin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Bir kasede ezilmiş sarımsak, limon suyu, tahin, zeytinyağı ve tuzu karıştırarak sosu hazırlayın.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Patlıcanları hazırladığınız sosla iyice harmanlayın.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Servis tabağına alıp üzerini maydanoz ve zeytinyağı ile süsleyerek servis edin.', 'tip' => null, 'duration' => 60],
                ],
            ],

            // HAMUR İŞİ
            [
                'title' => 'Klasik Lahmacun',
                'slug' => 'klasik-lahmacun',
                'excerpt' => 'Çıtır çıtır ev yapımı lahmacun.',
                'description' => 'Ev fırınında da yapabileceğiniz, incecik hamuru ve bol malzemeli iç harcıyla lahmacun.',
                'category_id' => $categories['hamur-isi']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 30,
                'cook' => 15,
                'rest' => 60,
                'servings' => 6,
                'difficulty' => 'hard',
                'ingredients' => [
                    ['name' => 'Un', 'qty' => 3, 'unit' => 'su bardağı', 'canon_qty' => 400, 'canon_unit' => 'g'],
                    ['name' => 'Su', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 200, 'canon_unit' => 'ml'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Kıyma', 'qty' => 300, 'unit' => 'gram', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Domates', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Sivri Biber', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Maydanoz', 'qty' => 0.5, 'unit' => 'demet', 'canon_qty' => 30, 'canon_unit' => 'g'],
                    ['name' => 'Biber Salçası', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Hamur için un, su ve tuzu yoğurarak ele yapışmayan orta sertlikte bir hamur elde edin. Üzerini örtüp 30 dakika dinlendirin.', 'tip' => 'Maya kullanmıyoruz, lahmacun hamuru mayasız olur.', 'duration' => 600],
                    ['instruction' => 'İç harcı için soğan, domates, biber ve maydanozu çok ince kıyın veya robottan hafifçe geçirin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Kıymayı ve sebzeleri bir kaba alın. Biber salçası, sıvı yağ, tuz, karabiber ve pul biber ekleyerek harcı iyice yoğurun.', 'tip' => 'Harcın kolay sürülebilmesi için çok az su ekleyebilirsiniz.', 'duration' => 300],
                    ['instruction' => 'Dinlenen hamurdan mandalina büyüklüğünde bezeler koparın ve unlanmış tezgahta tabak büyüklüğünde incecik açın.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Açtığınız hamurun üzerine iç harçtan koyup kenarlara kadar yayın. Önceden ısıtılmış 250 derece fırında veya krep tavasında altı ve üstü kızarana kadar pişirin.', 'tip' => null, 'duration' => 900],
                ],
            ],
            [
                'title' => 'Peynirli Pide',
                'slug' => 'peynirli-pide-klasik',
                'excerpt' => 'Pazar kahvaltılarının yıldızı.',
                'description' => 'Yumuşacık hamuru ve eriyen peyniriyle ev yapımı pide.',
                'category_id' => $categories['hamur-isi']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 30,
                'cook' => 20,
                'rest' => 60,
                'servings' => 4,
                'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Un', 'qty' => 4, 'unit' => 'su bardağı', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Su', 'qty' => 1.5, 'unit' => 'su bardağı', 'canon_qty' => 300, 'canon_unit' => 'ml'],
                    ['name' => 'Kuru Maya', 'qty' => 1, 'unit' => 'paket', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Beyaz Peynir', 'qty' => 200, 'unit' => 'gram', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kaşar Peyniri', 'qty' => 150, 'unit' => 'gram', 'canon_qty' => 150, 'canon_unit' => 'g'],
                    ['name' => 'Yumurta', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Maydanoz', 'qty' => 0.5, 'unit' => 'demet', 'canon_qty' => 30, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Ilık su, maya ve şekeri karıştırıp mayanın aktifleşmesini bekleyin. Un ve tuzu ekleyip yumuşak bir hamur yoğurun.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Hamurun üzerini kapatıp oda sıcaklığında 1 saat kadar mayalandırın.', 'tip' => null, 'duration' => 3600],
                    ['instruction' => 'İç harcı için ezilmiş beyaz peynir, rendelenmiş kaşar peyniri, ince kıyılmış maydanoz ve 1 yumurta akını karıştırın (sarısını kenarlara sürmek için ayırın).', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Mayalanan hamurdan bezeler alın, uzun oval şeklinde açın. Ortasına peynirli harçtan bolca koyup kenarlarını içe doğru katlayarak pide şekli verin.', 'tip' => null, 'duration' => 480],
                    ['instruction' => 'Kenarlarına yumurta sarısı sürüp, 200 derece fırında kızarana kadar pişirin. Fırından çıkınca kenarlarına tereyağı sürebilirsiniz.', 'tip' => null, 'duration' => 1200],
                ],
            ],

            // TATLILAR
            [
                'title' => 'Sütlaç',
                'slug' => 'sutlac',
                'excerpt' => 'Klasik anne sütlacı.',
                'description' => 'Fırında üzeri kızarmış, tam kıvamında nefis sütlaç.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 10,
                'cook' => 45,
                'rest' => 120, // soğutma
                'servings' => 6,
                'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Süt', 'qty' => 1, 'unit' => 'litre', 'canon_qty' => 1000, 'canon_unit' => 'ml'],
                    ['name' => 'Pirinç', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 80, 'canon_unit' => 'g'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 170, 'canon_unit' => 'g'],
                    ['name' => 'Buğday Nişastası', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Vanilya', 'qty' => 1, 'unit' => 'paket', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Pirinci yıkayıp 2 su bardağı su ile yumuşayana kadar haşlayın.', 'tip' => 'Pirinçlerin çok fazla ezilmemesine dikkat edin.', 'duration' => 900],
                    ['instruction' => 'Haşlanmış pirincin üzerine sütü ve vanilyayı ilave edip kaynamaya bırakın.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Kaynamaya başladıktan sonra şekeri ekleyin ve 5-10 dakika daha kaynatın.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Nişastayı yarım çay bardağı soğuk su ile açın ve kaynayan süte azar azar karıştırarak ekleyin. Kıvam alana kadar pişirin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Sütlacı ısıya dayanıklı kaselere paylaştırın. Fırın tepsisine dizip tepsiye yarıya kadar su doldurun. 200 derece fırının üst rafında üzerleri kızarana kadar fırınlayın.', 'tip' => null, 'duration' => 900],
                ],
            ],
            [
                'title' => 'Künefe',
                'slug' => 'kunefe',
                'excerpt' => 'Hatay\'ın incisi.',
                'description' => 'Sıcak şerbeti ve uzayan peyniriyle çıtır çıtır künefe.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 15,
                'cook' => 20,
                'rest' => 0,
                'servings' => 2,
                'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Tel Kadayıf', 'qty' => 250, 'unit' => 'gram', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Künefe Peyniri', 'qty' => 200, 'unit' => 'gram', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Tereyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'g'],
                    ['name' => 'Toz Şeker (Şerbet için)', 'qty' => 1.5, 'unit' => 'su bardağı', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Su (Şerbet için)', 'qty' => 1.5, 'unit' => 'su bardağı', 'canon_qty' => 300, 'canon_unit' => 'ml'],
                    ['name' => 'Limon Suyu', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Önce şerbeti hazırlayın: Su ve şekeri kaynatın, inmeye yakın limon suyunu ekleyip soğumaya bırakın.', 'tip' => 'Şerbet soğuk, künefe sıcak olmalı.', 'duration' => 900],
                    ['instruction' => 'Kadayıfları elinizle ince ince didin ve eritilmiş tereyağı ile iyice harmanlayın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Künefe tavasını yağlayın. Kadayıfın yarısını tavaya eşit şekilde yayıp elinizle veya bir tabakla iyice bastırın.', 'tip' => 'İyice bastırmak künefenin dağılmasını engeller.', 'duration' => 180],
                    ['instruction' => 'Üzerine rendelenmiş veya dilimlenmiş künefe peynirini yayın (kenarlara çok taşırmayın). Kalan kadayıfı da peynirin üzerine yayıp tekrar sıkıca bastırın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Ocakta kısık ateşte altı kızarana kadar pişirin, sonra bir tabak yardımıyla ters çevirip diğer tarafını da kızartın. Sıcakken üzerine soğuk şerbeti döküp servis yapın.', 'tip' => null, 'duration' => 900],
                ],
            ],
            [
                'title' => 'Kazandibi',
                'slug' => 'kazandibi',
                'excerpt' => 'Sütlü tatlıların en karamelizesi.',
                'description' => 'Tepsi dibi yakılarak yapılan, nefis kıvamlı klasik kazandibi tatlısı.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['turk-mutfagi']->id,
                'prep' => 10,
                'cook' => 30,
                'rest' => 120, // buzdolabında soğutma
                'servings' => 6,
                'difficulty' => 'hard',
                'ingredients' => [
                    ['name' => 'Süt', 'qty' => 1, 'unit' => 'litre', 'canon_qty' => 1000, 'canon_unit' => 'ml'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 170, 'canon_unit' => 'g'],
                    ['name' => 'Buğday Nişastası', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 40, 'canon_unit' => 'g'],
                    ['name' => 'Pirinç Unu', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Vanilya', 'qty' => 1, 'unit' => 'paket', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Tereyağı', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 15, 'canon_unit' => 'g'],
                    ['name' => 'Pudra Şekeri (Tepsi için)', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 30, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Süt, şeker, nişasta ve pirinç ununu tencereye alıp çırpıcıyla karıştırın. Ocağa alıp kaynayana kadar sürekli karıştırarak pişirin.', 'tip' => null, 'duration' => 900],
                    ['instruction' => 'Koyulaşan muhallebiye vanilya ve tereyağını ekleyip ocaktan alın, 2-3 dakika çırpın.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Alüminyum veya çelik bir tepsiyi tereyağı ile bolca yağlayın ve her yerine pudra şekeri serpin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Muhallebiden 2-3 kepçe alıp tepsiye ince bir tabaka halinde yayın. Tepsiyi ocak üzerinde çevirerek şekerin karamelize olmasını ve yanmasını sağlayın.', 'tip' => 'Tepsiyi sürekli çevirmek eşit yanmasını sağlar.', 'duration' => 600],
                    ['instruction' => 'Altı yanan tepsiye kalan muhallebiyi dikkatlice dökün. Oda sıcaklığına gelince buzdolabında en az 2-3 saat dinlendirin. Şeritler halinde kesip rulo yaparak servis edin.', 'tip' => null, 'duration' => 10800],
                ],
            ],
            [
                'title' => 'Kıbrıs Macunu (Cevizli Sucuk)',
                'slug' => 'kibris-macunu-cevizli-sucuk',
                'excerpt' => 'Kıbrıs\'ın doğal atıştırmalığı.',
                'description' => 'Pekmez ve cevizin buluştuğu, kış aylarının enerji deposu geleneksel lezzet.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 45,
                'cook' => 30,
                'rest' => 2880, // 2-3 gün kuruma süresi
                'servings' => 10,
                'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Ceviz İçi', 'qty' => 500, 'unit' => 'gram', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Üzüm Pekmezi', 'qty' => 2, 'unit' => 'su bardağı', 'canon_qty' => 400, 'canon_unit' => 'ml'],
                    ['name' => 'Su', 'qty' => 3, 'unit' => 'su bardağı', 'canon_qty' => 600, 'canon_unit' => 'ml'],
                    ['name' => 'Nişasta', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 40, 'canon_unit' => 'g'],
                    ['name' => 'Un', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Toz Şeker', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 85, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Cevizleri kalın bir iğne yardımıyla sağlam bir ipe veya yorgan ipine tek tek dizin. Aralarında biraz boşluk bırakın.', 'tip' => 'Cevizleri dizmeden önce suda hafifçe ıslatırsanız kırılmadan dizilir.', 'duration' => 1800],
                    ['instruction' => 'Tencereye pekmez, su ve şekeri alıp kaynamaya bırakın.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Ayrı bir kapta nişasta ve unu az miktar suyla pürüzsüz olana kadar ezin. Kaynayan pekmezli karışıma yavaş yavaş ve sürekli karıştırarak ekleyin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Karışım (harç) koyu bir muhallebi kıvamı alana kadar pişirin ve altını kapatın.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'İpe dizdiğiniz cevizleri sıcak harca batırın, her yerinin bulanmasını sağlayın ve asarak kurumaya bırakın. 2-3 gün kuruduktan sonra tüketebilirsiniz.', 'tip' => 'Daha kalın bir tabaka isterseniz ilk kat kuruduktan sonra işlemi tekrarlayabilirsiniz.', 'duration' => 900],
                ],
            ],
        ];

        foreach ($recipesData as $data) {
            if (Recipe::where('slug', $data['slug'])->exists()) {
                $this->command->line("SKIP (exists): {$data['slug']}");

                continue;
            }

            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $data['cuisine_id'],
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'],
                'description' => $data['description'],
                'country' => $data['cuisine_id'] === $cuisines['kibris-mutfagi']->id ? 'Kıbrıs' : 'Türkiye',
                'prep_minutes' => $data['prep'],
                'cook_minutes' => $data['cook'],
                'rest_minutes' => $data['rest'],
                'servings' => $data['servings'],
                'difficulty' => $data['difficulty'],
                'status' => 'published',
                'verification_status' => 'verified',
                'editorial_status' => 'approved',
                'completeness_score' => 85,
                'completeness_status' => 'complete',
                'is_featured' => false,
                'source_name' => 'House Chef Cyprus Editoryal Ekibi',
                'content_origin' => 'original',
                'reliability_score' => 95,
                'published_at' => now(),
                'editorial_reviewed_at' => now(),
                'fact_check_required' => false,
            ]);

            $sortOrder = 1;
            foreach ($data['ingredients'] as $ingData) {
                $ingId = $ing($ingData['name']);
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ingId,
                    'display_quantity' => $ingData['qty'],
                    'display_measure_code' => $ingData['unit'],
                    'sort_order' => $sortOrder++,
                    'canonical_quantity' => $ingData['canon_qty'],
                    'canonical_unit' => $ingData['canon_unit'],
                    'scaling_behavior' => 'scalable',
                    'is_optional' => false,
                    'conversion_confidence' => 'verified',
                ]);
            }

            $position = 1;
            foreach ($data['steps'] as $stepData) {
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'position' => $position,
                    'title' => 'Adım '.$position,
                    'instruction' => $stepData['instruction'],
                    'chef_tip' => $stepData['tip'],
                    'duration_seconds' => $stepData['duration'],
                    'timer_required' => false,
                    'is_optional' => false,
                ]);
                $position++;
            }

            RecipeProvenance::create([
                'recipe_id' => $recipe->id,
                'content_source_id' => null,
                'source_type' => 'original',
                'is_ai_generated' => false,
                'is_user_generated' => false,
                'verification_status' => 'verified',
                'confidence' => 90,
                'reviewed_at' => now(),
                'is_active' => true,
            ]);

            $this->command->line("✓ Eklendi: {$data['slug']}");
        }

        $total = Recipe::where('status', 'published')->count();
        $this->command->info("✓ KlasikTariflerSeeder tamamlandı. Toplam yayında: {$total}");
    }
}
