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
