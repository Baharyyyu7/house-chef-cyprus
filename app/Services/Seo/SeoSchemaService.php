<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

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
            'mainEntityOfPage' => route('recipes.show', $recipe),
            'inLanguage' => $locale,
            'dateModified' => $recipe->updated_at?->toAtomString(),
            'recipeCuisine' => $recipe->cuisine?->name,
            'recipeCategory' => $recipe->category?->name,
            'prepTime' => 'PT'.$recipe->prep_minutes.'M', 'cookTime' => 'PT'.$recipe->cook_minutes.'M',
            'totalTime' => 'PT'.$recipe->totalMinutes().'M', 'recipeYield' => $recipe->servings.' '.$this->yieldUnit($locale),
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $recipe->steps->values()->map(fn ($step, int $index): array => [
                '@type' => 'HowToStep',
                'name' => $step->title,
                'text' => $step->instruction,
                'url' => route('recipes.show', $recipe).'#step-'.($step->position ?: $index + 1),
            ])->all(),
            // author ve datePublished hiçbir tarifte yoktu. İkisi de Google'ın
            // önerdiği alanlar ve author E-E-A-T tarafında doğrudan sayılıyor -
            // sitenin bütün konumlanması "doğrulanmış tarifler" üzerine ama
            // içeriğin arkasında kimin durduğu söylenmiyordu.
            'author' => [
                '@type' => 'Organization',
                'name' => config('brand.name'),
                'url' => route('home'),
            ],
            'datePublished' => $recipe->published_at?->toAtomString(),
        ];

        // Beslenme bilgisi Google'ın tarif sonucunda kalori olarak görünür.
        // 236 geçerli beslenme kaydı vardı ama şema hiçbirini okumuyordu.
        // Yalnız tamlığı ve güveni yüksek kayıt basılır: eksik malzemeyle
        // hesaplanmış bir kalori, hiç kalori göstermemekten daha zararlıdır.
        $beslenme = $this->beslenme($recipe, $locale);
        if ($beslenme !== null) {
            $recipeNode['nutrition'] = $beslenme;
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
            $recipe->cuisine?->name,
            $recipe->category?->name,
        ], $this->localizedKeywords($locale)));
        if ($keywords !== []) {
            $recipeNode['keywords'] = implode(', ', $keywords);
        }
        $crumbs = [['name' => $this->crumbName('home', $locale), 'url' => route('home')]];
        if ($recipe->cuisine !== null && $cuisineUrl !== null) {
            $crumbs[] = ['name' => $this->crumbName('cuisines', $locale), 'url' => route('cuisines.index')];
            $crumbs[] = ['name' => $recipe->cuisine->name, 'url' => $cuisineUrl];
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

        $recipeNode['image'] = [$imageUrl];

        return $this->graph($recipeNode, $crumbs);
    }

    /** @return array<string,mixed> */
    public function article(BlogPost $post, string $imageUrl = ''): array
    {
        $publisher = [
            '@type' => 'Organization',
            'name' => config('brand.name'),
            'logo' => url(config('brand.assets.mark_light')),
        ];

        $locale = app()->getLocale();
        $article = ['@type' => 'BlogPosting', 'headline' => $post->translated('title'), 'description' => $post->translated('excerpt'), 'datePublished' => $post->published_at?->toAtomString(), 'dateModified' => $post->updated_at?->toAtomString(), 'mainEntityOfPage' => route('blog.show', $post), 'author' => $publisher, 'publisher' => $publisher, 'inLanguage' => $locale];
        if (filled($post->author?->name)) {
            $article['author'] = ['@type' => 'Person', 'name' => $post->author->name];
        }
        if ($imageUrl !== '') {
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
