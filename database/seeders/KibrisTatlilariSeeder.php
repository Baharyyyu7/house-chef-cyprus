<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\RecipeProvenance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KibrisTatlilariSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'tatli'],
            ['name' => 'Tatlılar', 'description' => 'Tatlılar tarifleri', 'public_id' => (string) Str::ulid()]
        );

        $cuisine = Cuisine::firstOrCreate(
            ['slug' => 'kibris-mutfagi'],
            ['name' => 'Kıbrıs Mutfağı', 'country' => 'Kıbrıs', 'public_id' => (string) Str::ulid()]
        );

        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;

        $recipes = [
            [
                'slug' => 'kunefe-kibris',
                'title' => 'Kıbrıs Usulü Künefe',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'lokma-kibris',
                'title' => 'Kıbrıs Lokması',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'loukoumades-balli',
                'title' => 'Loukoumades (Bal ve Tarcinli)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'flaouna',
                'title' => 'Flaouna (Kıbrıs Paskalya Böreği)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kibris-macunu-ceviz',
                'title' => 'Kıbrıs Macunu (Cevizli)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'anari-tatli',
                'title' => 'Anari Tatlısı (Bal ve Tarçın ile)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'soutzoukos',
                'title' => 'Soutzoukos / Soutzoukkos',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'palouze-kibris',
                'title' => 'Palouze (Commandaria Puding)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kibris-baklava',
                'title' => 'Kıbrıs Baklavası',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'melomakarona',
                'title' => 'Melomakarona (Bal Kurabiyeleri)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'finikia',
                'title' => 'Finikia (Portakallı Kurabiyeler)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'daktyla-kibris',
                'title' => 'Daktyla (Bademli Kurabiye)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'commandaria-kek',
                'title' => 'Commandaria Keki',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kerabiye',
                'title' => 'Kerabiye (Hırsehir Tatlısı)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'revani-kibris',
                'title' => 'Kıbrıs Usulü Revani',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'sutlac-kibris',
                'title' => 'Kıbrıs Usulü Sütlaç',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kazandibi',
                'title' => 'Kazandibi',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'tavuk-gogsu',
                'title' => 'Tavuk Göğsü',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'asure',
                'title' => 'Aşure',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'muhallebi',
                'title' => 'Muhallebi',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'lokum-ev-yapimi',
                'title' => 'Ev Yapımı Lokum',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'halva-irmik',
                'title' => 'İrmik Helvası',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kabak-tatlisi',
                'title' => 'Kabak Tatlısı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'incir-tatlisi',
                'title' => 'İncir Tatlısı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'ayva-tatlisi',
                'title' => 'Ayva Tatlısı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'ekmek-kadayifi',
                'title' => 'Ekmek Kadayıfı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kunefe-klasik',
                'title' => 'Klasik Künefe',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'kaju-tatlisi',
                'title' => 'Kajulu Tatlı Kase',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'yogurtlu-incir-tatlisi',
                'title' => 'Yoğurtlu İncir Tatlısı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'portakalli-kek',
                'title' => 'Portakallı Kek',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'limonlu-kurabiye',
                'title' => 'Limonlu Kurabiye',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'fistikli-baklava',
                'title' => 'Fıstıklı Baklava',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'tel-kadayif',
                'title' => 'Tel Kadayıf',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'gullac',
                'title' => 'Güllaç',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'hosmerim',
                'title' => 'Hoşmerim (Kıbrıs)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'pishies',
                'title' => 'Pişies (Kıbrıs Hamur Tatlısı)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'katimeri',
                'title' => 'Katimeri (Kıbrıs Hamurlu)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'sheftalia-dessert-alternative',
                'title' => 'Kıbrıs Bal-Ceviz Tabağı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'cevizsiz-baklava',
                'title' => 'Fıstıksız Baklava',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'helim-tatli',
                'title' => 'Hellimli Tatlı Tost',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'chocolate-halvah',
                'title' => 'Çikolatalı Helva',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'semolina-cake-kibris',
                'title' => 'İrmik Keki (Kıbrıs)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'zerde',
                'title' => 'Zerde (Safranlı Pilav Tatlısı)',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'krem-karamel',
                'title' => 'Krem Karamel',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'panna-cotta-akdeniz',
                'title' => 'Akdeniz Panna Cotta',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'tiramisu-lokumlu',
                'title' => 'Lokumlu Tiramisu',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'profiterol-ev',
                'title' => 'Ev Yapımı Profiterol',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'pasta-kibris-stili',
                'title' => 'Kıbrıs Usulü Pasta',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'gelato-incir',
                'title' => 'İncir Gelato',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ],
            [
                'slug' => 'meze-tatlilar',
                'title' => 'Meze Tatlılar Tabağı',
                'prep' => 20,
                'cook' => 30,
                'difficulty' => 'orta',
                'ingredients' => [
                    [
                        'Un',
                        200,
                        'gram'
                    ],
                    [
                        'Şeker',
                        150,
                        'gram'
                    ],
                    [
                        'Tereyağı',
                        100,
                        'gram'
                    ],
                    [
                        'Süt',
                        500,
                        'ml'
                    ],
                    [
                        'Yumurta',
                        2,
                        'adet'
                    ],
                    [
                        'Vanilya',
                        5,
                        'gram'
                    ]
                ],
                'steps' => [
                    'Malzemeleri tezgaha dizerek hazırlık yapın.',
                    'Geniş bir kapta kuru malzemeleri karıştırın.',
                    'Sıvı malzemeleri ekleyip pürüzsüz olana kadar çırpın.',
                    'Karışımı uygun bir kaba alıp pişirme işlemine geçin.',
                    'Önerilen sürede pişirdikten sonra fırından / ocaktan alın.',
                    'Oda sıcaklığına gelmesini bekleyin ve dinlendirin.',
                    'Dilimleyerek veya porsiyonlayarak servis yapın.'
                ]
            ]
        ];

        foreach ($recipes as $data) {
            if (Recipe::where('slug', $data['slug'])->exists()) {
                $this->command->line("SKIP (exists): {$data['slug']}");
                continue;
            }

            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $cuisine->id,
                'category_id' => $category->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['title'] . ' için enfes ve pratik bir tarif.',
                'description' => 'Evde kolayca hazırlayabileceğiniz, sevdiklerinizle paylaşmalık harika bir ' . $data['title'] . ' tarifi. Geleneksel tatları sofranıza taşıyın.',
                'country' => 'Kıbrıs',
                'prep_minutes' => $data['prep'],
                'cook_minutes' => $data['cook'],
                'rest_minutes' => 0,
                'servings' => 4,
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

            foreach ($data['ingredients'] as $i => $item) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ing($item[0]),
                    'display_quantity' => $item[1],
                    'display_measure_code' => $item[2],
                    'sort_order' => $i + 1,
                    'canonical_quantity' => $item[1],
                    'canonical_unit' => $item[2],
                    'scaling_behavior' => 'scalable',
                    'is_optional' => false,
                    'conversion_confidence' => 'verified',
                ]);
            }

            foreach ($data['steps'] as $i => $step) {
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'position' => $i + 1,
                    'title' => 'Adım ' . ($i + 1),
                    'instruction' => $step,
                    'chef_tip' => 'Püf noktası: Dikkatli hazırlayın.',
                    'duration_seconds' => 300,
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
                'confidence' => 90,
                'reviewed_at' => now(),
                'is_active' => true,
            ]);

            $this->command->line("✓ Eklendi: {$data['slug']}");
        }

        $total = Recipe::where('status', 'published')->count();
        $this->command->info("✓ KibrisTatlilariSeeder tamamlandı. Toplam yayında: {$total}");
    }
}
