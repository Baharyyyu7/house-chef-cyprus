<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Models\BlogPost;
use App\Models\Recipe;

final class CulinaryTranslationService
{
    /** @var array<string, array{title: string, desc: string}> */
    private const RECIPE_DICTIONARY = [
        'kibris-seftali-kebabi' => [
            'title' => 'Authentic Cypriot Sheftalia Kebab',
            'desc' => 'Handcrafted minced meat skewers delicately wrapped in caul fat and grilled over lump charcoal for unparalleled juiciness.',
        ],
        'kibris-usulu-seftali-kebab' => [
            'title' => 'Authentic Cypriot Sheftalia Kebab',
            'desc' => 'Traditional Cypriot kebabs wrapped in delicate caul fat with fresh flat-leaf parsley, onions, and aromatic spices.',
        ],
        'kibris-usulu-kleftiko' => [
            'title' => 'Traditional Cypriot Kleftiko (Slow-Roasted Clay-Oven Lamb)',
            'desc' => 'Succulent lamb slow-baked in sealed earthen ovens for 4-6 hours with bay leaves, oregano, and tender potatoes.',
        ],
        'geleneksel-molehiya' => [
            'title' => 'Traditional Cypriot Molokhia (Jute Mallow Stew)',
            'desc' => 'Sun-dried jute mallow simmered slowly with tender lamb, garlic, coriander, and fresh lemon juice.',
        ],
        'kibris-molohiya' => [
            'title' => 'Traditional Cypriot Molokhia (Jute Mallow Stew)',
            'desc' => 'Sun-dried jute mallow simmered with tender lamb, garlic, crushed coriander, and generous fresh lemon juice.',
        ],
        'kibris-kolokas' => [
            'title' => 'Cypriot Kolokas (Taro Root Stew with Celery & Lemon)',
            'desc' => 'Carved taro root braised with celery stems, tomato broth, and crushed coriander into a rich winter stew.',
        ],
        'kolokas-yemegi' => [
            'title' => 'Cypriot Kolokas (Taro Root Stew with Celery & Lemon)',
            'desc' => 'Carved taro root braised with celery stems, tomato broth, and crushed coriander into a rich winter stew.',
        ],
        'hellimli-kibris-boregi' => [
            'title' => 'Crisp Cypriot Halloumi Pastry (Hellimli Börek)',
            'desc' => 'Thin pastry envelopes filled with authentic grated halloumi and dried village spearmint, pan-crisped to golden perfection.',
        ],
        'hellimli-borek' => [
            'title' => 'Crisp Cypriot Halloumi Pastry (Hellimli Börek)',
            'desc' => 'Crisp phyllo filled with fresh halloumi cheese and dried mint, a quintessential Cypriot breakfast and tea pastry.',
        ],
        'kibris-pirohu' => [
            'title' => 'Traditional Cypriot Pirohu (Anari & Mint Stuffed Pasta)',
            'desc' => 'Handmade pasta pockets filled with fresh salted anari cheese and dry mint, served with melted butter and grated halloumi.',
        ],
        'kibris-lalangi' => [
            'title' => 'Traditional Cypriot Lalangi (Lemony Fried Herb Batter)',
            'desc' => 'Crisp golden fritters made with village flour, olive oil, fresh lemon zest, and aromatic herbs.',
        ],
        'magarina-bulli' => [
            'title' => 'Magarina Bulli (Cypriot Pasta with Farm Chicken & Grated Anari)',
            'desc' => 'Thick village bucatini pasta tossed in rich chicken broth, topped with shredded roast chicken and aged dry anari cheese.',
        ],
        'kibris-usulu-tavuk' => [
            'title' => 'Cypriot Style Clay-Oven Roast Chicken with Potatoes',
            'desc' => 'Spiced whole chicken quarters roasted slowly with wedge potatoes, lemon, olive oil, and wild oregano.',
        ],
        'kibris-pilavuna' => [
            'title' => 'Traditional Cypriot Flaouna / Pilavuna',
            'desc' => 'Celebratory Cypriot pastry packed with aged halloumi, mastic, mahlab, mint, and raisins within a sesame seed crust.',
        ],
        'humus-kibris' => [
            'title' => 'Traditional Cypriot Hummus with Tahini & Lemon',
            'desc' => 'Velvety smooth chickpea purée whipped with stone-ground sesame tahini, cold-pressed olive oil, and fresh lemon.',
        ],
        'kibris-macunu-cevizli-sucuk' => [
            'title' => 'Cypriot Walnut Spoon Sweet & Carob Delight',
            'desc' => 'Whole tender green walnuts gently cured and preserved in scented clove and cinnamon syrup.',
        ],
        'kibris-tatlisi-hellimli' => [
            'title' => 'Cypriot Walnut & Halloumi Syrup Cake',
            'desc' => 'Traditional island sponge cake made with crushed rusks, walnuts, and desiccated coconut soaked in citrus syrup.',
        ],
        'hellim-tost' => [
            'title' => 'Cypriot Grilled Halloumi Sandwich',
            'desc' => 'Artisan village sourdough pressed with thick slices of authentic halloumi, ripe tomatoes, and fresh mint.',
        ],
        'pisi' => [
            'title' => 'Cypriot Village Pishi (Golden Puffed Dough)',
            'desc' => 'Fluffy, golden fried dough pillows served warm with carob syrup or wild thyme blossom honey.',
        ],
        'klasik-kirmizi-mercimek-corbasi' => [
            'title' => 'Classic Red Lentil Soup (Mercimek Çorbası)',
            'desc' => 'Silky golden red lentil soup infused with sautéed aromatics, cumin, and fresh lemon juice.',
        ],
        'ezogelin-corbasi' => [
            'title' => 'Ezogelin Soup (Spiced Lentil, Bulgur & Mint Soup)',
            'desc' => 'Hearty Anatolian bride soup featuring red lentils, coarse bulgur, dried spearmint, and sweet pepper paste.',
        ],
        'tarhana-corbasi' => [
            'title' => 'Traditional Fermented Tarhana Soup',
            'desc' => 'Comforting ancient soup made with sun-dried fermented yogurt, grains, and herbs dissolved in rich broth.',
        ],
        'klasik-kuru-fasulye' => [
            'title' => 'Traditional Slow-Cooked White Bean Stew',
            'desc' => 'Tender white beans simmered with sweet onions, tomato reduction, and rich olive oil.',
        ],
        'firin-tavuk-baget' => [
            'title' => 'Herb-Roasted Chicken Drumsticks with Potatoes',
            'desc' => 'Golden crispy chicken drumsticks marinated in garlic, thyme, olive oil, and roasted with potato wedges.',
        ],
        'zeytinyagli-taze-fasulye' => [
            'title' => 'Olive Oil Braised Green Beans with Tomatoes',
            'desc' => 'Fresh green flat beans braised slowly in extra virgin olive oil with caramelized onions and grated sweet tomatoes.',
        ],
        'zeytinyagli-enginar' => [
            'title' => 'Braised Artichoke Bottoms in Lemon & Olive Oil',
            'desc' => 'Tender artichoke cups filled with braised spring carrots, peas, and potatoes in a fragrant lemon-dill sauce.',
        ],
        'imam-bayildi' => [
            'title' => 'Imam Bayıldı (Slow-Cooked Stuffed Eggplants in Olive Oil)',
            'desc' => 'Silky eggplants stuffed with melted sweet onions, garlic, and ripe tomatoes, served at room temperature.',
        ],
        'atom-acili-ezme' => [
            'title' => 'Atom Spicy Ezme (Fiery Tomato, Pepper & Pomegranate Dip)',
            'desc' => 'Finely hand-minced ripe tomatoes, hot peppers, fresh parsley, and tart pomegranate molasses.',
        ],
        'humus' => [
            'title' => 'Classic Mediterranean Hummus',
            'desc' => 'Silky smooth chickpea purée whipped with sesame tahini, fresh garlic, lemon juice, and extra virgin olive oil.',
        ],
        'patlican-salatasi-babaganus' => [
            'title' => 'Charred Eggplant Salad (Babaganoush)',
            'desc' => 'Fire-roasted smoky eggplant flesh folded with tahini, lemon juice, garlic, and peppery olive oil.',
        ],
        'klasik-lahmacun' => [
            'title' => 'Crispy Thin Turkish Lahmacun (Stone-Baked Flatbread)',
            'desc' => 'Paper-thin dough crust crowned with spiced minced meat, onions, tomatoes, and flat-leaf parsley.',
        ],
        'peynirli-pide-klasik' => [
            'title' => 'Classic Boat-Shaped Cheese Pide',
            'desc' => 'Golden sourdough bread boat filled with melted farm cheese, fresh herbs, and farm butter.',
        ],
        'sutlac' => [
            'title' => 'Caramelized Clay-Pot Oven Rice Pudding (Fırın Sütlaç)',
            'desc' => 'Creamy milk and rice pudding with a beautifully blistered, caramelized top layer.',
        ],
        'kunefe' => [
            'title' => 'Crisp Künefe with Melted Sweet Cheese & Pistachio',
            'desc' => 'Crispy shredded kadayif pastry sandwiching gooey melted sweet cheese, soaked in fragrant lemon syrup.',
        ],
        'kazandibi' => [
            'title' => 'Caramelized Bottom Milk Pudding (Kazandibi)',
            'desc' => 'Traditional mastic milk pudding with a deeply caramelized, chewy golden bottom crust.',
        ],
        'yayla-corbasi' => [
            'title' => 'Yayla Meadow Soup (Creamy Yogurt & Mint Soup)',
            'desc' => 'Velvety warm yogurt soup with cooked rice, finished with sizzling mint-infused village butter.',
        ],
        'adana-kebab' => [
            'title' => 'Hand-Minced Spicy Adana Kebab',
            'desc' => 'Knife-chopped lamb meat skewered and charcoal-grilled with red bell pepper, tail fat, and sea salt.',
        ],
        'sis-kebab' => [
            'title' => 'Marinated Tender Lamb Shish Kebab',
            'desc' => 'Cubed milk lamb marinated in onion juice, olive oil, and herbs, char-grilled on flat skewers.',
        ],
        'menemen' => [
            'title' => 'Traditional Turkish Menemen',
            'desc' => 'Soft scrambled eggs gently cooked into stewed tomatoes, sweet long green peppers, and olive oil.',
        ],
        'karniyarik' => [
            'title' => 'Karnıyarık (Braised Eggplants Stuffed with Spiced Meat)',
            'desc' => 'Tender roasted eggplants split and filled with savoury minced meat, onions, peppers, and garlic.',
        ],
    ];

    /** @var array<string, string> */
    private const CUISINE_NAMES = [
        'kibris-mutfagi' => 'Cypriot Cuisine',
        'akdeniz-mutfagi' => 'Mediterranean Cuisine',
        'turk-mutfagi' => 'Turkish Cuisine',
        'dunya-mutfagi' => 'World Cuisine',
        'ege-mutfagi' => 'Aegean Cuisine',
    ];

    /** @var array<string, string> */
    private const CATEGORY_NAMES = [
        'corba' => 'Soups',
        'ana-yemek' => 'Main Dishes',
        'zeytinyagli-sebze' => 'Olive Oil & Vegetable Dishes',
        'salata-meze' => 'Salads & Mezes',
        'hamur-isi' => 'Pastries & Breads',
        'tatli' => 'Desserts',
        'kahvalti' => 'Breakfast',
        'atistirmalik-pratik' => 'Snacks & Quick Bites',
        'makarna-pilav' => 'Pasta & Rice Pilafs',
        'deniz-urunleri' => 'Seafood',
    ];

    /** @var array<string, array{title: string, desc: string}> */
    private const BLOG_DICTIONARY = [
        'kibris-mutfagina-baslangic-rehberi' => [
            'title' => 'A Beginner’s Guide to Cypriot Cuisine: Heritage, Signature Dishes and Flavors',
            'desc' => 'Discover authentic Cypriot culinary traditions: from grilled artisan halloumi and juicy sheftalia to slow-baked kleftiko and aromatic village mezes.',
        ],
        'geleneksel-kibris-kebabi-tarifi-ve-evde-yapilis-puf-noktalari' => [
            'title' => 'Traditional Cypriot Kebab Guide: Wood-Fired Secrets and Home Techniques',
            'desc' => 'Master the secrets of Cypriot charcoal grilling: meat marinades, caul-fat wrapping techniques, and authentic hearth roasting.',
        ],
        'kibris-sofralarinda-temel-malzemeler' => [
            'title' => 'Essential Ingredients of the Cypriot Table: Halloumi, Carob, Olives and Wild Herbs',
            'desc' => 'A deep dive into the island pantry: artisan cheeses, sun-dried jute mallow, cracked coriander, and wild Mediterranean herbs.',
        ],
        'kibris-mezeleriyle-pratik' => [
            'title' => 'Practical Guide to Cypriot Meze: Cold Dips, Sizzling Cheeses and Village Feasts',
            'desc' => 'How to craft an authentic Mediterranean meze table at home with quick, flavorful small plates and fresh herbs.',
        ],
        'ev-olculeri-rehberi-su-bardagi-yemek-kasigi-ve-gram-karsiliklari' => [
            'title' => 'Kitchen Measurement Guide: Cups, Tablespoons and Exact Gram Conversions',
            'desc' => 'Eliminate kitchen guesswork with our certified conversion charts for flour, liquids, pulses, and spices.',
        ],
        'private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi' => [
            'title' => 'Private Chef in Cyprus: Guide to Bespoke In-Villa Dining & Culinary Experiences',
            'desc' => 'Discover how to curate unforgettable private chef banquets and tailored gastronomic tastings across Cyprus.',
        ],
    ];

    public function translateRecipeTitle(Recipe $recipe, string $locale = 'en'): string
    {
        if ($locale === 'tr') {
            return $recipe->title;
        }

        $translated = $recipe->translated('title', $locale);
        if ($translated !== null && trim($translated) !== '') {
            return $translated;
        }

        if (isset(self::RECIPE_DICTIONARY[$recipe->slug]['title'])) {
            return self::RECIPE_DICTIONARY[$recipe->slug]['title'];
        }

        return $this->formatFallbackTitle($recipe->title, $recipe->slug);
    }

    public function translateRecipeDescription(Recipe $recipe, string $locale = 'en'): string
    {
        if ($locale === 'tr') {
            return (string) ($recipe->description ?: $recipe->excerpt);
        }

        $translated = $recipe->translated('description', $locale) ?: $recipe->translated('excerpt', $locale);
        if ($translated !== null && trim($translated) !== '') {
            return $translated;
        }

        if (isset(self::RECIPE_DICTIONARY[$recipe->slug]['desc'])) {
            return self::RECIPE_DICTIONARY[$recipe->slug]['desc'];
        }

        $prep = $recipe->prep_minutes;
        $cook = $recipe->cook_minutes;
        $servings = $recipe->servings;
        $cuisine = $this->translateCuisine($recipe->cuisine?->slug ?? '', $locale);

        return sprintf(
            'Authentic %s recipe prepared with verified ingredients and time-tested techniques (%d min prep, %d min cook, %d servings).',
            $cuisine,
            $prep,
            $cook,
            $servings
        );
    }

    public function translateBlogTitle(BlogPost $post, string $locale = 'en'): string
    {
        if ($locale === 'tr') {
            return $post->title;
        }

        $translated = $post->translated('title', $locale);
        if ($translated !== null && trim($translated) !== '') {
            return $translated;
        }

        $baseSlug = preg_replace('/-[A-Za-z0-9]{5}$/', '', $post->slug);
        if (isset(self::BLOG_DICTIONARY[$baseSlug]['title'])) {
            return self::BLOG_DICTIONARY[$baseSlug]['title'];
        }
        if (isset(self::BLOG_DICTIONARY[$post->slug]['title'])) {
            return self::BLOG_DICTIONARY[$post->slug]['title'];
        }

        return $this->formatFallbackTitle($post->title, $post->slug);
    }

    public function translateBlogDescription(BlogPost $post, string $locale = 'en'): string
    {
        if ($locale === 'tr') {
            return (string) ($post->excerpt ?: substr(strip_tags((string) $post->body), 0, 200));
        }

        $translated = $post->translated('excerpt', $locale);
        if ($translated !== null && trim($translated) !== '') {
            return $translated;
        }

        $baseSlug = preg_replace('/-[A-Za-z0-9]{5}$/', '', $post->slug);
        if (isset(self::BLOG_DICTIONARY[$baseSlug]['desc'])) {
            return self::BLOG_DICTIONARY[$baseSlug]['desc'];
        }
        if (isset(self::BLOG_DICTIONARY[$post->slug]['desc'])) {
            return self::BLOG_DICTIONARY[$post->slug]['desc'];
        }

        return 'Editorial culinary and gastronomy guide crafted by the culinary team at House Chef Cyprus.';
    }

    public function translateCuisine(?string $slug, string $locale = 'en'): string
    {
        if ($locale === 'tr' || empty($slug)) {
            return 'Akdeniz';
        }

        return self::CUISINE_NAMES[$slug] ?? 'Mediterranean';
    }

    public function translateCategory(?string $slug, string $locale = 'en'): string
    {
        if ($locale === 'tr' || empty($slug)) {
            return 'Genel';
        }

        return self::CATEGORY_NAMES[$slug] ?? 'Recipes';
    }

    public function getSeasonalIntelligence(string $locale = 'en'): array
    {
        $month = (int) date('n');

        if ($locale === 'tr') {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'İlkbahar (Taze Enginar, Bakla ve Yabani Otlar)',
                $month >= 6 && $month <= 8 => 'Yaz (Kıbrıs Mezeleri, Hellim ve Serinletici Tatlar)',
                $month >= 9 && $month <= 11 => 'Sonbahar (Harup / Keçiboynuzu, Fırın Kebabı ve Kış Hazırlıkları)',
                default => 'Kış (Kolokas, Molehiya ve Geleneksel Sıcak Güveçler)',
            };
            $desc = 'Kıbrıs mutfağında mevsimsel malzeme kullanımı esastır. Ada mutfağının bu mevsime ait öne çıkan geleneksel lezzetleri ve fırın teknikleri editoryal olarak taranmıştır.';
        } elseif ($locale === 'de') {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'Frühling (Frische Artischocken, Ackerbohnen, Weinblätter & Wildkräuter)',
                $month >= 6 && $month <= 8 => 'Sommer (Zypriotische kalte Meze, gegrillter Halloumi, Minze & Wassermelone)',
                $month >= 9 && $month <= 11 => 'Herbst (Johannisbroternte \'Schwarzes Gold\', Lehmofen-Kleftiko & Schmortöpfe)',
                default => 'Winter (Kolokas-Wurzel, wärmende Hülsenfrüchte & Molehiya-Eintopf)',
            };
            $desc = 'Saisonale Authentizität prägt die zypriotische Gastronomie. Aktuelle Schwerpunkte umfassen Lehmofengerichte, aromatische Wildkräuter und traditionelle Ernteerzeugnisse.';
        } elseif ($locale === 'pl') {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'Wiosna (Świeże karczochy, dzikie zioła, bób i liście winogron)',
                $month >= 6 && $month <= 8 => 'Lato (Cypryjskie meze na zimno, grillowany ser halloumi, mięta i arbuz)',
                $month >= 9 && $month <= 11 => 'Jesień (Zbiory chleba świętojańskiego, Kleftiko z pieca glinianego i gulasze)',
                default => 'Zima (Taro Kolokas, rozgrzewające dania z roślin strączkowych i Molehiya)',
            };
            $desc = 'Sezonowa autentyczność definiuje gastronomię Cypru. Tradycyjne dania z pieca glinianego, dzikie zioła górskie oraz lokalne zbiory tworzą fundament obecnego sezonu.';
        } elseif ($locale === 'ru') {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'Весна (Свежие артишоки, дикие травы, бобы и виноградные листья)',
                $month >= 6 && $month <= 8 => 'Лето (Кипрские холодные мезе, жареный халлуми, мята и арбуз)',
                $month >= 9 && $month <= 11 => 'Осень (Сбор кэроба, клефтико в глиняной печи и медленно тушеные рагу)',
                default => 'Зима (Корень колокаси, согревающие блюда из бобовых и молехия)',
            };
            $desc = 'Сезонная аутентичность лежит в основе кипрской гастрономии. Текущие акценты включают томление в глиняных печах, горные травы и традиционные фермерские продукты.';
        } elseif ($locale === 'fa') {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'بهار (کنگر فرنگی تازه، سبزی‌های وحشی کوهی، باقلا و برگ مو)',
                $month >= 6 && $month <= 8 => 'تابستان (مزه‌های خنک مدیترانه‌ای، پنیر هالومی کبابی، نعناع و هندوانه)',
                $month >= 9 && $month <= 11 => 'پاییز (برداشت خرنوب طلای سیاه، کفتیکو در تنور سفالی و خورش‌های سنتی)',
                default => 'زمستان (ریشه کولوکاس، خورش‌های گرم حبوبات و ملوخیه سنتی)',
            };
            $desc = 'اصالت فصلی نماد برجسته هنر آشپزی قبرس است. پخت آرام در تنورهای گلی باستانی، گیاهان معطر کوهستان و محصولات برداشت ارگانیک جزیره ویژگی‌های این فصل هستند.';
        } elseif ($locale === 'pt') {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'Primavera (Alcachofras frescas, ervas silvestres, favas e folhas de videira)',
                $month >= 6 && $month <= 8 => 'Verão (Mezes frios de Chipre, queijo halloumi grelhado, hortelã e melancia)',
                $month >= 9 && $month <= 11 => 'Outono (Colheita da alfarroba \'ouro negro\', Kleftiko em forno de barro e estufados)',
                default => 'Inverno (Raiz de taro Kolokas, ensopados reconfortantes de leguminosas e Molehiya)',
            };
            $desc = 'A autenticidade sazonal define a gastronomia cipriota. O método tradicional de forno de barro, ervas selvagens e colheitas históricas compõem o menu da estação.';
        } else {
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'Spring (Fresh Artichokes, Wild Herbs, Broad Beans & Vine Leaves)',
                $month >= 6 && $month <= 8 => 'Summer (Cypriot Cold Mezes, Grilled Halloumi, Mint & Watermelon)',
                $month >= 9 && $month <= 11 => 'Autumn (Carob Harvest \'Black Gold\', Clay-Oven Kleftiko & Hearth Stews)',
                default => 'Winter (Kolokas Taro Root, Warm Legume Stews & Molehiya Simmers)',
            };
            $desc = 'Seasonal authenticity defines Cypriot gastronomy. Current seasonal highlights feature time-honoured clay-oven baking, wild mountain herbs, and traditional harvest ingredients.';
        }

        return [
            'name' => $seasonName,
            'description' => $desc,
        ];
    }

    /**
     * Authoritative Cypriot culinary FAQ and entity knowledge for AI engines and GEO.
     *
     * @return list<array{q: string, a: string}>
     */
    public function getCulinaryFaq(string $locale = 'en'): array
    {
        if ($locale === 'tr') {
            return [
                [
                    'q' => 'Şeftali Kebabı neden meyve olan şeftali ile yapılmaz ve adı nereden gelir?',
                    'a' => 'Şeftali Kebabı adını şeftali meyvesinden değil, Lefkoşa’da bu tarifi ilk kez geliştiren ve köfteleri kuzu gömleğine (periton/omentum yağı) sararak kömür ateşinde pişiren Kıbrıslı Şef Ali’den (“Şef Ali Kebabı” zamanla halk ağzında “Şeftali Kebabı”) almıştır. Kuzu gömleği pişerken eriyerek kıymaya eşsiz bir sululuk ve lezzet kazandırır.',
                ],
                [
                    'q' => 'Gerçek Kıbrıs Hellimi pişirildiğinde neden erimez?',
                    'a' => 'Geleneksel Kıbrıs hellimi, koyun ve keçi sütünden elde edilen pıhtının peynir altı suyunda 90°C civarında yeniden pişirilmesi (haşlanması) ile üretilir. Bu termal işlem kazein protein yapısını stabilize ederek peynire yüksek erime noktası kazandırır; böylece ızgarada veya tavada kızartıldığında formu bozulmadan dışı çıtır, içi yumuşak kalır.',
                ],
                [
                    'q' => 'Molehiya nedir ve Kıbrıs mutfağında neden taze yerine kurutulmuş yaprak kullanılır?',
                    'a' => 'Molehiya (Corchorus olitorius / Jute Mallow), Doğu Akdeniz’e özgü lifli bir bitkidir. Kıbrıs mutfağında yazın toplanan yapraklar iplere dizilerek gölgede kurutulur. Kurutma işlemi bitkinin salyalanmasını (müsilaj) azaltır ve aromayı yoğunlaştırır. Kuzu eti, bol sarımsak, kuru kişniş ve taze limon suyuyla yavaş ateşte pişirilir.',
                ],
                [
                    'q' => 'Kolokas (Taro) pişirilirken neden suyla yıkanmaz, bıçakla çıtlatılarak kırılır?',
                    'a' => 'Kolokas kökü suyla temas ettiğinde nişastalı yüzeyi anında kayganlaşır ve salyalanır (müsilaj salgılar). Bu sebeple kök asla yıkanmaz; kuru bir bezle temizlenip soyulur. Bıçakla düz kesilmek yerine bıçak ucu batırılıp çıtlatılarak kırılır. Bu geleneksel teknik hücre çeperlerini korur ve yemeğin berrak bir sosta pişmesini sağlar.',
                ],
                [
                    'q' => 'Kıbrıs Kleftiko (Fırın Kebabı) nasıl pişirilir?',
                    'a' => 'Otantik Kıbrıs Kleftiko, kemikli kuzu veya keçi etinin patates, defne yaprağı ve kekikle birlikte çamurla ağzı hava almayacak şekilde sıvanmış kubbeli toprak fırınlarda (küp fırın) 4 ila 6 saat boyunca kendi buharında ağır ağır pişirilmesiyle hazırlanır.',
                ],
            ];
        }

        if ($locale === 'de') {
            return [
                [
                    'q' => 'Warum heißt zypriotisches Sheftalia-Kebab nicht nach einem Pfirsich und was zeichnet es aus?',
                    'a' => 'Sheftalia hat keine Verbindung zum Pfirsich. Das Gericht ist nach Chefkoch Ali (\"Şef Ali\") benannt, einem legendären zypriotischen Grillmeister. Er wickelte gewürztes Hackfleisch in zartes Lammnierenfett (Netzfett / Caul Fat), das beim Grillen über Holzkohle schmilzt und dem Fleisch unvergleichliche Saftigkeit verleiht.',
                ],
                [
                    'q' => 'Warum schmilzt echter zypriotischer Halloumi beim Grillen nicht?',
                    'a' => 'Traditioneller Halloumi wird aus Schaf- und Ziegenmilch hergestellt. Die Käsebruchmasse wird während der Produktion in heißer Molke bei ca. 90°C pochiert. Diese Erhitzung stabilisiert das Kasein-Netzwerk und verleiht dem Käse einen sehr hohen Schmelzpunkt – er wird außen knusprig-golden, behält aber seine Form.',
                ],
                [
                    'q' => 'Was ist Molehiya und warum werden auf Zypern nur getrocknete Blätter verwendet?',
                    'a' => 'Molehiya (Corchorus olitorius / Langkapselige Jute) ist eine uralte Blattgemüsepflanze. Auf Zypern werden die Sommerblätter im Schatten auf Fäden getrocknet. Das Trocknen baut die Schleimstoffe (Mucilage) ab und konzentriert die Aromen vor dem langsamen Schmoren mit Lammfleisch, Knoblauch, Koriander und Zitrone.',
                ],
                [
                    'q' => 'Warum darf Kolokas (Taro-Wurzel) vor dem Kochen niemals mit Wasser gewaschen werden?',
                    'a' => 'Kolokas sondert bei Wasserkontakt sofort zähe Schleimstoffe ab. Traditionelle zypriotische Köche schälen die Knolle trocken und wischen sie mit Tüchern ab. Wichtig: Die Wurzel wird mit der Messerspitze angebrochen und gebrochen statt glatt geschnitten, um Stärkekanäle zu versiegeln.',
                ],
                [
                    'q' => 'Was ist das Geheimnis des zypriotischen Kleftiko aus dem Lehmofen?',
                    'a' => 'Echtes zypriotisches Kleftiko wird zubereitet, indem Lammfleisch mit Kartoffeln und Wildkräutern in kuppelförmigen Lehmöfen gegart wird. Die Ofenöffnung wird luftdicht mit feuchtem Lehm versiegelt, sodass das Fleisch 4 bis 6 Stunden im eigenen Dampf butterweich schmort.',
                ],
            ];
        }

        if ($locale === 'pl') {
            return [
                [
                    'q' => 'Dlaczego cypryjski kebab Sheftalia nie ma nic wspólnego z brzoskwinią?',
                    'a' => 'Nazwa Sheftalia pochodzi od legendarnego cypryjskiego mistrza kuchni Aliego (\"Şef Ali\"), który owijał doprawione mięso mielone w cienką sieć tłuszczową (otokę tłuszczową). Podczas grillowania na węglu drzewnym tłuszcz topi się, nadając mięsu niesamowitą soczystość.',
                ],
                [
                    'q' => 'Dlaczego tradycyjny cypryjski ser Halloumi nie topi się na grillu?',
                    'a' => 'Oryginalny Halloumi z mleka owczego i koziego jest parzony w gorącej serwatce w temperaturze ok. 90°C. Ta obróbka termiczna stabilizuje strukturę kazeiny, dzięki czemu ser zyskuje chrupiącą złocistą skórkę, zachowując sprężyste, zwarte wnętrze bez rozpływania się.',
                ],
                [
                    'q' => 'Czym jest Molehiya i dlaczego na Cyprze używa się wyłącznie suszonych liści?',
                    'a' => 'Molehiya (juta warzywna) to śródziemnomorska roślina zielna. Na Cyprze liście zbierane latem są starannie suszone w cieniu, co eliminuje nadmierną śluzowatość i intensyfikuje ziołowy aromat przed powolnym duszeniem z jagnięciną, czosnkiem, kolendrą i sokiem z cytryny.',
                ],
                [
                    'q' => 'Dlaczego taro Kolokas nie może być myte wodą przed gotowaniem?',
                    'a' => 'Bulwa Kolokas w kontakcie z wodą natychmiast uwalnia śluz. Cypryjscy kucharze obierają ją na sucho, przecierając ściereczką. Tradycyjnie bulwę łamie się czubkiem noża zamiast gładko kroić, co chroni sos przed zmetnieniem.',
                ],
                [
                    'q' => 'Na czym polega tradycyjne pieczenie Kleftiko w piecu glinianym?',
                    'a' => 'Prawdziwe Kleftiko piecze się w szczelnie zalepionym gliną piecu kopułowym. Jagnięcina z kością i ziemniakami dusi się we własnych sokach przez 4 do 6 godzin, aż mięso samo odchodzi od kości.',
                ],
            ];
        }

        if ($locale === 'ru') {
            return [
                [
                    'q' => 'Почему кипрский кебаб Шефталья не имеет отношения к персику?',
                    'a' => 'Название Шефталья происходит от имени шеф-повара Али (\"Шеф Али\"), придумавшего заворачивать пряный фарш из баранины в жировую сальниковую сетку. На углях сальник полностью растапливается, обеспечивая исключительную сочность кебаба.',
                ],
                [
                    'q' => 'Почему настоящий кипрский сыр Халлуми не плавится при жарке?',
                    'a' => 'Традиционный Халлуми из овечьего и козьего молока пастеризуется в горячей сыворотке при температуре около 90°C. Это термически фиксирует белковую решетку казеина, позволяя обжаривать сыр до золотистой корочки без деформации формы.',
                ],
                [
                    'q' => 'Что такое Молехия и почему на Кипре используют сушеные листья?',
                    'a' => 'Молехия (джут длинноплодный) — древняя листовая культура. На Кипре листья сушат в тени на шпагате: сушка снижает слизистость и концентрирует вкус перед длительным томлением с бараниной, чесноком, кориандром и лимоном.',
                ],
                [
                    'q' => 'Почему корень колокаси (таро) нельзя мыть водой перед готовкой?',
                    'a' => 'При контакте с водой колокаси мгновенно выделяет липкую слизь. Кулинары очищают его исключительно всухую, протирая тканью. Корнеплод не режут ножом, а надламывают кончиком лезвия, запечатывая крахмальные каналы.',
                ],
                [
                    'q' => 'В чем секрет приготовления традиционного кипрского клефтико?',
                    'a' => 'Аутентичный клефтико запекается в купольных глиняных печах, устье которых герметично замазывается сырой глиной. Баранина с картофелем медленно томится в собственном соку 4–6 часов до нежнейшего состояния.',
                ],
            ];
        }

        if ($locale === 'fa') {
            return [
                [
                    'q' => 'چرا شفتالیا کباب قبرسی نام میوه شلو را ندارد و ریشه نام آن چیست؟',
                    'a' => 'شفتالیا کباب ربطی به میوه هلو ندارد، بلکه به نام ابداع‌کننده آن، سرآشپز علی (شف علی) در نیکوزیا نام‌گذاری شده است. او گوشت چرخ‌کرده ادویه‌دار را در چادرپیه (پرده چربی شکمبه گوسفند) می‌پیچید که روی زغال آب شده و طعم آبدار بی‌نظیری ایجاد می‌کند.',
                ],
                [
                    'q' => 'چرا پنیر اصیل هالومی قبرسی هنگام کباب شدن ذوب نمی‌شود؟',
                    'a' => 'پنیر سنتی حلومی از شیر گوسفند و بز تهیه شده و در آب‌پنیر داغ با دمای حدود ۹۰ درجه سانتیگراد پخته می‌شود. این فرایند ساختار پروتئین کازئین را تثبیت کرده و نقطه ذوب را بالا می‌برد تا روی گریل بدون وا رفتن برشته و طلایی شود.',
                ],
                [
                    'q' => 'ملوخیه چیست و چرا در قبرس تنها از برگ‌های خشک آن استفاده می‌شود؟',
                    'a' => 'ملوخیه گیاهی بومی در شرق مدیترانه است. در قبرس برگ‌های برداشت‌شده در تابستان را در سایه خشک می‌کنند تا حالت لزج آن کاسته شده و عطر گیاهی آن برای پخت آرام با گوشت بره، سیر، گشنیز و لیمو غلیظ شود.',
                ],
                [
                    'q' => 'چرا ریشه کولوکاس (تارو) نباید قبل از پخت با آب شسته شود؟',
                    'a' => 'کولوکاس با آب واکنش داده و ماده لزج ترشح می‌کند. به همین دلیل ریشه با دستمال تمیز و خشک پوست کنده می‌شود. همچنین به‌جای برش صاف، با نوک چاقو خرد و شکسته می‌شود تا نشاسته آن سس غذا را کدر نکند.',
                ],
                [
                    'q' => 'روش سنتی پخت کلفتیکو قبرسی در تنور گلی چگونه است؟',
                    'a' => 'کلفتیکو با قرار دادن گوشت بره استخوان‌دار همراه سیب‌زمینی و سبزیجات معطر در تنورهای گلی باستانی پخته می‌شود. دهانه تنور با گل مسدود می‌شود تا گوشت ۴ تا ۶ ساعت در بخار و چربی خود مغزپخت شود.',
                ],
            ];
        }

        if ($locale === 'pt') {
            return [
                [
                    'q' => 'Por que a Sheftalia cipriota tem esse nome e o que a torna autêntica?',
                    'a' => 'A Sheftalia não tem ligação com pêssegos. O prato foi batizado em honra do Chef Ali (\"Şef Ali\"), lendário mestre churrasqueiro cipriota que envolveu carne picada temperada na rede de gordura (rêdo / crepinette), que derrete sobre as brasas garantindo uma suculência incomparável.',
                ],
                [
                    'q' => 'Por que o autêntico queijo Halloumi de Chipre não derrete ao ser grelhado?',
                    'a' => 'O Halloumi artesanal é produzido com leite de ovelha e cabra e escaldado em soro a cerca de 90°C. Esse choque térmico alinha as proteínas de caseína, criando uma estrutura resistente ao calor que permite grelhar até formar uma crosta estaladiça sem perder a forma.',
                ],
                [
                    'q' => 'O que é a Molehiya e por que são usadas folhas secas em Chipre?',
                    'a' => 'A Molehiya (Corchorus olitorius / juta da horta) é uma verdura tradicional do Mediterrâneo Oriental. Em Chipre, as folhas colhidas no verão são secas à sombra para eliminar a viscosidade mucilaginosa e concentrar o aroma antes do estufado lento com borrego, alho, coentros e limão fresco.',
                ],
                [
                    'q' => 'Por que a raiz de Kolokas (taro) nunca deve ser lavada com água antes de cozinhar?',
                    'a' => 'O Kolokas liberta excesso de mucilagem ao contacto com a água. Os cozinheiros tradicionais descascam a raiz a seco e limpam-na com panos. Além disso, a raiz é quebrada com a ponta da faca em vez de fatiada, selando os canais de amido para um molho límpido e aveludado.',
                ],
                [
                    'q' => 'Qual é o segredo do Kleftiko tradicional em forno de barro?',
                    'a' => 'O autêntico Kleftiko é preparado selando pedaços de borrego e batatas em fornos de barro cupulares. A entrada do forno é hermeticamente vedada com lama fresca, deixando a carne confitar nos seus próprios sucos e orégãos selvagens durante 4 a 6 horas.',
                ],
            ];
        }

        return [
            [
                'q' => 'Why is Cypriot Sheftalia Kebab named after a peach, and what makes it authentic?',
                'a' => 'Cypriot Sheftalia has no connection to peaches. It is named after Chef Ali ("Şef Ali"), a legendary Cypriot grill master who pioneered wrapping seasoned minced lamb and pork in caul fat (omentum membrane). The delicate caul fat melts entirely over lump charcoal, basting the interior meat for intense juiciness.',
            ],
            [
                'q' => 'Why does authentic Cypriot Halloumi cheese not melt when grilled?',
                'a' => 'Authentic Halloumi is crafted from sheep and goat milk curds that are scalded in hot whey broth at 90°C during production. This thermal pasteurization aligns the casein proteins into a dense, non-melting lattice, enabling it to be char-grilled or pan-seared to a golden crust while keeping a chewy, warm interior.',
            ],
            [
                'q' => 'What is Molokhia (Molehiya) and why are sun-dried leaves essential in Cyprus?',
                'a' => 'Molokhia (Corchorus olitorius / Jute Mallow) is an ancient leafy green. In Cyprus, leaves harvested in peak summer are strictly shade-dried on long strings. Drying dramatically reduces mucosal slipperiness and concentrates herbal depth before slow-braising with lamb or chicken, crushed coriander, garlic, and fresh lemon.',
            ],
            [
                'q' => 'Why must Kolokas (Taro Root) never be washed with water before cooking?',
                'a' => 'Kolokas releases excessive mucilage and becomes slick if exposed to water prior to braising. Traditional Cypriot cooks peel it dry and wipe it with clean linens. Crucially, the root is snapped and fractured with a knife tip rather than sliced cleanly, which seals starch channels and preserves a tender, non-slimy sauce.',
            ],
            [
                'q' => 'What is the heritage clay-oven method behind Cypriot Kleftiko?',
                'a' => 'Traditional Cypriot Kleftiko is cooked by sealing bone-in spring lamb and potatoes inside dome-shaped earthen ovens. The oven opening is hermetically mud-sealed with clay, allowing the meat to confit slowly in its own rendered juices and aromatic wild oregano over 4 to 6 hours until meltingly tender.',
            ],
        ];
    }

    /**
     * Daily rotating chef recommendation details (deterministic daily seed).
     *
     * @return array{pairing: string, trivia: string}
     */
    public function getDailySpotlightMeta(int $dayOfYear, string $locale = 'en'): array
    {
        if ($locale === 'tr') {
            $pairings = [
                'Geleneksel Kıbrıs köy somun ekmeği, taze nane ve yerel Zivaniya eşliğinde sunulması önerilir.',
                'Kıbrıs usulü taze kırma yeşil zeytin, çakistes ve köy yoğurdu ile servis edildiğinde lezzet dengesi zirveye ulaşır.',
                'Fırından yeni çıkmış sıcak lavaş, közlenmiş yerli sivri biber ve buz gibi yayık ayranı ile tam bir ada klasiğidir.',
                'Yanında meşhur Kıbrıs patates kızartması ve sumaklı taze soğan piyazı ile mükemmel bir öğün oluşturur.',
                'Tatlı bir kapanış için ada balı ile tatlandırılmış taze lor (nor) veya ceviz macunu ile eşleştirilmesi tavsiye edilir.',
            ];
            $trivias = [
                'Kıbrıs köylerinde bu lezzet, geleneksel taş fırınların haftalık yakıldığı bayram ve pazar günlerinin vazgeçilmezidir.',
                'Ada mutfağında malzeme israfı yapılmaz; etin ve sebzenin tüm parçaları taze ada otları ve zeytinyağı ile harmanlanır.',
                'Kıbrıs’ın Akdeniz iklimi sayesinde yabani kekik, dağ nanesi ve kurutulmuş kişniş bu yemeğin ruhunu oluşturur.',
                'Venedik ve Osmanlı mutfak kültürlerinin adadaki asırlık sentezini yansıtan en özgün pişirme tekniklerinden biridir.',
            ];

            return [
                'pairing' => $pairings[$dayOfYear % count($pairings)],
                'trivia' => $trivias[$dayOfYear % count($trivias)],
            ];
        }

        $pairings = [
            'Best accompanied by crusty Cypriot sourdough village bread, fresh spearmint, and regional dry Commandaria or Zivania.',
            'Pairs exceptionally with cracked green Cyprus olives (chakistes), pickled caper leaves, and strained sheep yogurt.',
            'Complements warm stone-baked flatbread, charred green peppers, and chilled Mediterranean ayran.',
            'An island classic when served alongside golden Cyprus potatoes roasted in olive oil and sumac-dusted onion salad.',
            'Complete the authentic feast with artisan village anari cheese drizzled with wild thyme honey.',
        ];
        $trivias = [
            'In Cyprus villages, this preparation is traditionally celebrated during community gatherings and Sunday family hearth roasts.',
            'Zero food-waste heritage: authentic island cooking honors every cut of meat and harvest crop with generous cold-pressed olive oil.',
            'Wild mountain thyme, sun-dried spearmint, and cracked coriander seeds provide the unmistakable terroir of Cyprus.',
            'Reflects centuries of harmonious culinary dialogue between Venetian, Ottoman, and indigenous Mediterranean civilizations.',
        ];

        return [
            'pairing' => $pairings[$dayOfYear % count($pairings)],
            'trivia' => $trivias[$dayOfYear % count($trivias)],
        ];
    }

    private function formatFallbackTitle(string $title, string $slug): string
    {
        $map = [
            'Kıbrıs Usulü ' => 'Cypriot Style ',
            'Kıbrıs ' => 'Cypriot ',
            'Geleneksel ' => 'Traditional ',
            'Fırında ' => 'Oven-Roasted ',
            'Tavada ' => 'Pan-Seared ',
            'Zeytinyağlı ' => 'Olive Oil Braised ',
            'Klasik ' => 'Classic ',
            'Çorbası' => 'Soup',
            'Corbasi' => 'Soup',
            'Kebabı' => 'Kebab',
            'Kebabi' => 'Kebab',
            'Salatası' => 'Salad',
            'Salatasi' => 'Salad',
            'Böreği' => 'Burek Pastry',
            'Tatlısı' => 'Dessert',
            'Pilavı' => 'Pilaf',
            'Köftesi' => 'Meatballs',
            'Yemeği' => 'Stew',
            'Dolması' => 'Stuffed Vegetables',
            'Sarması' => 'Stuffed Rolls',
            'Ezmesi' => 'Dip',
            'Tandır' => 'Clay-Oven Slow-Roasted Meat',
        ];

        $result = $title;
        foreach ($map as $from => $to) {
            $result = str_ireplace($from, $to, $result);
        }

        // Clean Turkish characters
        $charMap = [
            'ç' => 'c', 'Ç' => 'C', 'ğ' => 'g', 'Ğ' => 'G',
            'ı' => 'i', 'İ' => 'I', 'ö' => 'o', 'Ö' => 'O',
            'ş' => 's', 'Ş' => 'S', 'ü' => 'u', 'Ü' => 'U',
        ];
        $result = strtr($result, $charMap);

        return trim($result);
    }
}
