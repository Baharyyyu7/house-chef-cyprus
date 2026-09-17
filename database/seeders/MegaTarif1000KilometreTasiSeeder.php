<?php

declare(strict_types=1);

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

final class MegaTarif1000KilometreTasiSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('1.000 Tarif Kilometre Taşı Seeder başlatılıyor...');

        $categories = [
            'corba' => Category::firstOrCreate(['slug' => 'corba'], ['name' => 'Çorbalar', 'description' => 'Sıcak başlangıçlar', 'public_id' => (string) Str::ulid()]),
            'ana-yemek' => Category::firstOrCreate(['slug' => 'ana-yemek'], ['name' => 'Ana Yemekler', 'description' => 'Doyurucu ana yemekler', 'public_id' => (string) Str::ulid()]),
            'zeytinyagli-sebze' => Category::firstOrCreate(['slug' => 'zeytinyagli-sebze'], ['name' => 'Zeytinyağlılar ve Sebzeler', 'description' => 'Hafif ve sağlıklı', 'public_id' => (string) Str::ulid()]),
            'salata-meze' => Category::firstOrCreate(['slug' => 'salata-meze'], ['name' => 'Salatalar ve Mezeler', 'description' => 'Sofranın tamamlayıcıları', 'public_id' => (string) Str::ulid()]),
            'hamur-isi' => Category::firstOrCreate(['slug' => 'hamur-isi'], ['name' => 'Hamur İşleri', 'description' => 'Lezzetli hamur işleri', 'public_id' => (string) Str::ulid()]),
            'tatli' => Category::firstOrCreate(['slug' => 'tatli'], ['name' => 'Tatlılar', 'description' => 'Tatlı krizine son', 'public_id' => (string) Str::ulid()]),
            'kahvalti' => Category::firstOrCreate(['slug' => 'kahvalti'], ['name' => 'Kahvaltılıklar', 'description' => 'Güne enerjik başlangıç', 'public_id' => (string) Str::ulid()]),
        ];

        $cuisines = [
            'turk-mutfagi' => Cuisine::firstOrCreate(['slug' => 'turk-mutfagi'], ['name' => 'Türk Mutfağı', 'country' => 'Türkiye', 'public_id' => (string) Str::ulid()]),
            'kibris-mutfagi' => Cuisine::firstOrCreate(['slug' => 'kibris-mutfagi'], ['name' => 'Kıbrıs Mutfağı', 'country' => 'Kıbrıs', 'public_id' => (string) Str::ulid()]),
            'akdeniz-mutfagi' => Cuisine::firstOrCreate(['slug' => 'akdeniz-mutfagi'], ['name' => 'Akdeniz Mutfağı', 'country' => 'Akdeniz', 'public_id' => (string) Str::ulid()]),
        ];

        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;

        $recipesData = [
            [
                'title' => 'Kıbrıs Usulü Zeytinyağlı Enginar Dolması',
                'slug' => 'kibris-usulu-enginar-dolmasi',
                'excerpt' => 'Kıbrıs bahçelerinden taze çanak enginarların dereotlu ve zeytinyağlı pirinç harcıyla doldurulup pişirildiği eşsiz lezzet.',
                'description' => 'Geleneksel Kıbrıs mutfağında ilkbaharın müjdecisi olan zeytinyağlı enginar dolması, bol limon, taze nane ve sızma zeytinyağının mükemmel uyumuyla sofraların baş tacıdır.',
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 25, 'cook' => 40, 'rest' => 15, 'servings' => 4, 'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Çanak Enginar', 'qty' => 6, 'unit' => 'adet', 'canon_qty' => 600, 'canon_unit' => 'g'],
                    ['name' => 'Baldo Pirinç', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 180, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Sızma Zeytinyağı', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 100, 'canon_unit' => 'ml'],
                    ['name' => 'Taze Dereotu', 'qty' => 1, 'unit' => 'demet', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Taze Nane', 'qty' => 0.5, 'unit' => 'demet', 'canon_qty' => 25, 'canon_unit' => 'g'],
                    ['name' => 'Taze Sıkılmış Limon Suyu', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 60, 'canon_unit' => 'ml'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Enginar çanaklarını limonlu soğuk suda kararmamaları için bekletin.', 'tip' => 'Limon suyu enginarın rengini berrak ve canlı tutar.', 'duration' => 600],
                    ['instruction' => 'Geniş bir tavada ince kıyılmış soğanları zeytinyağında pembeleşene kadar kısık ateşte soteleyin. Yıkanmış pirinci ekleyip şeffaflaşana kadar kavurun.', 'tip' => null, 'duration' => 480],
                    ['instruction' => 'Kavrulan harca kıyılmış dereotu, taze nane, tuz ve şekeri ekleyin. Yarım çay bardağı sıcak su koyup suyunu çekene kadar demleyin.', 'tip' => null, 'duration' => 360],
                    ['instruction' => 'Enginar çanaklarını geniş tabanlı yayvan bir tencereye dizin. Hazırladığınız harcı çanakların içine tepeleme doldurun.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Kalan zeytinyağı, limon suyu ve 1 su bardağı sıcak suyu tencerenin kenarından gezdirin. Kapağı kapalı olarak kısık ateşte enginarlar yumuşayana kadar yaklaşık 35-40 dakika pişirin.', 'tip' => 'Oda sıcaklığına gelince buzdolabında dinlendirip soğuk servis edin.', 'duration' => 2400],
                ],
            ],
            [
                'title' => 'Gazimağusa Usulü Kıymalı Peynirli Börek',
                'slug' => 'kibris-magusa-boregi',
                'excerpt' => 'İncecik açılan el yapımı yufkaların baharatlı kıyma ve taze hellim harcıyla buluştuğu efsanevi Mağusa böreği.',
                'description' => 'Kıbrıs\'ın köklü liman kenti Gazimağusa\'nın düğün ve kutlama sofralarında nesillerdir pişirilen, kıyma ve rendelenmiş taze hellimin bir arada kullanıldığı çok özel bir tepsi böreğidir.',
                'category_id' => $categories['hamur-isi']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 35, 'cook' => 45, 'rest' => 20, 'servings' => 6, 'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Un', 'qty' => 4, 'unit' => 'su bardağı', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Dana Kıyma', 'qty' => 300, 'unit' => 'gram', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Taze Hellim', 'qty' => 200, 'unit' => 'gram', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Nane', 'qty' => 1.5, 'unit' => 'yemek kaşığı', 'canon_qty' => 8, 'canon_unit' => 'g'],
                    ['name' => 'Tereyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 60, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                    ['name' => 'Tuz ve Karabiber', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 6, 'canon_unit' => 'g'],
                    ['name' => 'Yumurta Sarısı', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 20, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Un, ılık su ve bir tutam tuzla kulak memesi kıvamında pürüzsüz bir hamur yoğurun. Üzerini nemli bezle örtüp 20 dakika dinlendirin.', 'tip' => null, 'duration' => 1200],
                    ['instruction' => 'Tavada zeytinyağında kıymayı suyunu çekene kadar kavurun. İnce doğranmış soğanları ekleyip kavurmaya devam edin. Ocaktan alıp ılınınca rendelenmiş hellim ve bol kuru nane ekleyin.', 'tip' => 'Hellim sıcakken konursa erir; ılımasını beklemek katların ayrışmasını sağlar.', 'duration' => 600],
                    ['instruction' => 'Dinlenen hamuru bezelere ayırıp ince yufkalar açın. Eritilmiş tereyağı ile yağlayıp iç harcı paylaştırarak rulo sarın ve tepsiye dizin.', 'tip' => null, 'duration' => 900],
                    ['instruction' => 'Üzerine yumurta sarısı sürüp susam serpin. Önceden ısıtılmış 190 derece fırında altın sarısı renk alıp çıtırdayana kadar pişirin.', 'tip' => null, 'duration' => 2700],
                ],
            ],
            [
                'title' => 'Hellimli ve Zeytinli Rulo Çörek',
                'slug' => 'hellimli-ve-zeytinli-rulo-corek',
                'excerpt' => 'Kıbrıs fırınlarının mis gibi kokan taze hellim, sele zeytin ve kuru nane dolgulu nefis rulo ada çöreği.',
                'description' => 'Ada kahvaltılarının ve ikindi çaylarının vazgeçilmezi olan hellimli ve zeytinli rulo çörek, yumuşacık mayalı hamuru ve geleneksel nane aromasıyla damaklarda unutulmaz bir tat bırakır.',
                'category_id' => $categories['hamur-isi']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 30, 'cook' => 35, 'rest' => 45, 'servings' => 6, 'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Un', 'qty' => 4, 'unit' => 'su bardağı', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Ilık Süt', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 200, 'canon_unit' => 'ml'],
                    ['name' => 'Kuru Maya', 'qty' => 1, 'unit' => 'paket', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Hellim Peyniri', 'qty' => 250, 'unit' => 'gram', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Çekirdeksiz Kıbrıs Sele Zeytini', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 150, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Nane', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 100, 'canon_unit' => 'ml'],
                    ['name' => 'Susam ve Çörek Otu', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Ilık süt, maya ve 1 tatlı kaşığı şekeri karıştırıp 10 dakika köpürmesini bekleyin. Un ve zeytinyağını ekleyip yumuşak bir hamur yoğurun. 45 dakika mayalandırın.', 'tip' => null, 'duration' => 2700],
                    ['instruction' => 'Hellimi rendeleyin, zeytinleri kabaca doğrayın. Bol kuru nane ile harmanlayın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Mayalanan hamuru dikdörtgen şeklinde açın. Zeytinyağı sürüp hellimli zeytinli harcı her yerine eşit yayın. Rulo sarıp dilimleyin.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Fırın tepsisine dizip 20 dakika tepsi mayası verin. Üzerine yumurta sarısı ve susam serpip 180 derece fırında 35 dakika pişirin.', 'tip' => null, 'duration' => 2100],
                ],
            ],
            [
                'title' => 'Otantik Ayrelli (Kıbrıs Yabani Kuşkonmazlı Yumurta)',
                'slug' => 'kibris-usulu-asparagas-kuskonmaz-yumurta',
                'excerpt' => 'Kıbrıs dağlarından toplanan taze yabani kuşkonmaz (ayrelli) ile zeytinyağında hazırlanan geleneksel ada kahvaltısı.',
                'description' => 'Kıbrıs doğasının en değerli armağanlarından biri olan yabani kuşkonmaz (yerel adıyla Ayrelli), saf zeytinyağında sotelenip serbest gezen köy tavuğu yumurtalarıyla buluştuğunda adanın en saf lezzetine dönüşür.',
                'category_id' => $categories['kahvalti']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 10, 'cook' => 15, 'rest' => 0, 'servings' => 2, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Taze Ayrelli (Yabani Kuşkonmaz)', 'qty' => 1, 'unit' => 'demet', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Köy Yumurtası', 'qty' => 4, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Sızma Zeytinyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                    ['name' => 'Deniz Tuzu', 'qty' => 1, 'unit' => 'çay kaşığı', 'canon_qty' => 3, 'canon_unit' => 'g'],
                    ['name' => 'Taze Çekilmiş Tane Karabiber', 'qty' => 0.5, 'unit' => 'çay kaşığı', 'canon_qty' => 2, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Ayrellilerin sert kök kısımlarını kırarak ayıklayın ve taze uç kısımlarını 3-4 cm uzunluğunda parçalara bölün.', 'tip' => 'Kuşkonmazı bıçakla kesmek yerine parmaklarınızla çıtlatarak kırmak lifli sert kısımları doğal olarak ayıklar.', 'duration' => 300],
                    ['instruction' => 'Geniş bir döküm tavada zeytinyağını ısıtın. Ayrellileri ekleyip orta ateşte rengi canlı yeşile dönüp hafif yumuşayana kadar 5-7 dakika soteleyin.', 'tip' => null, 'duration' => 420],
                    ['instruction' => 'Yumurtaları tavadaki ayrellilerin üzerine kırın. Tuz ve karabiber serpin. Sarıları hafif akışkan kalacak şekilde kapağı kapalı 3-4 dakika pişirin.', 'tip' => 'Köy ekmeği ve taze hellim dilimleriyle sıcak servis yapın.', 'duration' => 240],
                ],
            ],
            [
                'title' => 'Kıbrıs Patatesli Kolokas Kavurması',
                'slug' => 'kibris-patatesli-kolokas-kavurma',
                'excerpt' => 'Ünlü Kıbrıs patatesi ve lezzetli kolokas köklerinin zeytinyağında çıtır kavrulup kereviz yapraklarıyla taçlandırıldığı enfes yemek.',
                'description' => 'Coğrafi işaretli lezzetiyle ünlü Kıbrıs patatesi ve adanın kadim kök sebzesi kolokas, bu tarifte bir araya gelerek kış sofralarını ısıtan mükemmel bir Akdeniz kavurmasına dönüşür.',
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 20, 'cook' => 35, 'rest' => 5, 'servings' => 4, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Kolokas Kökü', 'qty' => 500, 'unit' => 'gram', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Patatesi', 'qty' => 3, 'unit' => 'adet', 'canon_qty' => 400, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Taze Kereviz Sapı ve Yaprağı', 'qty' => 1, 'unit' => 'demet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                    ['name' => 'Sızma Zeytinyağı', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 60, 'canon_unit' => 'ml'],
                    ['name' => 'Taze Limon Suyu', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 30, 'canon_unit' => 'ml'],
                    ['name' => 'Tuz ve Kimyon', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Kolokasın kabuğunu soyun ve asla su ile yıkamadan temiz bir bezle silin. Bıçakla çıtlata çıtlata ceviz büyüklüğünde doğrayın.', 'tip' => 'Kolokas yıkanırsa salyalanır; mutlaka kuru bezle silinmelidir.', 'duration' => 600],
                    ['instruction' => 'Kıbrıs patateslerini küp doğrayın. Geniş bir tavada zeytinyağında kolokas ve patatesleri altın sarısı olana kadar kavurun.', 'tip' => null, 'duration' => 900],
                    ['instruction' => 'Piyazlık doğranmış soğanları ve kıyılmış kereviz saplarını ekleyip 5 dakika daha kavurun.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Limon suyu, tuz, kimyon ve yarım bardak sıcak su ilave edin. Kapağı kapatıp sebzeler lokum gibi yumuşayana kadar kısık ateşte 15 dakika pişirin.', 'tip' => null, 'duration' => 900],
                ],
            ],
            [
                'title' => 'Girne Usulü Deniz Mahsullü Fırın Makarna',
                'slug' => 'girne-usulu-deniz-mahsulleri-makarna',
                'excerpt' => 'Karides, kalamar ve taze hellim rendesiyle harmanlanan, Akdeniz esintili nefis Girne fırın makarnası.',
                'description' => 'Girne balıkçı limanının taze deniz ürünlerini adanın meşhur fırın makarna geleneğiyle buluşturan, beşamelsiz, zeytinyağlı ve hellimli çok özel bir sahil yemeği.',
                'category_id' => $categories['ana-yemek']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 20, 'cook' => 40, 'rest' => 10, 'servings' => 6, 'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Fırın Makarna (Bucatini)', 'qty' => 1, 'unit' => 'paket', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Ayıklanmış Karides', 'qty' => 250, 'unit' => 'gram', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Halka Kalamar', 'qty' => 200, 'unit' => 'gram', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Taze Hellim', 'qty' => 250, 'unit' => 'gram', 'canon_qty' => 250, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Nane', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Sarımsak', 'qty' => 3, 'unit' => 'diş', 'canon_qty' => 15, 'canon_unit' => 'g'],
                    ['name' => 'Zeytinyağı', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 60, 'canon_unit' => 'ml'],
                    ['name' => 'Yumurta', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Makarnayı bol tuzlu suda 8-9 dakika al dente kıvamda haşlayıp süzün.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Tavada zeytinyağı ve ezilmiş sarımsakla karides ve kalamarları 3 dakika soteleyip ocaktan alın.', 'tip' => 'Deniz ürünlerini fazla pişirmeyin, fırında da pişecekleri için suyunu kaybetmesinler.', 'duration' => 240],
                    ['instruction' => 'Geniş bir kapta haşlanmış makarna, sotelenmiş deniz mahsulleri, rendelenmiş hellimin üçte ikisi, kuru nane ve çırpılmış yumurtaları harmanlayın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Yağlanmış fırın kabına makarnayı düzgünce yerleştirin. Kalan hellim rendesini üzerine serpip 190 derece fırında üzeri kızarana kadar 35 dakika fırınlayın.', 'tip' => null, 'duration' => 2100],
                ],
            ],
            [
                'title' => 'Kıbrıs Usulü Tahinli Bal Kabağı Böreği',
                'slug' => 'kibris-tahinli-kabak-boregi',
                'excerpt' => 'Tatlı bal kabağı rendesi, ada tahini ve tarçının çıtır yufkalarla buluştuğu nefis Kıbrıs tatlı böreği.',
                'description' => 'Kıbrıs köylerinde kış aylarında pişirilen geleneksel tahinli kabak böreği, bal kabağının doğal tatlılığı ve taş değirmen tahininin zengin aromasıyla benzersiz bir lezzet şöleni sunar.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 30, 'cook' => 40, 'rest' => 15, 'servings' => 6, 'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Bal Kabağı', 'qty' => 500, 'unit' => 'gram', 'canon_qty' => 500, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Taş Değirmen Tahini', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Toz Şeker', 'qty' => 0.75, 'unit' => 'su bardağı', 'canon_qty' => 150, 'canon_unit' => 'g'],
                    ['name' => 'Toz Tarçın', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Baklavalık Yufka', 'qty' => 12, 'unit' => 'adet', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Eritilmiş Tereyağı', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 60, 'canon_unit' => 'g'],
                    ['name' => 'Dövülmüş Ceviz İçi', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 100, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Bal kabağını rendeleyin, şekerle karıştırıp 15 dakika suyunu salmasını bekleyin. Fazla suyunu elinizle hafifçe sıkın.', 'tip' => null, 'duration' => 900],
                    ['instruction' => 'Kabak rendesine tahin, ceviz ve toz tarçını ekleyip homojen bir harç elde edene kadar karıştırın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'İki kat baklava yufkasını arasına tereyağı sürerek serin. Harçtan yayıp rulo şeklinde sarın ve yağlanmış tepsiye dizin.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Üzerlerine kalan tereyağını sürüp 180 derece fırında altın sarısı olana kadar 35-40 dakika pişirin. Ilık servis yapın.', 'tip' => null, 'duration' => 2400],
                ],
            ],
            [
                'title' => 'Karpaz Usulü Hellimli ve Naneli Karpuz Salatası',
                'slug' => 'karpos-karpuz-hellim-salatasi',
                'excerpt' => 'Sıcak yaz günlerinde buz gibi Karpaz karpuzu, ızgara hellim ve taze ada nanesinin ferahlatıcı dansı.',
                'description' => 'Kıbrıs yaz sofralarının klasiği olan karpuz-hellim ikilisini çağdaş Akdeniz sunumuyla buluşturan, sızma zeytinyağı ve nar ekşisiyle taçlandırılmış mükemmel bir başlangıç.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 15, 'cook' => 5, 'rest' => 0, 'servings' => 4, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Karpuz Dilimi (Çekirdeksiz)', 'qty' => 600, 'unit' => 'gram', 'canon_qty' => 600, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Hellim Peyniri', 'qty' => 200, 'unit' => 'gram', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Taze Nane Yaprakları', 'qty' => 1, 'unit' => 'avuç', 'canon_qty' => 20, 'canon_unit' => 'g'],
                    ['name' => 'Sızma Zeytinyağı', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 30, 'canon_unit' => 'ml'],
                    ['name' => 'Doğal Nar Ekşisi', 'qty' => 1, 'unit' => 'yemek kaşığı', 'canon_qty' => 15, 'canon_unit' => 'ml'],
                    ['name' => 'Kavrulmuş Çam Fıstığı', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 20, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Soğuk karpuzu küp küp doğrayıp geniş bir servis tabağına yayın.', 'tip' => 'Karpuzun dolaptan yeni çıkmış buz gibi olması lezzeti ikiye katlar.', 'duration' => 300],
                    ['instruction' => 'Hellimleri 1 cm kalınlığında dilimleyip yağsız sıcak ızgara tavada her iki yüzü çizgili kızarana kadar 2\'şer dakika pişirin.', 'tip' => null, 'duration' => 240],
                    ['instruction' => 'Sıcak ızgara hellimleri karpuzların arasına yerleştirin. Üzerine taze nane yaprakları ve çam fıstıklarını serpin.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Zeytinyağı ve nar ekşisini gezdirerek beklemeden servis yapın.', 'tip' => null, 'duration' => 120],
                ],
            ],
            [
                'title' => 'Kıbrıs Usulü Dereotlu Kuru Bakla Ezmesi (Fava)',
                'slug' => 'kibris-usulu-kuru-bakla-ezmesi',
                'excerpt' => 'Kıbrıs usulü pişirilen ipeksi kuru bakla püresi, karamelize soğan ve bol sızma zeytinyağı ile hazırlanan otantik meze.',
                'description' => 'Ada meyhanelerinin ve meze sofralarının olmazsa olmazı olan geleneksel Kıbrıs favası, tatlı sarı kuru baklanın kısık ateşte eritilip zeytinyağıyla çırpılmasıyla hazırlanır.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 15, 'cook' => 45, 'rest' => 60, 'servings' => 6, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Kuru Sarı Bakla', 'qty' => 2, 'unit' => 'su bardağı', 'canon_qty' => 350, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Soğan', 'qty' => 2, 'unit' => 'adet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Sızma Zeytinyağı', 'qty' => 0.75, 'unit' => 'su bardağı', 'canon_qty' => 150, 'canon_unit' => 'ml'],
                    ['name' => 'Taze Sıkılmış Limon Suyu', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 30, 'canon_unit' => 'ml'],
                    ['name' => 'Taze Dereotu', 'qty' => 1, 'unit' => 'demet', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Toz Şeker', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Tuz', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Baklaları yıkayıp tencereye alın. İri doğranmış 1 adet soğan, 4 su bardağı su, tuz ve şekeri ekleyip kaynamaya bırakın.', 'tip' => 'Üzerinde oluşan köpükleri kevgirle alarak favanın berrak olmasını sağlayın.', 'duration' => 600],
                    ['instruction' => 'Kısık ateşte baklalar tamamen yumuşayıp suyunu çekene kadar yaklaşık 40 dakika pişirin.', 'tip' => null, 'duration' => 2400],
                    ['instruction' => 'Zeytinyağının yarısını ve limon suyunu ekleyip el blenderı ile pürüzsüz ipeksi bir kıvam alana kadar çekin. Yayvan kaba döküp soğutun.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Kalan soğanı ince piyazlık doğrayıp zeytinyağında karamelize edin. Soğuyan favanın üzerine karamelize soğan, kıyılmış dereotu ve zeytinyağı gezdirerek servis edin.', 'tip' => null, 'duration' => 600],
                ],
            ],
            [
                'title' => 'Ada Usulü Ağır Ateşte Fırın Kuzu Kol',
                'slug' => 'ada-usulu-firin-kuzu-kol',
                'excerpt' => 'Kıbrıs kekiği, sarımsak ve defne yapraklarıyla marine edilip kemiğinden ayrılacak kadar yumuşak pişen fırın kuzu kol.',
                'description' => 'Geleneksel Kıbrıs fırın kebap kültürünün en asil temsilcisi; taze patatesler, taze kolyandro ve dağ kekiği eşliğinde saatlerce ağır ateşte pişen lokum kıvamında kuzu kol.',
                'category_id' => $categories['ana-yemek']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 20, 'cook' => 180, 'rest' => 15, 'servings' => 6, 'difficulty' => 'medium',
                'ingredients' => [
                    ['name' => 'Bütün Kuzu Kol', 'qty' => 1.5, 'unit' => 'kilogram', 'canon_qty' => 1500, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Patatesi', 'qty' => 6, 'unit' => 'adet', 'canon_qty' => 800, 'canon_unit' => 'g'],
                    ['name' => 'Sarımsak', 'qty' => 8, 'unit' => 'diş', 'canon_qty' => 40, 'canon_unit' => 'g'],
                    ['name' => 'Taze Dağ Kekiği', 'qty' => 4, 'unit' => 'dal', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Defne Yaprağı', 'qty' => 3, 'unit' => 'adet', 'canon_qty' => 2, 'canon_unit' => 'g'],
                    ['name' => 'Sızma Zeytinyağı', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 100, 'canon_unit' => 'ml'],
                    ['name' => 'Tane Kişniş (Kolyandro)', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'g'],
                    ['name' => 'Tuz ve Çekilmiş Karabiber', 'qty' => 1.5, 'unit' => 'tatlı kaşığı', 'canon_qty' => 10, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Kuzu kolun üzerine bıçakla derin çizikler atın. Sarımsak dişlerini ikiye bölüp bu yarıklara yerleştirin.', 'tip' => null, 'duration' => 600],
                    ['instruction' => 'Zeytinyağı, ezilmiş tane kolyandro, kekik, tuz ve karabiberi karıştırıp etin her yerine iyice masaj yaparak yedirin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'İri elma dilim doğranmış Kıbrıs patateslerini fırın tepsisine dizin. Üzerine marine edilmiş kuzu kolu yerleştirin. Defne yapraklarını ekleyin.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Tepsinin üzerini ıslatılmış fırın kağıdı ve alüminyum folyo ile hava almayacak şekilde sıkıca kapatın. 160 derece fırında 2.5 saat pişirin. Folyoyu açıp 200 derecede 20 dakika nar gibi kızartın.', 'tip' => 'Et kemikten kendiliğinden ayrılacak kadar yumuşayana dek sabırla pişirilmelidir.', 'duration' => 10800],
                ],
            ],
            [
                'title' => 'Kıbrıs Köy Usulü Hellimli ve Nar Ekşili Roka Salatası',
                'slug' => 'kibris-ev-yapimi-nar-eksili-roka',
                'excerpt' => 'Çıtır çıtır taze ada rokası, nar taneleri, ceviz içi ve sıcak kızartılmış hellim küpleriyle hazırlanan gurme salata.',
                'description' => 'Akdeniz\'in aromatik yeşilliği yabani roka ve Kıbrıs\'ın taze hellim peynirinin zeytinyağı-nar ekşisi sosuyla harmanlandığı leziz bir ada klasiği.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 15, 'cook' => 5, 'rest' => 0, 'servings' => 4, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Taze Roka', 'qty' => 2, 'unit' => 'demet', 'canon_qty' => 200, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Hellim Peyniri', 'qty' => 150, 'unit' => 'gram', 'canon_qty' => 150, 'canon_unit' => 'g'],
                    ['name' => 'Ayıklanmış Nar Tanesi', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 80, 'canon_unit' => 'g'],
                    ['name' => 'İri Kırılmış Ceviz İçi', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Sızma Zeytinyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                    ['name' => 'Hakiki Nar Ekşisi', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 30, 'canon_unit' => 'ml'],
                    ['name' => 'Deniz Tuzu', 'qty' => 0.5, 'unit' => 'çay kaşığı', 'canon_qty' => 2, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Rokaları yıkayıp iyice kurutun ve elinizle iri parçalara bölerek servis kasesine alın.', 'tip' => 'Bıçak vurmamak rokanın acılaşmasını engeller ve tazeliğini korur.', 'duration' => 300],
                    ['instruction' => 'Hellimi küçük küpler halinde doğrayıp yapışmaz tavada hafif zeytinyağında her yanı altın sarısı olana dek soteleyin.', 'tip' => null, 'duration' => 240],
                    ['instruction' => 'Bir kasede zeytinyağı, nar ekşisi ve tuzu homojen bir emülsiyon olana kadar çırpın.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Rokaların üzerine sıcak hellim küplerini, cevizleri ve nar tanelerini serpin. Sosu gezdirip hemen servis edin.', 'tip' => null, 'duration' => 120],
                ],
            ],
            [
                'title' => 'Akdeniz Usulü Güveçte Sarımsaklı Karides',
                'slug' => 'iskenderun-kibris-karides-guvec',
                'excerpt' => 'Toprak güveçte cızırdayan taze karidesler, sızma zeytinyağı, bol sarımsak, pul biber ve taze maydanoz.',
                'description' => 'Akdeniz sahillerinin en sevilen sıcak mezesi; taze karideslerin toprak güveçte kızgın zeytinyağı ve sarımsakla buluşup sıcacık cızırdayarak sofraya geldiği unutulmaz bir lezzet.',
                'category_id' => $categories['salata-meze']->id,
                'cuisine_id' => $cuisines['akdeniz-mutfagi']->id,
                'prep' => 10, 'cook' => 10, 'rest' => 0, 'servings' => 2, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Ayıklanmış Taze Karides', 'qty' => 300, 'unit' => 'gram', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Sarımsak', 'qty' => 5, 'unit' => 'diş', 'canon_qty' => 25, 'canon_unit' => 'g'],
                    ['name' => 'Tereyağı', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 40, 'canon_unit' => 'g'],
                    ['name' => 'Sızma Zeytinyağı', 'qty' => 3, 'unit' => 'yemek kaşığı', 'canon_qty' => 45, 'canon_unit' => 'ml'],
                    ['name' => 'Acı Pul Biber', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 4, 'canon_unit' => 'g'],
                    ['name' => 'İnce Kıyılmış Maydanoz', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Tuz', 'qty' => 0.5, 'unit' => 'çay kaşığı', 'canon_qty' => 2, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Toprak güveci ocak üzerinde kısık ateşte ısıtın. Tereyağı ve zeytinyağını ekleyip köpürmesini sağlayın.', 'tip' => null, 'duration' => 240],
                    ['instruction' => 'İnce dilimlenmiş sarımsakları ve pul biberi ekleyip sarımsakların kokusu çıkana kadar 1 dakika çevirin.', 'tip' => 'Sarımsakların yanmamasına dikkat edin.', 'duration' => 60],
                    ['instruction' => 'Karidesleri ekleyip yüksek ateşte her iki yüzünü 2-3 dakika karidesler pembeleşip kıvrılana kadar soteleyin.', 'tip' => null, 'duration' => 180],
                    ['instruction' => 'Tuz ve taze kıyılmış maydanozu ekleyin. Güveç cızırdayarak hemen masaya götürülüp ekmek banılarak yenmelidir.', 'tip' => null, 'duration' => 60],
                ],
            ],
            [
                'title' => 'Bal ve Cevizli Taze Anari Tatlısı',
                'slug' => 'kibris-yaban-mersinli-anari-tatlisi',
                'excerpt' => 'Kıbrıs\'ın taze süt peyniri anari, saf ada balı, tarçın ve taze ceviz içiyle hazırlanan hafif ve sağlıklı ada tatlısı.',
                'description' => 'Hellim üretiminin saf şirden mayasıyla elde edilen taze anari peyniri, hafifliği ve kremamsı dokusuyla Kıbrıs\'ın en kadim ve en sağlıklı tatlısıdır.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 10, 'cook' => 0, 'rest' => 10, 'servings' => 4, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Taze Kıbrıs Anari Peyniri (Tuzsuz)', 'qty' => 400, 'unit' => 'gram', 'canon_qty' => 400, 'canon_unit' => 'g'],
                    ['name' => 'Kıbrıs Çiçek veya Kekik Balı', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 80, 'canon_unit' => 'g'],
                    ['name' => 'Taze Ceviz İçi', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 60, 'canon_unit' => 'g'],
                    ['name' => 'Toz Tarçın', 'qty' => 1, 'unit' => 'çay kaşığı', 'canon_qty' => 3, 'canon_unit' => 'g'],
                    ['name' => 'Taze İncir veya Yaban Mersini', 'qty' => 4, 'unit' => 'adet', 'canon_qty' => 100, 'canon_unit' => 'g'],
                ],
                'steps' => [
                    ['instruction' => 'Taze tuzsuz anari peynirini dilimleyin veya bir çatalla hafifçe iri parçalar halinde ezin.', 'tip' => 'Anari taze ve soğuk olmalıdır.', 'duration' => 180],
                    ['instruction' => 'Peyniri porsiyonluk kaselere veya şık tatlı tabaklarına paylaştırın.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Üzerine bol miktarda Kıbrıs balı gezdirin. İri kırılmış taze ceviz içi ve toz tarçın serpin.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Taze meyvelerle süsleyip hafif bir Akdeniz tatlısı olarak soğuk servis edin.', 'tip' => null, 'duration' => 60],
                ],
            ],
            [
                'title' => 'Kıbrıs Usulü Üzüm Pekmezli Paluze',
                'slug' => 'kibris-usulu-pekmezli-paluze',
                'excerpt' => 'Taze üzüm şırası veya hakiki ada pekmeziyle pişirilen, bademli ipeksi geleneksel Kıbrıs tatlısı.',
                'description' => 'Kıbrıs köylerinin bağ bozumu şenliklerinde kazanlarda kaynatılan paluze, saf üzümün ve buğday nişastasının eşsiz buluşmasıyla hazırlanan tarihî bir tatlıdır.',
                'category_id' => $categories['tatli']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 10, 'cook' => 20, 'rest' => 60, 'servings' => 6, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Doğal Üzüm Pekmezi', 'qty' => 1.5, 'unit' => 'su bardağı', 'canon_qty' => 300, 'canon_unit' => 'ml'],
                    ['name' => 'İçme Suyu', 'qty' => 3.5, 'unit' => 'su bardağı', 'canon_qty' => 700, 'canon_unit' => 'ml'],
                    ['name' => 'Buğday Nişastası', 'qty' => 4, 'unit' => 'yemek kaşığı', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Kavrulmuş Badem İçi', 'qty' => 0.5, 'unit' => 'su bardağı', 'canon_qty' => 60, 'canon_unit' => 'g'],
                    ['name' => 'Gül Suyu', 'qty' => 1, 'unit' => 'tatlı kaşığı', 'canon_qty' => 5, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Bir tencerede su, üzüm pekmezi ve nişastayı pürüzsüz olana kadar çırpma teliyle karıştırın.', 'tip' => 'Ocağa almadan önce nişastanın soğuk sıvıda tamamen çözüldüğünden emin olun.', 'duration' => 240],
                    ['instruction' => 'Orta ateşte sürekli karıştırarak koyulaşıp göz göz kaynayana kadar yaklaşık 15 dakika pişirin.', 'tip' => null, 'duration' => 900],
                    ['instruction' => 'Kaynayınca gül suyunu ilave edin ve 2 dakika daha karıştırıp ocaktan alın.', 'tip' => null, 'duration' => 120],
                    ['instruction' => 'Kaselere paylaştırın, üzerlerini kavrulmuş bademlerle süsleyin. Ilık veya soğuk olarak servis edin.', 'tip' => null, 'duration' => 300],
                ],
            ],
            [
                'title' => 'Kıbrıs Köy Usulü Taze Naneli Hellimli Börek',
                'slug' => 'kibris-hellimli-taze-borek',
                'excerpt' => 'Çıtır yufka içinde uzayan taze hellim, kuru nane ve yumurtalı harcın muazzam uyumu.',
                'description' => 'Kıbrıs kahvaltılarının ve meze masalarının taçsız kralı; taze rendelenmiş hellim peynirinin bol nane ile harmanlanıp kızartıldığı sıcacık ada böreği.',
                'category_id' => $categories['hamur-isi']->id,
                'cuisine_id' => $cuisines['kibris-mutfagi']->id,
                'prep' => 20, 'cook' => 15, 'rest' => 10, 'servings' => 4, 'difficulty' => 'easy',
                'ingredients' => [
                    ['name' => 'Kıbrıs Taze Hellim Peyniri', 'qty' => 300, 'unit' => 'gram', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Kuru Nane', 'qty' => 2, 'unit' => 'yemek kaşığı', 'canon_qty' => 10, 'canon_unit' => 'g'],
                    ['name' => 'Yumurta', 'qty' => 1, 'unit' => 'adet', 'canon_qty' => 50, 'canon_unit' => 'g'],
                    ['name' => 'Taze Yufka', 'qty' => 3, 'unit' => 'adet', 'canon_qty' => 300, 'canon_unit' => 'g'],
                    ['name' => 'Kızartma Yağı', 'qty' => 1, 'unit' => 'su bardağı', 'canon_qty' => 200, 'canon_unit' => 'ml'],
                ],
                'steps' => [
                    ['instruction' => 'Hellimi rendeleyin. 1 yumurta ve bol kuru nane ile ezerek harmanlayın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Yufkaları üçgen dilimler halinde kesin. Geniş kenarlarına hellimli harçtan koyup kenarları içe katlayarak sigara böreği gibi sarın.', 'tip' => 'Uçlarını hafif suyla ıslatarak yapıştırın ki kızarırken açılmasın.', 'duration' => 600],
                    ['instruction' => 'Kızgın yağda böreklerin her iki tarafını altın sarısı olana kadar 3-4 dakika kızartın.', 'tip' => null, 'duration' => 300],
                    ['instruction' => 'Kağıt havlu üzerine çıkarıp fazla yağını süzdürün ve sıcak servis edin.', 'tip' => null, 'duration' => 120],
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
                'country' => 'Kıbrıs',
                'prep_minutes' => $data['prep'],
                'cook_minutes' => $data['cook'],
                'rest_minutes' => $data['rest'],
                'servings' => $data['servings'],
                'difficulty' => $data['difficulty'],
                'status' => 'published',
                'verification_status' => 'verified',
                'editorial_status' => 'approved',
                'completeness_score' => 95,
                'completeness_status' => 'complete',
                'is_featured' => true,
                'source_name' => 'House Chef Cyprus Editoryal Ekibi',
                'content_origin' => 'original',
                'reliability_score' => 98,
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
                    'title' => 'Adım ' . $position,
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
                'confidence' => 95,
                'reviewed_at' => now(),
                'is_active' => true,
            ]);

            $this->command->line("✓ 1000. Kilometre Taşına Eklendi: {$data['slug']}");
        }

        $total = Recipe::where('status', 'published')->count();
        $this->command->info("🎉 TEBRİKLER! MegaTarif1000KilometreTasiSeeder tamamlandı. Toplam yayında: {$total} tarif!");
    }
}
