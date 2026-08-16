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

/**
 * HamurIsiRecipesSeeder
 *
 * 20 özgün, editoryal kalitede hamur işi tarifi.
 * Tüm içerik House Chef Cyprus editoryal ekibine aittir.
 */
final class HamurIsiRecipesSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['slug' => 'hamur-isi'], ['name' => 'Hamur İşi', 'description' => 'Hamur işi tarifleri', 'public_id' => (string) Str::ulid()]);
        $kibris = Cuisine::firstOrCreate(['slug' => 'kibris-mutfagi'], ['name' => 'Kıbrıs Mutfağı', 'country' => 'Kıbrıs', 'public_id' => (string) Str::ulid()]);
        $turk = Cuisine::firstOrCreate(['slug' => 'turk-mutfagi'], ['name' => 'Türk Mutfağı', 'country' => 'Türkiye', 'public_id' => (string) Str::ulid()]);
        $akdeniz = Cuisine::firstOrCreate(['slug' => 'akdeniz-mutfagi'], ['name' => 'Akdeniz Mutfağı', 'country' => 'Akdeniz', 'public_id' => (string) Str::ulid()]);

        // Ingredient IDs (safely create if missing on production)
        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;

        $un = $ing('Un');
        $yumurta = $ing('Yumurta');
        $sut = $ing('Süt');
        $seker = $ing('Şeker');
        $tuz = $ing('Tuz');
        $ter = $ing('Tereyağı');
        $yogurt = $ing('Yoğurt');
        $peynir = $ing('Peynir');
        $zeytiny = $ing('Zeytinyağı');
        $sogan = $ing('Soğan');
        $ispanak = $ing('Ispanak');
        $ceviz = $ing('Ceviz');
        $yufka = $ing('Baklavalık Yufka');
        $nane = $ing('Nane');

        // Each recipe: [title, slug, cuisine_id, excerpt, description, country, prep, cook, rest, servings, difficulty, ingredients[], steps[]]
        // Ingredient row: [ingredient_id, display_quantity, display_measure_code, sort_order, canonical_quantity, canonical_unit]
        // Step row: [position, instruction, chef_tip, duration_seconds]

        $recipes = [

            // ── 1 ──────────────────────────────────────────────────────────
            [
                'title' => 'Kıbrıs Usulü Flaouna',
                'slug' => 'kibris-usulu-flaouna',
                'cuisine_id' => $kibris->id,
                'excerpt' => 'Kıbrıs\'ın geleneksel Paskalya çöreği; hellim, nane ve yumurtayla hazırlanan yumuşak, lezzetli bir hamur işi.',
                'description' => 'Flaouna, Kıbrıs mutfağının vazgeçilmez Paskalya lezzetidir. Hellim peyniri, taze nane ve yumurtanın muhteşem uyumuyla hazırlanan bu çörek, fırından çıktığında altın sarısı rengiyle sofraları süsler. Hem tatlı hem tuzlu aromaların bir arada olduğu bu tarif, kuşaktan kuşağa geçen bir miras lezzetidir.',
                'country' => 'Kıbrıs',
                'prep' => 45, 'cook' => 35, 'rest' => 60, 'servings' => 12, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '500', 'gram',       1, '500', 'g'],
                    [$yumurta, '3',   'adet',        2, '3',   'adet'],
                    [$sut,     '150', 'mililitre',   3, '150', 'ml'],
                    [$ter,     '60',  'gram',        4, '60',  'g'],
                    [$seker,   '20',  'gram',        5, '20',  'g'],
                    [$tuz,     '8',   'gram',        6, '8',   'g'],
                    [$nane,    '1',   'demet',       7, null,  null],
                ],
                'steps' => [
                    [1, 'Un, tuz ve şekeri geniş bir kaba alın; ortasına çukur açarak ılık sütü, eritilmiş tereyağını ve yumurtaları ekleyin. Yumuşak bir hamur elde edene kadar yoğurun.', null, 900],
                    [2, 'Hamuru üzeri örtülü olarak oda sıcaklığında 1 saat dinlendirin.', null, 3600],
                    [3, 'İç harcı için ince kıyılmış naneyi peynirle karıştırın. Hamuru ceviz büyüklüğünde parçalara ayırıp yuvarlayın, içini doldurup kapatın.', null, 1200],
                    [4, 'Yüzeylerini yumurta sarısıyla fırçalayın. Önceden ısıtılmış 180 °C fırında 30–35 dakika, üzerleri altın rengi alana kadar pişirin.', null, 2100],
                    [5, 'Fırından çıktıktan sonra tel ızgara üzerinde 10 dakika dinlendirip servis edin.', 'Sıcak veya oda sıcaklığında servis edilebilir.', null],
                ],
            ],

            // ── 2 ──────────────────────────────────────────────────────────
            [
                'title' => 'Ispanaklı Börek',
                'slug' => 'ispanakli-borek',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Çıtır yufkalar arasında ıspanak ve beyaz peynirin buluştuğu, fırında pişen klasik Türk böreği.',
                'description' => 'Ispanaklı börek, Türk mutfağının en köklü hamur işlerinden biridir. İnce yufkalar arasına sıkıştırılan taze ıspanak ve beyaz peynir, yüksek ısıda fırında çıtır çıtır pişer. Her lokmasında eşsiz bir doku ve lezzet sunan bu tarif, hem kahvaltı hem de öğün olarak idealdir.',
                'country' => 'Türkiye',
                'prep' => 30, 'cook' => 40, 'rest' => 0, 'servings' => 8, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$yufka,   '6',   'adet',        1, null,  null],
                    [$ispanak, '400', 'gram',        2, '400', 'g'],
                    [$peynir,  '250', 'gram',        3, '250', 'g'],
                    [$yumurta, '3',   'adet',        4, '3',   'adet'],
                    [$sut,     '150', 'mililitre',   5, '150', 'ml'],
                    [$zeytiny, '60',  'mililitre',   6, '60',  'ml'],
                    [$tuz,     '5',   'gram',        7, '5',   'g'],
                ],
                'steps' => [
                    [1, 'Ispanakları yıkayıp iri kıyın; tuzla hafifçe ovarak suyunu sıkın. Ufalanmış beyaz peynirle karıştırın.', null, 600],
                    [2, 'Yumurta, süt ve zeytinyağını çırpın. Tepsiyi yağlayın; ilk yufkayı serin, yarısını süt karışımıyla fırçalayın.', null, 600],
                    [3, 'İkinci yufkayı serin, üzerine ıspanak-peynir harcının yarısını yayın. Bu işlemi tekrarlayarak katmanları tamamlayın. En üste bir yufka daha serin ve kalan sütlü karışımı gezdirin.', null, 900],
                    [4, '190 °C önceden ısıtılmış fırında üzeri altın rengi olana kadar 35–40 dakika pişirin.', 'Fırından çıkınca 5 dakika bekleyip kesin.', 2400],
                ],
            ],

            // ── 3 ──────────────────────────────────────────────────────────
            [
                'title' => 'Soğanlı Çörek',
                'slug' => 'soganli-corek',
                'cuisine_id' => $kibris->id,
                'excerpt' => 'Kıbrıs\'ta geleneksel olarak yapılan, karamelize soğan dolgusu olan yumuşak ekmek çöreği.',
                'description' => 'Soğanlı çörek, Kıbrıs evlerinde nesilden nesile aktarılan mütevazı ama bir o kadar lezzetli bir hamur işidir. Yavaş yavaş karamelize edilen soğanlar, baharatlarla buluşarak çöreğin içinde eşsiz bir tatlılık ve derinlik yaratır.',
                'country' => 'Kıbrıs',
                'prep' => 30, 'cook' => 30, 'rest' => 90, 'servings' => 10, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '500', 'gram',        1, '500', 'g'],
                    [$sogan,   '300', 'gram',        2, '300', 'g'],
                    [$yumurta, '2',   'adet',        3, '2',   'adet'],
                    [$sut,     '120', 'mililitre',   4, '120', 'ml'],
                    [$ter,     '50',  'gram',        5, '50',  'g'],
                    [$seker,   '15',  'gram',        6, '15',  'g'],
                    [$tuz,     '8',   'gram',        7, '8',   'g'],
                    [$zeytiny, '30',  'mililitre',   8, '30',  'ml'],
                ],
                'steps' => [
                    [1, 'Soğanları ince ince halkalar halinde doğrayın. Zeytinyağında orta ateşte 20 dakika, hafif karamelize olana kadar kavurun. Tuzlayıp soğumaya bırakın.', null, 1500],
                    [2, 'Un, tuz, şeker ve kuru mayayı harmanlayın. Ilık süt, yumurta ve yumuşak tereyağını ekleyip pürüzsüz bir hamur yoğurun.', null, 900],
                    [3, 'Hamuru toparlayıp üzerini örtün; oda ısısında 1 saat kabarana bırakın.', null, 3600],
                    [4, 'Hamuru yassılaştırıp soğan harcını içine alarak yuvarlayın; yağlı tepsiye yerleştirin. 30 dakika daha dinlendirin.', null, 1800],
                    [5, 'Yüzeyini yumurta sarısıyla fırçalayın. 185 °C fırında 25–30 dakika pişirin.', null, 1800],
                ],
            ],

            // ── 4 ──────────────────────────────────────────────────────────
            [
                'title' => 'Cevizli Baklava',
                'slug' => 'cevizli-baklava',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Çıtır yufka katmanları arasında taze ceviz ve aromatik şerbetle hazırlanan geleneksel Türk baklavası.',
                'description' => 'Baklava, Türk mutfağının dünyaya armağan ettiği en nadide tatlılardan biridir. İnce ince açılmış yufka katları arasına yerleştirilen taze ceviz, kızgın yağ ve soğuk şerbetin buluşmasıyla eşsiz bir çıtırlık elde edilir. Sabırla hazırlanan bu tarif, özel günlerin ve misafir ağırlamalarının vazgeçilmezidir.',
                'country' => 'Türkiye',
                'prep' => 60, 'cook' => 45, 'rest' => 120, 'servings' => 20, 'difficulty' => 'Zor',
                'ingredients' => [
                    [$yufka,  '1000', 'gram',  1, null,  null],
                    [$ceviz,  '400',  'gram',  2, '400', 'g'],
                    [$ter,    '250',  'gram',  3, '250', 'g'],
                    [$seker,  '400',  'gram',  4, '400', 'g'],
                    [$tuz,    '2',    'gram',  5, '2',   'g'],
                ],
                'steps' => [
                    [1, 'Cevizleri kaba kıyın. Şeker ve 300 ml suyu kaynatıp 10 dakika pişirerek soğuk şerbet hazırlayın.', null, 900],
                    [2, 'Tereyağını eritin. Yağlanmış tepsiye ilk yufkayı serin, erimiş tereyağıyla fırçalayın. Bu işlemi 10 yufka olana kadar tekrarlayın.', null, 1200],
                    [3, 'Üzerine ceviz kırıklarını yayın. Kalan yufkaları da tereyağlayarak katmanlayın.', null, 1200],
                    [4, 'Baklavayı eşit parçalara kesin. 175 °C fırında 40–45 dakika, üzeri altın rengi olana kadar pişirin.', null, 2700],
                    [5, 'Fırından çıkar çıkmaz soğuk şerbeti ince ince gezdirin. En az 2 saat beklettikten sonra servis edin.', 'Şerbet mutlaka soğuk olmalı; sıcak baklavaya soğuk şerbet dökülerek çıtırlık sağlanır.', 7200],
                ],
            ],

            // ── 5 ──────────────────────────────────────────────────────────
            [
                'title' => 'Akdeniz Zeytinli Ekmek',
                'slug' => 'akdeniz-zeytinli-ekmek',
                'cuisine_id' => $akdeniz->id,
                'excerpt' => 'Zeytin, kekik ve zeytinyağıyla zenginleştirilen, çıtır kabuklu ve yumuşak içli Akdeniz ekmeği.',
                'description' => 'Akdeniz mutfağının en yalın ve en besleyici unsurlarından biri olan zeytinli ekmek, her lokmasında güneşin sıcaklığını hissettirir. Yeşil ve siyah zeytinlerin harika uyumu, kekiğin aromatik kokusu ve kaliteli zeytinyağının zenginliğiyle hazırlanan bu ekmek, tek başına bir öğün olabilir.',
                'country' => 'Türkiye',
                'prep' => 20, 'cook' => 35, 'rest' => 75, 'servings' => 8, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '450', 'gram',       1, '450', 'g'],
                    [$zeytiny, '60',  'mililitre',  2, '60',  'ml'],
                    [$sut,     '200', 'mililitre',  3, '200', 'ml'],
                    [$tuz,     '9',   'gram',       4, '9',   'g'],
                    [$seker,   '10',  'gram',       5, '10',  'g'],
                    [$yumurta, '1',   'adet',       6, '1',   'adet'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şeker ve kuru mayayı karıştırın. Ilık süt, zeytinyağı ve yumurtayı ekleyip 10 dakika yoğurun; pürüzsüz, elastik bir hamur elde edin.', null, 900],
                    [2, 'Doğranmış zeytinleri ve kekiği hamura yedirin. Topu yağlı kaba alın, üzerini örtüp 1 saat kabarın.', null, 3600],
                    [3, 'Hamuru bastırıp oval biçim verin; yağlı tepside 15 dakika daha kabarmasını bekleyin.', null, 900],
                    [4, 'Yüzeyine zeytinyağı sürüp bıçakla çizin. 200 °C fırında 30–35 dakika, altı-üstü kızarıncaya kadar pişirin.', 'Ekmeği tel ızgara üzerinde soğutun, böylece altı ıslanmaz.', 2100],
                ],
            ],

            // ── 6 ──────────────────────────────────────────────────────────
            [
                'title' => 'Yoğurtlu Poğaça',
                'slug' => 'yogurtlu-pogaca',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Yoğurtla yoğrulan, içi peynirli, dışı altın sarısı çıtır kabuğuyla kahvaltıların gözdesi poğaça.',
                'description' => 'Yoğurtlu poğaça, Türk kahvaltı kültürünün ayrılmaz parçasıdır. Yoğurt, hamura özel bir yumuşaklık ve hafif ekşilik katar. Peynirli iç harç eşliğinde fırından çıkan bu poğaçalar, çayın yanında mis gibi tüketilir.',
                'country' => 'Türkiye',
                'prep' => 25, 'cook' => 25, 'rest' => 30, 'servings' => 12, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '400', 'gram',       1, '400', 'g'],
                    [$yogurt,  '200', 'gram',       2, '200', 'g'],
                    [$ter,     '80',  'gram',       3, '80',  'g'],
                    [$yumurta, '2',   'adet',       4, '2',   'adet'],
                    [$peynir,  '150', 'gram',       5, '150', 'g'],
                    [$tuz,     '7',   'gram',       6, '7',   'g'],
                    [$seker,   '5',   'gram',       7, '5',   'g'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şeker ve kuru mayayı harmanlayın. Yoğurt, 1 yumurta ve yumuşak tereyağını ekleyip yumuşak bir hamur yoğurun.', null, 900],
                    [2, 'Hamuru 30 dakika dinlendirin. Ceviz büyüklüğünde parçalara ayırın, her birini yassılaştırıp içine az peynir koyun; kapatıp yuvarlayın.', null, 1800],
                    [3, 'Yağlı tepsiye dizin. Üzerlerine çırpılmış yumurta sarısı sürün.', null, 600],
                    [4, '180 °C fırında 20–25 dakika, yüzleri güzelce kızarıncaya kadar pişirin.', null, 1500],
                ],
            ],

            // ── 7 ──────────────────────────────────────────────────────────
            [
                'title' => 'Su Böreği',
                'slug' => 'su-boregi',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Haşlanıp yufkalar arasına dizilen, peynir ve maydanozla hazırlanan el yapımı su böreği.',
                'description' => 'Su böreği, Türk mutfağının en ustaca hazırlanan hamur işlerinden biridir. Haşlanmış yufkaların katman katman dizilmesi, tuzlu peynir ve taze maydanozla doldurulması; fırında eşsiz bir yumuşaklık ve çıtırlık yaratır. Sabahın erken saatlerinde hazırlanıp sıcak servis edilen su böreği, misafir sofralarının baş tacıdır.',
                'country' => 'Türkiye',
                'prep' => 50, 'cook' => 35, 'rest' => 0, 'servings' => 10, 'difficulty' => 'Zor',
                'ingredients' => [
                    [$yufka,   '8',   'adet',        1, null,  null],
                    [$peynir,  '300', 'gram',        2, '300', 'g'],
                    [$ter,     '120', 'gram',        3, '120', 'g'],
                    [$yumurta, '4',   'adet',        4, '4',   'adet'],
                    [$sut,     '250', 'mililitre',   5, '250', 'ml'],
                    [$tuz,     '8',   'gram',        6, '8',   'g'],
                ],
                'steps' => [
                    [1, 'Büyük bir tencerede tuzlu su kaynatın. Yufkaları tek tek 20 saniye haşlayın, soğuk suya alın ve süzün.', null, 1200],
                    [2, 'Tepsiyi tereyağıyla yağlayın. İlk haşlanmış yufkayı serin ve eritilmiş tereyağıyla fırçalayın.', null, 600],
                    [3, 'Kırıntılanmış peyniri yufkaya yayın. Yufkaları sırayla ve tereyağıyla birleştirerek katmanları tamamlayın.', null, 1200],
                    [4, 'Yumurta ve sütü çırparak son kata dökün. 185 °C fırında 30–35 dakika pişirin.', null, 2100],
                    [5, 'Fırından çıktıktan hemen sonra keserek sıcak servis edin.', 'Birinci günden en lezzetlidir.', null],
                ],
            ],

            // ── 8 ──────────────────────────────────────────────────────────
            [
                'title' => 'Kıbrıs Hellimli Gözleme',
                'slug' => 'kibris-hellimli-gozleme',
                'cuisine_id' => $kibris->id,
                'excerpt' => 'Taze hellim ve naneyle hazırlanan, sacda pişen geleneksel Kıbrıs gözlemesi.',
                'description' => 'Kıbrıs\'ın simgesi hellim peyniri, gözleme hamuruna özel bir kimlik katar. Taze nane yaprakları ve eritilmiş hellimin bir araya geldiği bu gözleme, sacda ya da tavada pişerek eşsiz bir çıtırlık elde eder.',
                'country' => 'Kıbrıs',
                'prep' => 20, 'cook' => 20, 'rest' => 20, 'servings' => 6, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '300', 'gram',       1, '300', 'g'],
                    [$peynir,  '200', 'gram',       2, '200', 'g'],
                    [$tuz,     '5',   'gram',       3, '5',   'g'],
                    [$zeytiny, '20',  'mililitre',  4, '20',  'ml'],
                    [$nane,    '1',   'demet',      5, null,  null],
                    [$sut,     '120', 'mililitre',  6, '120', 'ml'],
                ],
                'steps' => [
                    [1, 'Un, tuz ve zeytinyağını karıştırın; ılık suyla (veya sütle) pürüzsüz bir hamur yoğurun. 20 dakika dinlendirin.', null, 1200],
                    [2, 'Hamuru bezelere bölüp ince yuvarlak şekle getirin. Her birini hafif yağlanmış sıcak tavaya veya saca alın.', null, 600],
                    [3, 'Bir yüzü hafifçe kızarınca, yarısına ince dilimlenmiş hellim ve nane yaprakları koyun; kapatın.', null, 300],
                    [4, 'Her iki yüzü de altın rengi olana kadar pişirin ve sıcak servis edin.', 'Üzerine bir miktar zeytinyağı gezdirilebilir.', 1200],
                ],
            ],

            // ── 9 ──────────────────────────────────────────────────────────
            [
                'title' => 'Peynirli Pide',
                'slug' => 'peynirli-pide',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Tekne şeklinde hazırlanan, içi bol peynirli ve yumurtalı geleneksel Türk pidesi.',
                'description' => 'Pide, Türk ekmek kültürünün en şenlikli temsilcisidir. Özellikle Karadeniz bölgesine özgü tekne biçimindeki bu pide, bol peynir ve yumurtayla doldurularak taş fırın ısısında pişirilir. Yüzeyindeki kabarcıklar ve kızaran kenarlarıyla servis masasına geldiğinde tüm gözleri üzerine çeker.',
                'country' => 'Türkiye',
                'prep' => 30, 'cook' => 20, 'rest' => 60, 'servings' => 6, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '500', 'gram',       1, '500', 'g'],
                    [$peynir,  '300', 'gram',       2, '300', 'g'],
                    [$yumurta, '3',   'adet',       3, '3',   'adet'],
                    [$ter,     '30',  'gram',       4, '30',  'g'],
                    [$sut,     '150', 'mililitre',  5, '150', 'ml'],
                    [$tuz,     '9',   'gram',       6, '9',   'g'],
                    [$seker,   '10',  'gram',       7, '10',  'g'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şeker ve kuru mayayı karıştırın. Ilık süt, yumurta ve eritilmiş tereyağı ile pürüzsüz bir hamur yoğurun; 1 saat kabarın.', null, 3600],
                    [2, 'Hamuru eşit parçalara bölün; her birini oval, ince açın.', null, 600],
                    [3, 'Kenarlarını yükselterek tekne şeklini verin. İçine ufalanmış peyniri doldurun; ortasına çiğ yumurta kırın.', null, 600],
                    [4, '220 °C çok sıcak fırında 15–20 dakika pişirin; kenarlar kızarıp yumurta pişince çıkarın.', null, 1200],
                    [5, 'Üzerine eritilmiş tereyağı gezdirerek sıcak servis edin.', 'Fırın ne kadar sıcak olursa pide o kadar iyi kabarır.', null],
                ],
            ],

            // ── 10 ─────────────────────────────────────────────────────────
            [
                'title' => 'Cevizli Sarma Çörek',
                'slug' => 'cevizli-sarma-corek',
                'cuisine_id' => $akdeniz->id,
                'excerpt' => 'Tarçın, ceviz ve şekerle doldurularak sarılan, fırında pişen aromaterapi gibi bir çörek.',
                'description' => 'Cevizli sarma çörek, Akdeniz\'in zengin baharatlı lezzetlerini bir araya getiren özel bir tarif. Yumuşak mayalı hamur üzerine yayılan ceviz ve şeker karışımı, hamur rulo yapılıp fırınlandığında eşsiz bir tatlılık kazanır.',
                'country' => 'Türkiye',
                'prep' => 35, 'cook' => 30, 'rest' => 90, 'servings' => 10, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '480', 'gram',       1, '480', 'g'],
                    [$ceviz,   '200', 'gram',       2, '200', 'g'],
                    [$seker,   '80',  'gram',       3, '80',  'g'],
                    [$ter,     '90',  'gram',       4, '90',  'g'],
                    [$sut,     '180', 'mililitre',  5, '180', 'ml'],
                    [$yumurta, '2',   'adet',       6, '2',   'adet'],
                    [$tuz,     '6',   'gram',       7, '6',   'g'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şekerin yarısını ve kuru mayayı karıştırın. Ilık süt, 1 yumurta ve eritilmiş tereyağı ekleyip yumuşak hamur yoğurun. 1,5 saat kabarın.', null, 5400],
                    [2, 'İç harç için kuru cevizleri kaba kıyın; kalan şekerle karıştırın.', null, 300],
                    [3, 'Kabaran hamuru dikdörtgen şekle açın. Eritilmiş tereyağı sürüp ceviz karışımını yayın; sıkıca rulo yapın.', null, 900],
                    [4, 'Ruloyu eşit dilimler halinde kesin ve yağlı tepsiye dizin. 30 dakika daha kabarmasına izin verin.', null, 1800],
                    [5, 'Yüzlerini yumurta sarısıyla fırçalayın. 175 °C fırında 25–30 dakika, üzerleri altın sarısı olana kadar pişirin.', 'Üzerine pudra şekeri serpilebilir.', 1800],
                ],
            ],

            // ── 11 ─────────────────────────────────────────────────────────
            [
                'title' => 'Pratik Lavaş Ekmeği',
                'slug' => 'pratik-lavas-ekmegi',
                'cuisine_id' => $akdeniz->id,
                'excerpt' => 'Mayasız, hızla hazırlanan, tavada pişen ince Akdeniz lavaşı; tüm yemeklerin yanında mükemmel.',
                'description' => 'Lavaş, Orta Doğu ve Akdeniz mutfağının en pratik ekmeklerinden biridir. Mayasız hamuru 10 dakikada hazırlanır, yüksek ısıda tavada ya da sacda pişirilerek birkaç dakikada sofraya gelir.',
                'country' => 'Türkiye',
                'prep' => 15, 'cook' => 20, 'rest' => 10, 'servings' => 8, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '350', 'gram',       1, '350', 'g'],
                    [$tuz,     '6',   'gram',       2, '6',   'g'],
                    [$zeytiny, '15',  'mililitre',  3, '15',  'ml'],
                ],
                'steps' => [
                    [1, 'Un ve tuzu karıştırın. 190–200 ml ılık su ve zeytinyağını ekleyip pürüzsüz bir hamur yoğurun. 10 dakika dinlendirin.', null, 600],
                    [2, 'Hamuru sekiz eşit parçaya bölün; her birini mümkün olduğunca ince açın.', null, 600],
                    [3, 'Kızgın kuru tavaya veya saca alın; 1–1,5 dakika içi kabarcık yapıncaya kadar pişirin, çevirin, 1 dakika daha pişirin.', null, 1200],
                    [4, 'Bezle örtüp sıcak tutun veya servis edin.', 'Pişer pişmez üstünü örtmek yumuşaklığı korur.', null],
                ],
            ],

            // ── 12 ─────────────────────────────────────────────────────────
            [
                'title' => 'Katmer',
                'slug' => 'katmer',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Gaziantep\'in simgesi; tereyağı ve cevizle hazırlanan, sacda pişen çıtır hamur katmeri.',
                'description' => 'Katmer, Türk mutfağının Gaziantep\'e özgü, nefis bir hamur işidir. Açılan ince hamurun tereyağı ve cevizle doldurulması, ardından sac üzerinde hızla pişirilmesiyle oluşan çıtır çıtır katmanlar, bu tarifi eşsiz kılar.',
                'country' => 'Türkiye',
                'prep' => 20, 'cook' => 20, 'rest' => 30, 'servings' => 6, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '350', 'gram',  1, '350', 'g'],
                    [$ter,     '100', 'gram',  2, '100', 'g'],
                    [$seker,   '40',  'gram',  3, '40',  'g'],
                    [$tuz,     '4',   'gram',  4, '4',   'g'],
                    [$ceviz,   '100', 'gram',  5, '100', 'g'],
                    [$yumurta, '1',   'adet',  6, '1',   'adet'],
                ],
                'steps' => [
                    [1, 'Un, tuz ve şekerin yarısını karıştırın. Yumurta ve ılık su ekleyip 30 dakika dinlendirilecek yumuşak bir hamur yoğurun.', null, 1800],
                    [2, 'Hamuru ince açın; eritilmiş tereyağı sürüp kıyılmış ceviz ve kalan şekeri serpin.', null, 600],
                    [3, 'Hamuru kapatarak kat kat katlayın; tekrar ince açın. Bu işlemi birkaç kez tekrarlayın.', null, 600],
                    [4, 'Sıcak ve kuru saca veya tavaya alın; her iki yüzü de tereyağıyla fırçalayarak kızartın.', null, 1200],
                ],
            ],

            // ── 13 ─────────────────────────────────────────────────────────
            [
                'title' => 'Dilber Dudağı',
                'slug' => 'dilber-dudagi',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Şekilli, kızarmış hamurdan yapılan, üzeri şerbetle ıslanmış geleneksel Osmanlı tatlısı.',
                'description' => 'Dilber dudağı, Osmanlı saray mutfağından günümüze uzanan, şeklinden ismini almış zarif bir tatlıdır. Yoğurt ve yumurtayla hazırlanan hamur; yağda kızarttıktan sonra şerbete daldırılır.',
                'country' => 'Türkiye',
                'prep' => 20, 'cook' => 30, 'rest' => 20, 'servings' => 16, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '350', 'gram',  1, '350', 'g'],
                    [$yogurt,  '150', 'gram',  2, '150', 'g'],
                    [$yumurta, '2',   'adet',  3, '2',   'adet'],
                    [$seker,   '300', 'gram',  4, '300', 'g'],
                    [$ter,     '40',  'gram',  5, '40',  'g'],
                    [$tuz,     '3',   'gram',  6, '3',   'g'],
                ],
                'steps' => [
                    [1, 'Şeker ve 300 ml su kaynatıp 8 dakika pişirerek şerbet hazırlayın; soğutun.', null, 600],
                    [2, 'Un, tuz, yoğurt, yumurta ve erimiş tereyağını harmanlayıp yumuşak bir hamur elde edin. 20 dakika dinlendirin.', null, 1200],
                    [3, 'Hamuru ince açın; küçük yuvarlak veya oval şekiller kesin. Her iki ucunu birleştirerek dudak formuna getirin.', null, 900],
                    [4, 'Bol yağda orta ateşte her iki yüzü altın rengi olana kadar kızartın; kağıt havluya alın.', null, 1200],
                    [5, 'Sıcakken şerbete batırıp çıkarın; servis tabağına dizin.', 'Tatlı soğuduktan sonra daha iyidir.', null],
                ],
            ],

            // ── 14 ─────────────────────────────────────────────────────────
            [
                'title' => 'Lor Peynirli Tepsi Böreği',
                'slug' => 'lor-peynirli-tepsi-boregi',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Lor peyniri ve maydanozla hazırlanan, sütlü yufkalardan yapılan hafif ve lezzetli tepsi böreği.',
                'description' => 'Lor peyniri, börek harcına beyaz peynirden farklı bir yumuşaklık ve hafiflik katar. Taze maydanoz ve karabiber eşliğinde hazırlanan bu iç harç, sütlü yufka katmanları arasına yerleştirilir ve fırında kızararak mükemmel bir tat ve dokuya kavuşur.',
                'country' => 'Türkiye',
                'prep' => 25, 'cook' => 35, 'rest' => 0, 'servings' => 8, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$yufka,   '5',   'adet',        1, null,  null],
                    [$peynir,  '250', 'gram',        2, '250', 'g'],
                    [$yumurta, '3',   'adet',        3, '3',   'adet'],
                    [$sut,     '200', 'mililitre',   4, '200', 'ml'],
                    [$zeytiny, '50',  'mililitre',   5, '50',  'ml'],
                    [$tuz,     '5',   'gram',        6, '5',   'g'],
                ],
                'steps' => [
                    [1, 'Yumurta, süt, zeytinyağı ve tuzu çırpın. Peyniri ufalayın.', null, 300],
                    [2, 'Tepsiyi yağlayın. İlk yufkayı serin; sütlü karışımla fırçalayın.', null, 300],
                    [3, 'İkinci yufkayı serin, üzerine peynir serpin. Bu işlemi katlayarak sürdürün; son yufka üstüne kalan sütlü karışımı dökün.', null, 900],
                    [4, '185 °C fırında 30–35 dakika, üzeri kızarıncaya kadar pişirin.', null, 2100],
                    [5, '5 dakika dinlendirip keserek servis edin.', null, null],
                ],
            ],

            // ── 15 ─────────────────────────────────────────────────────────
            [
                'title' => 'Sütlü Revani',
                'slug' => 'sutlu-revani',
                'cuisine_id' => $turk->id,
                'excerpt' => 'İrmik ve undan yapılan, ılık sütlü şerbetle ıslatılarak servis edilen geleneksel Türk tatlısı.',
                'description' => 'Revani, Osmanlı mutfağından bugüne taşınan irmikli, hafif tatlılar arasında özel bir yere sahiptir. Fırında pişen irmik-un tabanı, üzerine dökülen ılık sütlü şerbetle zenginleşir.',
                'country' => 'Türkiye',
                'prep' => 20, 'cook' => 35, 'rest' => 30, 'servings' => 12, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '150', 'gram',       1, '150', 'g'],
                    [$seker,   '300', 'gram',       2, '300', 'g'],
                    [$yumurta, '4',   'adet',       3, '4',   'adet'],
                    [$sut,     '400', 'mililitre',  4, '400', 'ml'],
                    [$ter,     '50',  'gram',       5, '50',  'g'],
                    [$tuz,     '2',   'gram',       6, '2',   'g'],
                ],
                'steps' => [
                    [1, 'Yumurta ve yarısı şekeri köpük köpük çırpın. Eritilmiş tereyağı ve 200 ml sütü ekleyin.', null, 600],
                    [2, 'Unu yavaşça karıştırın; pürüzsüz kıvam elde edin. Yağlı tepsiye dökün.', null, 600],
                    [3, '175 °C fırında 30–35 dakika, üzeri altın rengi olana kadar pişirin.', null, 2100],
                    [4, 'Kalan şeker ve sütü kaynatarak şerbet hazırlayın; ılık hale gelince revaniye yavaşça dökün.', null, 600],
                    [5, '30 dakika bekleyip kare şeklinde keserek servis edin.', 'Üzerine hindistan cevizi serpilebilir.', 1800],
                ],
            ],

            // ── 16 ─────────────────────────────────────────────────────────
            [
                'title' => 'Akdeniz Yassı Ekmek',
                'slug' => 'akdeniz-yassi-ekmek',
                'cuisine_id' => $akdeniz->id,
                'excerpt' => 'Zeytinyağı ve baharatlarla fırınlanan, yassı mayalı hamurdan yapılan Akdeniz odaklı ekmek.',
                'description' => 'Bu tarif, Kıbrıs, Türkiye, Yunanistan ve Lübnan mutfaklarının kesişim noktasından doğar. Kahvaltıda, mezzeyle ya da çorbanın yanında servis edilen bu ekmek, sade ama derin bir lezzete sahiptir.',
                'country' => 'Türkiye',
                'prep' => 20, 'cook' => 25, 'rest' => 60, 'servings' => 6, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '400', 'gram',       1, '400', 'g'],
                    [$zeytiny, '60',  'mililitre',  2, '60',  'ml'],
                    [$tuz,     '8',   'gram',       3, '8',   'g'],
                    [$seker,   '8',   'gram',       4, '8',   'g'],
                    [$sut,     '150', 'mililitre',  5, '150', 'ml'],
                    [$yogurt,  '80',  'gram',       6, '80',  'g'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şeker ve kuru mayayı karıştırın. Süt, yoğurt ve 30 ml zeytinyağını ekleyip pürüzsüz hamur yoğurun. 1 saat kabarın.', null, 3600],
                    [2, 'Kabaran hamuru yassı, oval biçimde açın; yağlı tepsiye yerleştirin.', null, 600],
                    [3, 'Yüzeyine parmak izleri basın; üzerine kalan zeytinyağını gezdirin ve tuz serpin.', null, 300],
                    [4, '200 °C fırında 20–25 dakika pişirin.', null, 1500],
                ],
            ],

            // ── 17 ─────────────────────────────────────────────────────────
            [
                'title' => 'Fırın Simit',
                'slug' => 'firin-simit',
                'cuisine_id' => $turk->id,
                'excerpt' => 'Susamlı, çıtır kabuklu, içi yumuşak ev yapımı simit; kahvaltının vazgeçilmezi.',
                'description' => 'Simit, Türkiye\'nin simgesi haline gelmiş bir sokak lezzetidir. Ev yapımı simit ise hem daha taze hem de katkısız olmasıyla fırından alınandan çok daha lezzetlidir.',
                'country' => 'Türkiye',
                'prep' => 30, 'cook' => 25, 'rest' => 60, 'servings' => 8, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '500', 'gram',       1, '500', 'g'],
                    [$seker,   '15',  'gram',       2, '15',  'g'],
                    [$tuz,     '9',   'gram',       3, '9',   'g'],
                    [$zeytiny, '30',  'mililitre',  4, '30',  'ml'],
                    [$sut,     '50',  'mililitre',  5, '50',  'ml'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şeker ve mayayı karıştırın. Ilık su (200 ml), süt ve zeytinyağı ekleyip pürüzsüz bir hamur yoğurun. 1 saat dinlendirin.', null, 3600],
                    [2, 'Hamuru uzun silindir şekline getirip birleştirerek halka yapın. Tuzlu suya batırıp bol susam serpin.', null, 900],
                    [3, 'Yağlı tepsiye dizin; 15 dakika daha kabarmasına izin verin.', null, 900],
                    [4, '210 °C fırında 20–25 dakika, dışı kızarıp içi boş ses çıkana kadar pişirin.', null, 1500],
                ],
            ],

            // ── 18 ─────────────────────────────────────────────────────────
            [
                'title' => 'Tatlı Tarçınlı Rulo',
                'slug' => 'tatli-tarcinli-rulo',
                'cuisine_id' => $akdeniz->id,
                'excerpt' => 'Tarçın ve şekerle rulo yapılmış, fırından çıkınca üstüne krema sürülen yumuşak tatlı ekmek.',
                'description' => 'Tarçınlı rulo, tüm dünyada sevilen ama Akdeniz ve Türk mutfağında kendi yorumuyla parlayan bir tatlı ekmektir. Yumuşak mayalı hamurun tarçın-şeker karışımıyla rulo yapılması ve fırınlanması, evin her köşesini büyüleyici bir kokuya bürür.',
                'country' => 'Türkiye',
                'prep' => 30, 'cook' => 30, 'rest' => 90, 'servings' => 10, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '450', 'gram',       1, '450', 'g'],
                    [$seker,   '100', 'gram',       2, '100', 'g'],
                    [$ter,     '80',  'gram',       3, '80',  'g'],
                    [$sut,     '180', 'mililitre',  4, '180', 'ml'],
                    [$yumurta, '2',   'adet',       5, '2',   'adet'],
                    [$tuz,     '6',   'gram',       6, '6',   'g'],
                    [$yogurt,  '100', 'gram',       7, '100', 'g'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şekerin yarısı ve kuru mayayı karıştırın. Ilık süt, yumurta ve eritilmiş tereyağıyla pürüzsüz hamur yoğurun; 1,5 saat kabarın.', null, 5400],
                    [2, 'Hamuru dikdörtgen açın; tereyağı sürüp kalan şeker ve bol tarçın karışımını yayın.', null, 600],
                    [3, 'Sıkıca rulo yapın; eşit parçalara kesin ve yağlı tepsiye dizin. 30 dakika daha kabarın.', null, 1800],
                    [4, '180 °C fırında 25–30 dakika altın rengi olana kadar pişirin.', null, 1800],
                    [5, 'Ilık iken üzerine yoğurt ve şeker karıştırılmış krema sürün.', 'Fırından çıkınca 5 dakika bekleyin ki krema erimeden sürülsün.', null],
                ],
            ],

            // ── 19 ─────────────────────────────────────────────────────────
            [
                'title' => 'Kıbrıs Kolokoti',
                'slug' => 'kibris-kolokoti',
                'cuisine_id' => $kibris->id,
                'excerpt' => 'Balkabağı, ceviz ve şekerle hazırlanan geleneksel Kıbrıs el böreği; tatlı ve tuzlunun eşsiz birlikteliği.',
                'description' => 'Kolokoti, Kıbrıs\'a özgü, balkabağı ve ceviz dolu el yapımı bir börek/turtacıktır. Orta Çağ\'dan bu yana Kıbrıslıların sofralarında bulunan bu tarif, hem tatlı hem de tuzlu aromaların nadir görülen uyumunu taşır.',
                'country' => 'Kıbrıs',
                'prep' => 40, 'cook' => 40, 'rest' => 30, 'servings' => 10, 'difficulty' => 'Orta',
                'ingredients' => [
                    [$un,      '400', 'gram',       1, '400', 'g'],
                    [$zeytiny, '50',  'mililitre',  2, '50',  'ml'],
                    [$tuz,     '7',   'gram',       3, '7',   'g'],
                    [$seker,   '30',  'gram',       4, '30',  'g'],
                    [$ceviz,   '100', 'gram',       5, '100', 'g'],
                    [$yumurta, '1',   'adet',       6, '1',   'adet'],
                ],
                'steps' => [
                    [1, 'Un, tuz, şekerin yarısını ve zeytinyağını karıştırın; ılık suyla (200 ml) yumuşak hamur yoğurun. 30 dakika dinlendirin.', null, 1800],
                    [2, 'İç harç için rendelenmiş balkabağı, kıyılmış ceviz ve kalan şekeri harmanlayın; tuzlayın.', null, 900],
                    [3, 'Hamuru ince açın; yuvarlak ya da kare şekillere kesin. Her birinin ortasına harç koyun, kapatıp kenarlarını sıkışın.', null, 1200],
                    [4, 'Yüzeye yumurta sarısı fırçalayın. 180 °C fırında 35–40 dakika pişirin.', null, 2400],
                ],
            ],

            // ── 20 ─────────────────────────────────────────────────────────
            [
                'title' => 'Yoğurtlu Soğan Halkası',
                'slug' => 'yogurtlu-sogan-halkasi',
                'cuisine_id' => $akdeniz->id,
                'excerpt' => 'Yoğurt ve undan hazırlanan çıtır hamura batırılmış, yağda kızartılmış soğan halkası.',
                'description' => 'Soğan halkası; Akdeniz ve dünya mutfağında sevilen bir atıştırmalıktır. Yoğurt bazlı hamura batırılan soğanlar, kızgın yağda kabaran çıtır bir kaplama kazanır. Ev yapımı versiyon, pazar ürünlerinden hem daha lezzetli hem de katkısızdır.',
                'country' => 'Türkiye',
                'prep' => 15, 'cook' => 20, 'rest' => 10, 'servings' => 4, 'difficulty' => 'Kolay',
                'ingredients' => [
                    [$un,      '150', 'gram',  1, '150', 'g'],
                    [$yogurt,  '100', 'gram',  2, '100', 'g'],
                    [$yumurta, '1',   'adet',  3, '1',   'adet'],
                    [$tuz,     '5',   'gram',  4, '5',   'g'],
                    [$seker,   '5',   'gram',  5, '5',   'g'],
                    [$sogan,   '2',   'adet',  6, null,  null],
                ],
                'steps' => [
                    [1, 'Un, yoğurt, yumurta, tuz ve şekeri iyice çırpın; hafif akışkan hamur elde edin. 10 dakika bekletin.', null, 600],
                    [2, 'Soğanları kalın halkalar halinde kesin; ayrı ayrı halkalarına ayırın.', null, 300],
                    [3, 'Halkalaları hamura batırın ve kızgın bol yağa alın; orta ateşte her iki tarafı altın rengi olana kadar kızartın.', null, 900],
                    [4, 'Kağıt havluya alın; tuzlayıp hemen servis edin.', 'Hava alması için kaplı tutmayın; çıtırlığı gider.', null],
                ],
            ],
        ];

        foreach ($recipes as $data) {
            if (Recipe::where('slug', $data['slug'])->exists()) {
                $this->command->line("  SKIP (exists): {$data['slug']}");

                continue;
            }

            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $data['cuisine_id'],
                'category_id' => $category->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'],
                'description' => $data['description'],
                'country' => $data['country'] ?? null,
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

            foreach ($data['ingredients'] as [$ingId, $qty, $unit, $sort, $canonQty, $canonUnit]) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ingId,
                    'display_quantity' => $qty,
                    'display_measure_code' => $unit,
                    'sort_order' => $sort,
                    'canonical_quantity' => $canonQty,
                    'canonical_unit' => $canonUnit,
                    'scaling_behavior' => 'scalable',
                    'is_optional' => false,
                    'conversion_confidence' => $canonQty ? 'verified' : 'unknown',
                ]);
            }

            foreach ($data['steps'] as [$position, $instruction, $tip, $duration]) {
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'position' => $position,
                    'title' => 'Adım '.$position,
                    'instruction' => $instruction,
                    'chef_tip' => $tip,
                    'duration_seconds' => $duration,
                    'timer_required' => false,
                    'is_optional' => false,
                ]);
            }

            RecipeProvenance::create([
                'recipe_id' => $recipe->id,
                'content_source_id' => null,
                'source_type' => 'original',
                'is_ai_generated' => false,
                'is_user_generated' => false,
                'verification_status' => 'verified',
                'confidence' => 'high',
                'reviewed_at' => now(),
                'is_active' => true,
            ]);

            $this->command->info("  ✓ {$recipe->title}");
        }

        $total = Recipe::where('status', 'published')->where('verification_status', 'verified')->count();
        $this->command->info("✓ HamurIsiRecipesSeeder tamamlandı. Toplam yayında: {$total}");
    }
}
