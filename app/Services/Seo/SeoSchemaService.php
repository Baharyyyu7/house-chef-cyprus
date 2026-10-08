<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Str;

/** Builds schemas from already-public, visible editorial data only. */
final class SeoSchemaService
{
    /** @var array<string, array{sameAs: list<string>, is_dish?: bool, about?: array{name: string, description: string}}> */
    private const HERITAGE_ENTITIES = [
        'kibris-seftali-kebabi' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Sheftalia',
                'https://www.wikidata.org/wiki/Q1225501',
            ],
            'about' => [
                'name' => 'Sheftalia',
                'description' => 'Traditional Cypriot sausage made of seasoned minced meat wrapped in caul fat.',
            ],
        ],
        'kibris-usulu-seftali-kebab' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Sheftalia',
                'https://www.wikidata.org/wiki/Q1225501',
            ],
            'about' => [
                'name' => 'Sheftalia',
                'description' => 'Traditional Cypriot sausage made of seasoned minced meat wrapped in caul fat.',
            ],
        ],
        'kibris-usulu-seftali-kebabi' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Sheftalia',
                'https://www.wikidata.org/wiki/Q1225501',
            ],
            'about' => [
                'name' => 'Sheftalia',
                'description' => 'Traditional Cypriot sausage made of seasoned minced meat wrapped in caul fat.',
            ],
        ],
        'kibris-usulu-kleftiko' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Kleftiko',
                'https://www.wikidata.org/wiki/Q3197825',
            ],
            'about' => [
                'name' => 'Kleftiko',
                'description' => 'Traditional slow-cooked bone-in lamb cooked in sealed earthen clay ovens.',
            ],
        ],
        'geleneksel-molehiya' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Mulukhiyah',
                'https://www.wikidata.org/wiki/Q1143896',
            ],
            'about' => [
                'name' => 'Molokhia',
                'description' => 'Sun-dried leaves of Corchorus olitorius braised with meat, garlic, lemon, and coriander.',
            ],
        ],
        'kibris-molohiya' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Mulukhiyah',
                'https://www.wikidata.org/wiki/Q1143896',
            ],
            'about' => [
                'name' => 'Molokhia',
                'description' => 'Sun-dried leaves of Corchorus olitorius braised with meat, garlic, lemon, and coriander.',
            ],
        ],
        'kibris-kolokas' => [
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Taro',
                'https://www.wikidata.org/wiki/Q23798',
            ],
            'about' => [
                'name' => 'Colocasia esculenta',
                'description' => 'Cypriot braised taro root with celery, tomato, and fresh lemon sauce.',
            ],
        ],
        'kolokas-yemegi' => [
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Taro',
                'https://www.wikidata.org/wiki/Q23798',
            ],
            'about' => [
                'name' => 'Colocasia esculenta',
                'description' => 'Cypriot braised taro root with celery, tomato, and fresh lemon sauce.',
            ],
        ],
        'hellimli-kibris-boregi' => [
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Halloumi',
                'https://www.wikidata.org/wiki/Q219717',
            ],
            'about' => [
                'name' => 'Halloumi',
                'description' => 'Traditional Cypriot semi-hard unripened brined cheese made from goat and sheep milk.',
            ],
        ],
        'hellimli-borek' => [
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Halloumi',
                'https://www.wikidata.org/wiki/Q219717',
            ],
            'about' => [
                'name' => 'Halloumi',
                'description' => 'Traditional Cypriot semi-hard unripened brined cheese made from goat and sheep milk.',
            ],
        ],
        'hellim-tost' => [
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Halloumi',
                'https://www.wikidata.org/wiki/Q219717',
            ],
            'about' => [
                'name' => 'Halloumi',
                'description' => 'Grilled halloumi sandwich in artisan village bread with mint and tomato.',
            ],
        ],
        'kibris-pilavuna' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Flaouna',
                'https://www.wikidata.org/wiki/Q5457416',
            ],
            'about' => [
                'name' => 'Flaouna',
                'description' => 'Celebratory Cypriot festive bread pastry filled with aged cheese, mastic, and mint.',
            ],
        ],
        'kibris-pirohu' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Pierogi',
                'https://www.wikidata.org/wiki/Q20074',
            ],
            'about' => [
                'name' => 'Pirohu',
                'description' => 'Cypriot stuffed boiled pasta filled with fresh salted anari cheese and dry spearmint.',
            ],
        ],
        'magarina-bulli' => [
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Bucatini',
                'https://www.wikidata.org/wiki/Q20025',
            ],
            'about' => [
                'name' => 'Bucatini with Chicken',
                'description' => 'Cypriot village pasta boiled in rich chicken stock with shredded roast chicken and grated anari.',
            ],
        ],
        'humus' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Hummus',
                'https://www.wikidata.org/wiki/Q241999',
            ],
            'about' => [
                'name' => 'Hummus',
                'description' => 'Cooked mashed chickpeas blended with sesame tahini, lemon juice, garlic, and olive oil.',
            ],
        ],
        'humus-kibris' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Hummus',
                'https://www.wikidata.org/wiki/Q241999',
            ],
            'about' => [
                'name' => 'Cypriot Hummus',
                'description' => 'Cooked mashed chickpeas blended with sesame tahini, lemon juice, and cold-pressed olive oil.',
            ],
        ],
        'klasik-lahmacun' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Lahmacun',
                'https://www.wikidata.org/wiki/Q501460',
            ],
            'about' => [
                'name' => 'Lahmacun',
                'description' => 'Round, thin piece of dough topped with minced meat, vegetables, and herbs, stone-baked.',
            ],
        ],
        'kunefe' => [
            'is_dish' => true,
            'sameAs' => [
                'https://en.wikipedia.org/wiki/Kanafeh',
                'https://www.wikidata.org/wiki/Q245249',
            ],
            'about' => [
                'name' => 'Kanafeh',
                'description' => 'Traditional dessert made with spun kataifi pastry, sweet unripened cheese, and citrus syrup.',
            ],
        ],
    ];

    /** @return array<string,mixed> */
    public function recipe(Recipe $recipe, array $ingredients, string $imageUrl = '', ?string $cuisineUrl = null): array
    {
        $locale = app()->getLocale();
        $recipeNode = [
            '@type' => 'Recipe', 'name' => $recipe->translated('title'),
            'description' => $recipe->translated('description') ?: $recipe->translated('excerpt'),
            'url' => route('recipes.show', $recipe),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('recipes.show', $recipe),
            ],
            'inLanguage' => $locale,
            'dateModified' => $recipe->updated_at?->toAtomString(),
            'recipeCuisine' => $recipe->cuisine ? taxonomy_name($recipe->cuisine) : null,
            'recipeCategory' => $recipe->category ? taxonomy_name($recipe->category) : null,
            'prepTime' => 'PT'.$recipe->prep_minutes.'M', 'cookTime' => 'PT'.$recipe->cook_minutes.'M',
            'totalTime' => 'PT'.$recipe->totalMinutes().'M', 'recipeYield' => $recipe->servings.' '.$this->yieldUnit($locale),
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $recipe->steps->values()->map(fn ($step, int $index): array => [
                '@type' => 'HowToStep',
                'position' => $step->position ?: $index + 1,
                'name' => $step->title,
                'text' => $step->instruction,
                'url' => route('recipes.show', $recipe).'#step-'.($step->position ?: $index + 1),
            ])->all(),
            'author' => $this->organizationNode(),
            'publisher' => $this->organizationNode(),
            'datePublished' => $recipe->published_at?->toAtomString(),
        ];

        // Equipment (mutfak ekipmanları) - Google HowTo zengin sonucu için faydalı
        if (is_array($recipe->equipment) && $recipe->equipment !== []) {
            $recipeNode['tool'] = array_map(static fn (string $tool): array => [
                '@type' => 'HowToTool',
                'name' => $tool,
            ], $recipe->equipment);
        }

        // Zorluk şemaya yazılmaz: schema.org Recipe'de "difficulty" diye bir
        // alan yok (8 Ekim 2026 denetimi); tanımsız alan doğrulayıcıda uyarı
        // üretir ve hiçbir zengin sonuca girmez. Zorluk sayfada görünüyor.

        // Beslenme bilgisi Google'ın tarif sonucunda kalori olarak görünür.
        // Eksik malzemeyle hesaplanmış bir kalori, hiç kalori göstermemekten daha zararlıdır.
        $beslenme = $this->beslenme($recipe, $locale);
        if ($beslenme !== null) {
            $recipeNode['nutrition'] = $beslenme;
        }

        // Topluluk oyu: sayfadaki görünür yıldız widget'ıyla birebir aynı
        // sayılar. Oy yoksa alan basılmaz; sahte 5.0 ilan edilmez.
        // Kaydedilmemiş (bellek içi) tarifte tablo sorgulanmaz: exists
        // kontrolü hem ratings hem aşağıdaki recipeIngredients erişimini
        // korur (DB'siz testler ve factory make() akışları).
        $oyOzeti = $recipe->exists ? $recipe->ratingSummary() : ['average' => null, 'count' => 0];
        // Yıldız ancak en az beş farklı ağdan (ip_hash) oy gelmişse basılır:
        // bir iki oy, ya da tek kişinin çerez silerek verdiği oylar Google'a
        // tarifin genel değerlendirmesi diye bildirilmez.
        $farkliSecmen = 0;
        if ($oyOzeti['count'] >= 5) {
            $farkliSecmen = $recipe->relationLoaded('ratings')
                ? $recipe->ratings->pluck('ip_hash')->unique()->count()
                : (int) $recipe->ratings()->distinct()->count('ip_hash');
        }
        if ($farkliSecmen >= 5 && $oyOzeti['average'] !== null) {
            $recipeNode['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($oyOzeti['average'], 1, '.', ''),
                'reviewCount' => (string) $oyOzeti['count'],
                'bestRating' => '5',
            ];
        }

        // Şef ipuçları + malzeme ikameleri: sayfadaki görünür GEO kutusundaki
        // metnin aynısı. CreativeWork.comment geçerli bir alandır ve görünen
        // içerikle birebir eşleşir.
        // İpucu ve ikame metinleri yalnız Türkçe tutuluyor; yabancı dildeki
        // şemaya Türkçe cümle (" yoksa: ") girmesin diye yalnız kaynak dilde.
        $yorumlar = [];
        $kaynakDil = $locale === (string) config('locales.default', 'tr');
        foreach ($kaynakDil ? (array) ($recipe->tips ?? []) : [] as $ipucu) {
            if (is_string($ipucu) && trim($ipucu) !== '') {
                $yorumlar[] = ['@type' => 'Comment', 'text' => trim($ipucu)];
            }
        }
        if ($kaynakDil && $recipe->exists) {
            foreach ($recipe->recipeIngredients as $kalem) {
                // recipeIngredients HasMany'dir, pivot yoktur: ikame doğrudan
                // kalem sütunundadır. Görünür GEO kutusundaki metnin aynısı.
                $ikame = $kalem->substitution ?? null;
                $ad = $kalem->ingredient?->name ?? $kalem->group_name ?? null;
                if (is_string($ikame) && trim($ikame) !== '' && is_string($ad) && trim($ad) !== '') {
                    $yorumlar[] = ['@type' => 'Comment', 'text' => trim($ad).' yoksa: '.trim($ikame)];
                }
            }
        }
        if ($yorumlar !== []) {
            $recipeNode['comment'] = array_slice($yorumlar, 0, 20);
        }

        $baseSlug = preg_replace('/-[A-Za-z0-9]{5}$/', '', $recipe->slug);
        $entity = self::HERITAGE_ENTITIES[$recipe->slug]
            ?? self::HERITAGE_ENTITIES[$baseSlug]
            ?? (str_ends_with($baseSlug, 'i') ? (self::HERITAGE_ENTITIES[substr($baseSlug, 0, -1)] ?? null) : null)
            ?? (self::HERITAGE_ENTITIES[$baseSlug.'i'] ?? null);

        if ($entity !== null) {
            // Düğümün kendisindeki sameAs "bu sayfa o varlığın ta kendisi"
            // demektir; about içindeki sameAs ise "bu sayfa o varlık HAKKINDA"
            // der. İkisi aynı şey değil ve karıştırmanın bedeli ağır.
            //
            // Şeftali kebabı tarifi gerçekten Sheftalia'dır: kimlik iddiası
            // doğru ve yabancı dilli okuyucu "sheftalia" aradığında sayfayı
            // o varlığa bağlayan tek sinyal budur. Ama hellim tost, Halloumi
            // DEĞİLDİR - hellim onun malzemesidir. Oraya kimlik iddiası
            // koymak, tarif sayfasını peynirin ansiklopedi maddesi ilan eder.
            //
            // Bu yüzden düğüm seviyesindeki bağlantı yalnız varlığın yemeğin
            // kendisi olduğu kayıtlarda basılır. Malzemeye işaret edenlerde
            // bağlantı kaybolmaz, doğru yerde - about içinde - kalır.
            if (($entity['is_dish'] ?? false) === true) {
                $recipeNode['sameAs'] = $entity['sameAs'];
            }

            if (isset($entity['about'])) {
                $recipeNode['about'] = [
                    '@type' => 'Thing',
                    'name' => $entity['about']['name'],
                    'description' => $entity['about']['description'],
                    'sameAs' => $entity['sameAs'][0],
                ];
            }
        }

        $keywords = array_filter(array_merge([
            $recipe->cuisine ? taxonomy_name($recipe->cuisine) : null,
            $recipe->category ? taxonomy_name($recipe->category) : null,
        ], $this->localizedKeywords($locale)));
        if ($keywords !== []) {
            $recipeNode['keywords'] = implode(', ', $keywords);
        }
        // Tarif bir yer değildir: her tarife Lefkoşa koordinatı basılıyordu,
        // Gaziantep katmeri ve teriyaki dahil (8 Ekim 2026 denetimi). Recipe
        // için geçerli bir alan da değil; kaldırıldı.
        $crumbs = [['name' => $this->crumbName('home', $locale), 'url' => route('home')]];
        if ($recipe->cuisine !== null && $cuisineUrl !== null) {
            $crumbs[] = ['name' => $this->crumbName('cuisines', $locale), 'url' => route('cuisines.index')];
            $crumbs[] = ['name' => taxonomy_name($recipe->cuisine), 'url' => $cuisineUrl];
        } else {
            $crumbs[] = ['name' => $this->crumbName('recipes', $locale), 'url' => route('recipes.index')];
        }
        $crumbs[] = ['name' => $recipe->translated('title'), 'url' => route('recipes.show', $recipe)];

        // Google Recipe zengin sonucu icin image zorunludur. Onayli bir ana
        // gorsel yokken eksik/gecersiz bir Recipe nesnesi ilan etmek yerine
        // sayfanin gercek breadcrumb bilgisini koruyup Recipe nesnesini basma.
        if ($imageUrl === '') {
            return $this->graph(null, $crumbs);
        }

        // speakable basılmıyor: Google yalnız ABD'deki haber yayıncıları için
        // beta olarak destekliyor; tarif ve blog sayfasında etkisi yok.
        $recipeNode['image'] = [$imageUrl];

        $graph = $this->graph($recipeNode, $crumbs);

        // VideoObject: medyada video varsa Recipe'ye bağla (Google Video zengin sonucu)
        $videoData = $this->extractVideoFromMedia($recipe);
        if ($videoData !== null) {
            $graph['@graph'][] = $videoData;
            // Recipe'nin video alanını referans göster
            foreach ($graph['@graph'] as &$node) {
                if (($node['@type'] ?? '') === 'Recipe') {
                    $node['video'] = ['@type' => 'VideoObject', '@id' => $videoData['@id']];
                    break;
                }
            }
            unset($node);
        }

        $faqQuestions = $this->generateRecipeFaqs($recipe, $locale);
        if ($faqQuestions !== []) {
            $graph['@graph'][] = [
                '@type' => 'FAQPage',
                'mainEntity' => $faqQuestions,
            ];
        }

        return $graph;
    }

    /** @return array<string,mixed> */
    /**
     * Porsiyon başı beslenme bilgisi.
     *
     * Kaynak en son geçerli beslenme kaydı. Tamlık ya da güven %80'in altındaysa
     * boş döner. 14 Eylül 2026'da yayındaki 78 dolu kaydın 17'sinin tamlığı
     * %0-20 arasındaydı; o değerler şemaya girseydi Google yanlış kalori
     * gösterirdi.
     *
     * @return array<string, string>|null
     */
    private function beslenme(Recipe $recipe, string $locale): ?array
    {
        // Kaydedilmemiş tarifin beslenme kaydı olamaz; sorgu atmak hem boşa
        // hem de veritabanı olmayan bağlamlarda (önizleme, birim testi) hata.
        if (! $recipe->exists && ! $recipe->relationLoaded('nutritionSnapshots')) {
            return null;
        }

        $kayit = $recipe->relationLoaded('nutritionSnapshots')
            ? $recipe->nutritionSnapshots->whereNull('invalidated_at')->sortByDesc('calculated_at')->first()
            : $recipe->nutritionSnapshots()->whereNull('invalidated_at')->latest('calculated_at')->first();

        if ($kayit === null || (float) $kayit->completeness_percentage < 80 || (float) $kayit->confidence_score < 80) {
            return null;
        }

        $payload = is_array($kayit->payload) ? $kayit->payload : (array) json_decode((string) $kayit->payload, true);
        $besinler = (array) ($payload['nutrients'] ?? []);

        $deger = static function (string $anahtar, string $varsayilanBirim) use ($besinler): ?string {
            $besin = $besinler[$anahtar] ?? null;

            if (! is_array($besin) || ! is_numeric($besin['per_serving'] ?? null) || (float) $besin['per_serving'] < 0) {
                return null;
            }

            $sayi = rtrim(rtrim(number_format((float) $besin['per_serving'], 1, '.', ''), '0'), '.');

            return $sayi.' '.((string) ($besin['unit'] ?? '') ?: $varsayilanBirim);
        };

        $kalori = $deger('energy_kcal', 'kcal');

        if ($kalori === null || (float) $kalori <= 0) {
            return null;
        }

        return array_filter([
            '@type' => 'NutritionInformation',
            'servingSize' => '1 '.$this->yieldUnit($locale),
            'calories' => $kalori,
            'proteinContent' => $deger('protein', 'g'),
            'carbohydrateContent' => $deger('carbohydrate', 'g'),
            'fatContent' => $deger('fat', 'g'),
            'fiberContent' => $deger('fiber', 'g'),
            'sugarContent' => $deger('sugar', 'g'),
            'sodiumContent' => $deger('sodium', 'mg'),
        ], static fn (?string $v): bool => $v !== null);
    }

    /**
     * @param  iterable<Recipe>  $recipes  Yalnız sayfada gerçekten gösterilen tarifler
     * @param  list<array{name:string,url:string}>  $crumbs
     * @return array<string,mixed>
     */
    public function collectionPage(string $name, string $description, string $url, iterable $recipes, array $crumbs): array
    {
        // Koleksiyon sayfası bir tarif ayrıntı sayfası değildir. ListItem
        // yalnız görünür sıralamayı ve hedef adresi bildirir; Recipe/image
        // ayrıntısı hedef tarif sayfasının kendi şemasının sorumluluğudur.
        $items = collect($recipes)->values()->map(fn (Recipe $recipe, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => route('recipes.show', $recipe),
        ])->all();

        return $this->graph([
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ], $crumbs);
    }

    /**
     * Alt sayfalarini listeleyen ust sayfa.
     *
     * collectionPage() Recipe bekliyor; ust sayfanin ogeleri tarif degil,
     * kendi alt koleksiyon sayfalari. Bos bir ItemList basmak yerine gercek
     * ogeleri bildiriyoruz.
     *
     * @param  list<string>  $urls
     * @param  list<array{name:string,url:string}>  $crumbs
     * @return array<string,mixed>
     */
    public function hubPage(string $name, string $description, string $url, array $urls, array $crumbs): array
    {
        $items = collect($urls)->values()->map(fn (string $adres, int $sira): array => [
            '@type' => 'ListItem',
            'position' => $sira + 1,
            'url' => $adres,
        ])->all();

        return $this->graph([
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ], $crumbs);
    }

    /** @param iterable<BlogPost> $posts @param list<array{name:string,url:string}> $crumbs @return array<string,mixed> */
    public function blogCollectionPage(string $name, string $description, string $url, iterable $posts, array $crumbs): array
    {
        $items = collect($posts)->values()->map(fn (BlogPost $post, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => route('blog.show', $post),
        ])->all();

        return $this->graph([
            '@type' => 'CollectionPage', 'name' => $name, 'description' => $description, 'url' => $url,
            'mainEntity' => ['@type' => 'ItemList', 'numberOfItems' => count($items), 'itemListElement' => $items],
        ], $crumbs);
    }

    private function yieldUnit(string $locale): string
    {
        return match ($locale) {
            'en' => 'servings',
            'de' => 'Portionen',
            'pl' => 'porcje',
            'ru' => 'порций',
            'fa' => 'پرس',
            'pt' => 'porções',
            default => 'porsiyon',
        };
    }

    private function crumbName(string $type, string $locale): string
    {
        return match ($type) {
            'home' => match ($locale) {
                'en' => 'Home',
                'de' => 'Startseite',
                'pl' => 'Strona główna',
                'ru' => 'Главная',
                'fa' => 'صفحه اصلی',
                'pt' => 'Início',
                default => 'Ana Sayfa',
            },
            'recipes' => match ($locale) {
                'en' => 'Recipes',
                'de' => 'Rezepte',
                'pl' => 'Przepisy',
                'ru' => 'Рецепты',
                'fa' => 'دستور پخت‌ها',
                'pt' => 'Receitas',
                default => 'Tarifler',
            },
            'cuisines' => match ($locale) {
                'en' => 'Cuisines',
                'de' => 'Küchen',
                'pl' => 'Kuchnie',
                'ru' => 'Кухни',
                'fa' => 'سبک‌های آشپزی',
                'pt' => 'Culinárias',
                default => 'Mutfaklar',
            },
            'blog' => 'Blog',
            default => $type,
        };
    }

    /** @return list<string> */
    private function localizedKeywords(string $locale): array
    {
        return match ($locale) {
            'en' => ['Cypriot cuisine', 'Traditional recipes', 'Mediterranean gastronomy'],
            'de' => ['Zypriotische Küche', 'Traditionelle Rezepte', 'Mediterrane Gastronomie'],
            'pl' => ['Kuchnia cypryjska', 'Tradycyjne przepisy', 'Gastronomia śródziemnomorska'],
            'ru' => ['Кипрская кухня', 'Традиционные рецепты', 'Средиземноморская гастрономия'],
            'fa' => ['آشپزی قبرسی', 'دستورهای سنتی', 'غذاهای مدیترانه‌ای'],
            'pt' => ['Culinária cipriota', 'Receitas tradicionais', 'Gastronomia mediterrânica'],
            default => ['Kıbrıs mutfağı', 'Geleneksel tarifler', 'Doğu Akdeniz mutfağı'],
        };
    }

    /**
     * Generates structured FAQs for recipes to win Google AI Overviews and Perplexity direct answers.
     *
     * @return list<array{@type: string, name: string, acceptedAnswer: array{text: string}}>
     */
    public function generateRecipeFaqs(Recipe $recipe, string $locale): array
    {
        $title = $recipe->translated('title', $locale) ?: (string) $recipe->getRawOriginal('title');
        $questions = [];
        // Saklama, ısıtma, dondurma, ipucu ve servis alanları yalnız Türkçe
        // tutuluyor. Yabancı dilde soru o dilde, cevap Türkçe basılıyordu;
        // FAQPage şemasına da öyle giriyordu. Çevirisi olmayan alan yabancı
        // dilde cevap olmaz.
        $kaynakDil = $locale === (string) config('locales.default', 'tr');

        // 1. Storage notes
        if (filled($recipe->storage_notes)) {
            $gun = is_array($recipe->storage_notes) ? data_get($recipe->storage_notes, 'buzdolabi_gun') : null;
            $uyari = is_array($recipe->storage_notes) ? (string) data_get($recipe->storage_notes, 'uyari', '') : (string) $recipe->storage_notes;

            $q = match ($locale) {
                'en' => "How should {$title} be stored and how long does it stay fresh?",
                'de' => "Wie sollte {$title} aufbewahrt werden und wie lange bleibt es frisch?",
                'pl' => "Jak przechowywać {$title} i jak długo zachowuje świeżość?",
                'ru' => "Как правильно хранить {$title} и сколько дней блюдо остается свежим?",
                'fa' => "چگونه {$title} را نگهداری کنیم و تا چند روز تازه می‌ماند؟",
                'pt' => "Como armazenar {$title} e por quanto tempo permanece fresco?",
                default => "{$title} nasıl saklanır ve kaç gün tazeliğini korur?",
            };

            $daysText = '';
            if ($gun) {
                $daysText = match ($locale) {
                    'en' => "Keeps fresh in the refrigerator for up to {$gun} days. ",
                    'de' => "Im Kühlschrank bis zu {$gun} Tage haltbar. ",
                    'pl' => "W lodówce zachowuje świeżość do {$gun} dni. ",
                    'ru' => "В холодильнике сохраняет свежесть до {$gun} дней. ",
                    'fa' => "در یخچال تا {$gun} روز تازه می‌ماند. ",
                    'pt' => "Conserva-se no frigorífico até {$gun} dias. ",
                    default => "Buzdolabında {$gun} gün boyunca tazeliğini korur. ",
                };
            }

            // Tarife özgü veri yoksa (gün sayısı ya da kaynak dildeki uyarı)
            // soru basılmaz; genel "buzdolabında saklayın" kalıbı cevap değil.
            $ans = trim($daysText.($kaynakDil ? $uyari : ''));
            if ($ans !== '') {
                $questions[] = [
                    '@type' => 'Question',
                    'name' => $q,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $ans],
                ];
            }
        }

        // 2. Reheating notes
        if ($kaynakDil && filled($recipe->reheating_notes)) {
            $q = match ($locale) {
                'en' => "What is the recommended way to reheat {$title}?",
                'de' => "Wie wärmt man {$title} am besten wieder auf?",
                'pl' => "Jak najlepiej podgrzać {$title}?",
                'ru' => "Как лучше всего разогреть {$title}?",
                'fa' => "بهترین روش برای گرم کردن مجدد {$title} چیست؟",
                'pt' => "Qual é a melhor forma de reaquecer {$title}?",
                default => "{$title} nasıl yeniden ısıtılmalıdır?",
            };
            $questions[] = [
                '@type' => 'Question',
                'name' => $q,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $recipe->reheating_notes],
            ];
        }

        // 3. Freezing notes
        if ($kaynakDil && filled($recipe->freezing_notes)) {
            $q = match ($locale) {
                'en' => "Can {$title} be frozen for later use?",
                'de' => "Kann man {$title} einfrieren?",
                'pl' => "Czy można zamrozić {$title}?",
                'ru' => "Можно ли замораживать {$title}?",
                'fa' => "آیا می‌توان {$title} را در فریزر نگهداری کرد؟",
                'pt' => "É possível congelar {$title}?",
                default => "{$title} dondurucuda saklanabilir mi?",
            };
            $questions[] = [
                '@type' => 'Question',
                'name' => $q,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $recipe->freezing_notes],
            ];
        }

        // 4. Chef tips
        if ($kaynakDil && filled($recipe->tips)) {
            $tipText = is_array($recipe->tips) ? implode(' ', array_filter($recipe->tips)) : (string) $recipe->tips;
            if (trim($tipText) !== '') {
                $q = match ($locale) {
                    'en' => "What are the essential chef tips for making {$title}?",
                    'de' => "Was sind die wichtigsten Profi-Tipps für {$title}?",
                    'pl' => "Jakie są najważniejsze wskazówki szefa kuchni dla {$title}?",
                    'ru' => "Каковы главные кулинарные советы шеф-повара для {$title}?",
                    'fa' => "مهم‌ترین نکات طلایی سرآشپز برای تهیه {$title} چیست؟",
                    'pt' => "Quais são as principais dicas do chef para fazer {$title}?",
                    default => "{$title} yaparken dikkat edilmesi gereken şef püf noktaları nelerdir?",
                };
                $questions[] = [
                    '@type' => 'Question',
                    'name' => $q,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $tipText],
                ];
            }
        }

        // 5. Serving suggestions
        if ($kaynakDil && filled($recipe->serving_suggestions)) {
            $q = match ($locale) {
                'en' => "How should {$title} be served and paired?",
                'de' => "Wie und womit serviert man {$title} am besten?",
                'pl' => "Z czym i jak najlepiej podawać {$title}?",
                'ru' => "С чем и как лучше всего подавать {$title}?",
                'fa' => "{$title} چگونه و همراه با چه مواردی باید سرو شود؟",
                'pt' => "Como e com quais acompanhamentos servir {$title}?",
                default => "{$title} ne ile ve nasıl servis edilmelidir?",
            };
            $questions[] = [
                '@type' => 'Question',
                'name' => $q,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $recipe->serving_suggestions],
            ];
        }

        // Kalıp yedek sorular (süre, porsiyon, genel saklama) kaldırıldı.
        // Her tarife aynı cümleyi basıyordu; başlığı "Recipe" ile biten
        // tarifte "…Recipe recipe make?" gibi bozuk metin çıkıyordu (8 Ekim
        // 2026 denetimi). Süre ve porsiyon zaten Recipe düğümünde; SSS yalnız
        // tarife özgü veri varsa basılır.
        return $questions;
    }

    /**
     * Tarif medyasından video verisi çıkarır.
     *
     * Media modelinde type=video veya mime_type video içeren kayıtları
     * VideoObject şemasına dönüştürür. YouTube/Vimeo URL'leri doğrudan
     * embed_url olarak kullanılır.
     *
     * @return array<string,mixed>|null
     */
    private function extractVideoFromMedia(Recipe $recipe): ?array
    {
        // Kaydedilmemiş tarifin medyası olamaz. Bu kontrol yokken medya
        // tablosuna sorgu gidiyordu; veritabanı olmayan bağlamda (önizleme,
        // birim testi) hata verdi ve yayın kapısını durdurdu.
        if (! $recipe->exists && ! $recipe->relationLoaded('media')) {
            return null;
        }

        if (! $recipe->relationLoaded('media')) {
            $recipe->load('media');
        }

        $videoMedia = $recipe->media
            ->first(fn ($m) => ($m->kind ?? null) === 'video' || str_starts_with((string) ($m->mime_type ?? ''), 'video/'));

        if ($videoMedia === null) {
            return null;
        }

        $url = $videoMedia->embed_url ?: ($videoMedia->source_url ?: ($videoMedia->publicUrl() ?: ''));
        if ($url === '') {
            return null;
        }

        // YouTube ve Vimeo embed URL'lerini tanır
        $embedUrl = $url;
        if (preg_match('#youtube\.com/watch\?v=([a-zA-Z0-9_-]+)#', $url, $m)) {
            $embedUrl = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]+)#', $url, $m)) {
            $embedUrl = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('#vimeo\.com/(\d+)#', $url, $m)) {
            $embedUrl = 'https://player.vimeo.com/video/'.$m[1];
        }

        return [
            '@type' => 'VideoObject',
            '@id' => $url.'#video',
            'name' => $recipe->translated('title').' - Tarif Videosu',
            'description' => $recipe->translated('description') ?: $recipe->translated('excerpt'),
            'thumbnailUrl' => $videoMedia->publicUrl() ?: $url,
            'uploadDate' => $recipe->published_at?->toAtomString(),
            'duration' => 'PT'.max(1, (int) ceil(($recipe->prep_minutes + $recipe->cook_minutes) / 5)).'M',
            'contentUrl' => $url,
            'embedUrl' => $embedUrl,
            'publisher' => $this->organizationNode(),
        ];
    }

    /**
     * Organization şema düğümü - E-E-A-T ve Knowledge Graph için.
     *
     * sameAs ile sosyal medya profilleri, logonun bulunduğu yer ve
     * sitenin kimliği tek bir tutarlı düğümde birleşir.
     */
    private function organizationNode(): array
    {
        $org = [
            '@type' => 'Organization',
            // Kimlik dilden bağımsız tek değer: route('home') /en, /de ...
            // üretiyordu ve aynı kuruluş yedi ayrı düğüm gibi görünüyordu.
            '@id' => url('/').'#organization',
            'name' => config('brand.name'),
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url(config('brand.assets.mark_light')),
            ],
            'areaServed' => ['Cyprus', 'Northern Cyprus', 'TR', 'EU', 'Global'],
            'knowsAbout' => [
                'Cypriot Cuisine',
                'Mediterranean Diet',
                'Hellim / Halloumi Cheese',
                'Traditional Turkish Recipes',
                'Metric Kitchen Measurement Conversions',
                'Pantry Recipe Recommendations',
            ],
        ];

        // Sosyal medya profilleri - Knowledge Graph bağlamı için sameAs
        $socialProfiles = array_filter([
            config('brand.social.twitter'),
            config('brand.social.instagram'),
            config('brand.social.facebook'),
            config('brand.social.youtube'),
            config('brand.social.tiktok'),
            config('brand.social.pinterest'),
        ]);

        if ($socialProfiles !== []) {
            $org['sameAs'] = array_values($socialProfiles);
        }

        return $org;
    }

    /**
     * Site geneli WebSite + Organization şeması.
     *
     * @return array<string,mixed>
     */
    public function webSite(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->organizationNode(),
                // SearchAction yok: Google site arama kutusunu Kasım 2024'te
                // kaldırdı, işaretleme artık hiçbir sonuç üretmiyor.
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'name' => config('brand.name'),
                    'url' => url('/'),
                    'publisher' => ['@id' => url('/').'#organization'],
                ],
            ],
        ];
    }

    /**
     * Site navigasyon yapısı - crawl bütçesi ve site hiyerarşisi için.
     *
     * @param  list<array{title: string, url: string, children?: list<array{title: string, url: string}>}>  $navItems
     * @return array<string,mixed>
     */
    public function siteNavigation(array $navItems): array
    {
        $elements = [];
        foreach ($navItems as $item) {
            $elements[] = [
                '@type' => 'SiteNavigationElement',
                'name' => $item['title'],
                'url' => $item['url'],
            ];
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    $elements[] = [
                        '@type' => 'SiteNavigationElement',
                        'name' => $child['title'],
                        'url' => $child['url'],
                    ];
                }
            }
        }

        return ['@context' => 'https://schema.org', '@graph' => $elements];
    }

    /**
     * Blog yazısı için Article şemasını zenginleştirir.
     *
     * wordCount tahmini ve publisher alanlarını ekler.
     */
    public function article(BlogPost $post, string $imageUrl = ''): array
    {
        $publisher = $this->organizationNode();

        $locale = app()->getLocale();
        $bodyText = (string) $post->body;
        $wordCount = str_word_count(strip_tags(Str::markdown($bodyText)));

        $article = [
            '@type' => 'BlogPosting',
            'headline' => $post->translated('title'),
            'description' => $post->translated('excerpt'),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $post),
            ],
            'author' => filled($post->author?->name)
                ? ['@type' => 'Person', 'name' => $post->author->name]
                : $publisher,
            'publisher' => $publisher,
            'inLanguage' => $locale,
            'wordCount' => $wordCount,
            'articleSection' => $post->category?->name,
        ];

        if ($imageUrl !== '') {
            $article['image'] = [$imageUrl];
        }

        $graph = $this->graph($article, [
            ['name' => $this->crumbName('home', $locale), 'url' => route('home')],
            ['name' => 'Blog', 'url' => route('blog.index')],
            ['name' => $post->translated('title'), 'url' => route('blog.show', $post)],
        ]);
        $faqBlocks = $post->getAttribute('faq_blocks');
        $questions = collect(is_array($faqBlocks) ? $faqBlocks : [])->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))->map(fn (array $faq): array => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]])->values()->all();
        if ($questions !== []) {
            $graph['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $questions];
        }

        return $graph;
    }

    /** @param array<string,mixed>|null $node @param list<array{name:string,url:string}> $crumbs @return array<string,mixed> */
    private function graph(?array $node, array $crumbs): array
    {
        $graph = $node === null ? [] : [$node];
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $index): array => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['name'], 'item' => $crumb['url']])->all(),
        ];

        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }
}
