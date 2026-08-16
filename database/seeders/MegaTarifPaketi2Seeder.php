<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\RecipeProvenance;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;

class MegaTarifPaketi2Seeder extends Seeder
{
    public function run(): void
    {
        $turk = Cuisine::firstOrCreate(
            ['slug' => 'turk-mutfagi'],
            ['name' => 'Türk Mutfağı', 'country' => 'Türkiye', 'public_id' => (string) Str::ulid()]
        );
        
        $kibris = Cuisine::firstOrCreate(
            ['slug' => 'kibris-mutfagi'],
            ['name' => 'Kıbrıs Mutfağı', 'country' => 'Kıbrıs', 'public_id' => (string) Str::ulid()]
        );

        $categories = [
            'corba' => Category::firstOrCreate(['slug' => 'corba'], ['name' => 'Çorbalar', 'description' => 'Çorba Tarifleri', 'public_id' => (string) Str::ulid()]),
            'ana-yemek' => Category::firstOrCreate(['slug' => 'ana-yemek'], ['name' => 'Ana Yemekler', 'description' => 'Ana Yemek Tarifleri', 'public_id' => (string) Str::ulid()]),
            'zeytinyagli-sebze' => Category::firstOrCreate(['slug' => 'zeytinyagli-sebze'], ['name' => 'Zeytinyağlılar ve Sebzeler', 'description' => 'Zeytinyağlı Tarifleri', 'public_id' => (string) Str::ulid()]),
            'salata-meze' => Category::firstOrCreate(['slug' => 'salata-meze'], ['name' => 'Salata ve Mezeler', 'description' => 'Salata ve Meze Tarifleri', 'public_id' => (string) Str::ulid()]),
            'hamur-isi' => Category::firstOrCreate(['slug' => 'hamur-isi'], ['name' => 'Hamur İşleri', 'description' => 'Hamur İşi Tarifleri', 'public_id' => (string) Str::ulid()]),
            'tatli' => Category::firstOrCreate(['slug' => 'tatli'], ['name' => 'Tatlılar', 'description' => 'Tatlı Tarifleri', 'public_id' => (string) Str::ulid()]),
        ];

        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;


        if (!Recipe::where('slug', 'mercimek-kofte-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Mahluta Çorbası',
                'slug' => 'mercimek-kofte-corbasi',
                'excerpt' => 'Kıbrıs\'ın meşhur mahluta çorbası.',
                'description' => 'Kırmızı mercimek ve pirinç ile hazırlanan enfes bir yöresel çorba.',
                'country' => 'Kıbrıs',
                'prep_minutes' => 15,
                'cook_minutes' => 40,
                'rest_minutes' => 0,
                'servings' => 6,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kırmızı Mercimek'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Pirinç'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'çay bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '6',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kimyon'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Mercimek ve pirinci yıkayıp tencereye alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Üzerine suyu ekleyip kaynamaya bırakın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Ayrı bir tavada yemeklik doğranmış soğanı zeytinyağında kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kavrulan soğanı çorbaya ilave edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Baharatları ekleyip blenderdan geçirmeden taneli olarak servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: mercimek-kofte-corbasi");
        } else {
            $this->command->line("SKIP (exists): mercimek-kofte-corbasi");
        }

        if (!Recipe::where('slug', 'tavuklu-eriste-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Tavuklu Erişte Çorbası',
                'slug' => 'tavuklu-eriste-corbasi',
                'excerpt' => 'Şifa deposu tavuklu erişte çorbası.',
                'description' => 'Tavuk suyu ve erişte ile hazırlanan doyurucu çorba.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 30,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tavuk Göğsü'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Erişte'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Havuç'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tavuk Suyu'),
                'display_quantity' => '5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Karabiber'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Tavuk göğsünü haşlayıp didikleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Havucu rendeleyip tereyağında soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Tavuk suyunu ekleyip kaynamaya bırakın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kaynayan suya erişte ve didiklenmiş tavukları ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Erişteler yumuşayana kadar pişirip sıcak servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: tavuklu-eriste-corbasi");
        } else {
            $this->command->line("SKIP (exists): tavuklu-eriste-corbasi");
        }

        if (!Recipe::where('slug', 'sogan-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Soğan Çorbası',
                'slug' => 'sogan-corbasi',
                'excerpt' => 'Karamelize soğanlı enfes çorba.',
                'description' => 'Fransız usulüne benzer, peynirli soğan çorbası.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 45,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '1',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Et Suyu'),
                'display_quantity' => '5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kaşar Peyniri'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (rendelenmiş)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Soğanları piyazlık doğrayıp tereyağında karamelize olana kadar kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Unu ekleyip kokusu çıkana kadar kavurmaya devam edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Et suyunu azar azar ekleyip karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kısık ateşte 20 dakika kaynatın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine peynir serpilmiş ekmek dilimleri ile fırınlayıp servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: sogan-corbasi");
        } else {
            $this->command->line("SKIP (exists): sogan-corbasi");
        }

        if (!Recipe::where('slug', 'havuc-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Havuç Çorbası',
                'slug' => 'havuc-corbasi',
                'excerpt' => 'Rengiyle iştah açan havuç çorbası.',
                'description' => 'Kremamsı dokusuyla nefis bir sebze çorbası.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 25,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Havuç'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patates'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Sebzeleri küp küp doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Zeytinyağında soğanı soteleyip havuç ve patatesi ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Suyu ilave edip sebzeler yumuşayana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Blenderdan geçirip pürüzsüz kıvam elde edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine krema veya dereotu ekleyerek servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: havuc-corbasi");
        } else {
            $this->command->line("SKIP (exists): havuc-corbasi");
        }

        if (!Recipe::where('slug', 'brokoli-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Brokoli Çorbası',
                'slug' => 'brokoli-corbasi',
                'excerpt' => 'Sağlıklı ve pratik brokoli çorbası.',
                'description' => 'Sütlü ve kremamsı nefis brokoli çorbası.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Brokoli'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süt'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '4',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Brokolileri çiçeklerine ayırıp suda haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Ayrı bir tencerede tereyağı ve unu kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Haşlama suyundan ve sütten ekleyerek çırpın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Brokolileri ekleyip blenderdan geçirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Birkaç dakika kaynatıp ocaktan alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: brokoli-corbasi");
        } else {
            $this->command->line("SKIP (exists): brokoli-corbasi");
        }

        if (!Recipe::where('slug', 'kremali-patates-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Kremalı Patates Çorbası',
                'slug' => 'kremali-patates-corbasi',
                'excerpt' => 'Yumuşacık içimli patates çorbası.',
                'description' => 'Krema ile zenginleştirilmiş nefis patates çorbası.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 30,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patates'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Krema'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '1',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tavuk Suyu'),
                'display_quantity' => '4',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Karabiber'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Patatesleri soyup küp küp doğrayın ve haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Haşlanan patatesleri püre haline getirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Tavuk suyunu ekleyip kaynamaya bırakın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kremayı ekleyip karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Tuz ve baharatları ayarlayıp sıcak servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kremali-patates-corbasi");
        } else {
            $this->command->line("SKIP (exists): kremali-patates-corbasi");
        }

        if (!Recipe::where('slug', 'pacar-corbasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['corba']->id,
                'title' => 'Pancar Çorbası',
                'slug' => 'pacar-corbasi',
                'excerpt' => 'Rengiyle büyüleyen pancar çorbası.',
                'description' => 'Farklı ve besleyici kırmızı pancar çorbası.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 35,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kırmızı Pancar'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patates'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Havuç'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Limon Suyu'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Pancarları soyup rendeleyin veya doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Diğer sebzeleri de doğrayıp tencereye alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Su ekleyip sebzeler yumuşayana kadar haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Blenderdan geçirip pürüzsüz yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Limon suyu ve tuz ekleyip servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: pacar-corbasi");
        } else {
            $this->command->line("SKIP (exists): pacar-corbasi");
        }

        if (!Recipe::where('slug', 'adana-kebab')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Adana Kebabı',
                'slug' => 'adana-kebab',
                'excerpt' => 'Ev yapımı Adana kebap lezzeti.',
                'description' => 'Zırhla çekilmiş kıymadan orijinal Adana kebap tarifi.',
                'country' => 'Türkiye',
                'prep_minutes' => 30,
                'cook_minutes' => 20,
                'rest_minutes' => 60,
                'servings' => 4,
                'difficulty' => 'zor',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuzu Kıyması'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram (Zırh kıyması)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuyruk Yağı'),
                'display_quantity' => '100',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kapya Biber'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Pul Biber'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kapya biberi çok ince doğrayıp suyunu sıkın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kıyma, kuyruk yağı, biber ve baharatları geniş bir kaba alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Malzemeleri iyice yoğurarak özdeşleştirin ve buzdolabında 1 saat dinlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Dinlenen harcı geniş şişlere yassı şekilde saplayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Mangalda veya fırın ızgarasında pişirip lavaşla servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: adana-kebab");
        } else {
            $this->command->line("SKIP (exists): adana-kebab");
        }

        if (!Recipe::where('slug', 'sis-kebab')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Şiş Kebab',
                'slug' => 'sis-kebab',
                'excerpt' => 'Lokum gibi kuzu şiş kebap.',
                'description' => 'Özel terbiyesiyle yumuşacık şiş kebap.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 15,
                'rest_minutes' => 120,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuzu Kuşbaşı'),
                'display_quantity' => '600',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süt'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Soğan Suyu'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kekik'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yeşil Biber'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Etleri zeytinyağı, süt, soğan suyu ve kekik ile karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Karışımı buzdolabında en az 2 saat marine edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Dinlenen etleri, aralarına domates ve biber dizerek şişlere geçirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Izgarada veya mangalda çevirerek pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Sıcak olarak pide üzerinde servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: sis-kebab");
        } else {
            $this->command->line("SKIP (exists): sis-kebab");
        }

        if (!Recipe::where('slug', 'beyti-sarma')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Beyti Sarma',
                'slug' => 'beyti-sarma',
                'excerpt' => 'Lavaşlı, soslu nefis beyti kebap.',
                'description' => 'Evde yapabileceğiniz restorant usulü beyti.',
                'country' => 'Türkiye',
                'prep_minutes' => 30,
                'cook_minutes' => 30,
                'rest_minutes' => 30,
                'servings' => 4,
                'difficulty' => 'zor',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kıyma'),
                'display_quantity' => '400',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Lavaş'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sarımsak'),
                'display_quantity' => '2',
                'display_measure_code' => 'diş',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates Salçası'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yoğurt'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kıymayı sarımsak, tuz ve baharatlarla yoğurup uzun kebap şekli verin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Fırında veya tavada pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Pişen kebabı lavaşa sarıp dilimleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Dilimleri fırın tepsisine dizip kısa süre ısıtın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine salçalı tereyağlı sos ve yoğurt gezdirerek servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: beyti-sarma");
        } else {
            $this->command->line("SKIP (exists): beyti-sarma");
        }

        if (!Recipe::where('slug', 'iskender-kebab')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'İskender Kebabı',
                'slug' => 'iskender-kebab',
                'excerpt' => 'Evde İskender kebap keyfi.',
                'description' => 'Döner etinden nefis tereyağlı İskender kebabı.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Hazır Yaprak Döner'),
                'display_quantity' => '300',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tırnak Pide'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates Salçası'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yoğurt'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Pideleri küp küp doğrayıp fırında veya tavada ısıtın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Tavada tereyağını eritip salçayı ve biraz suyu ekleyerek sos hazırlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Dönerleri ayrı bir tavada ısıtın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Tabağa pideleri dizin, üzerine sos gezdirin, dönerleri yerleştirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Yanına yoğurt koyup kızdırılmış tereyağı dökerek servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: iskender-kebab");
        } else {
            $this->command->line("SKIP (exists): iskender-kebab");
        }

        if (!Recipe::where('slug', 'cop-sis')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Çöp Şiş',
                'slug' => 'cop-sis',
                'excerpt' => 'Pratik ve lezzetli çöp şiş.',
                'description' => 'Küçük doğranmış etlerle hazırlanan hızlı kebap.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 10,
                'rest_minutes' => 60,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Antrikot'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Toz Kırmızı Biber'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kekik'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Etleri çok küçük (fındık büyüklüğünde) doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Yağ ve baharatlarla harmanlayıp 1 saat dinlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Etleri çöp şişlere dizin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kızgın döküm tavada veya mangalda hızlıca pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Lavaş ve soğan piyazı ile servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: cop-sis");
        } else {
            $this->command->line("SKIP (exists): cop-sis");
        }

        if (!Recipe::where('slug', 'et-sote')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Et Sote',
                'slug' => 'et-sote',
                'excerpt' => 'Lokum gibi sebzeli et sote.',
                'description' => 'Kuşbaşı et ve sebzelerin mükemmel uyumu.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 40,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kuşbaşı'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yeşil Biber'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Etleri kızgın tavada suyunu salıp çekene kadar soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Sıvı yağı ve doğranmış soğanları ekleyip kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Biberleri ilave edin ve birkaç dakika daha kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Küp doğranmış domatesleri ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kapağını kapatıp etler yumuşayana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: et-sote");
        } else {
            $this->command->line("SKIP (exists): et-sote");
        }

        if (!Recipe::where('slug', 'kuzu-incik')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Fırında Kuzu İncik',
                'slug' => 'kuzu-incik',
                'excerpt' => 'Ağızda dağılan fırın incik.',
                'description' => 'Sebzelerle ağır ağır fırında pişen kuzu incik.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 120,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuzu İncik'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patates'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Havuç'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sarımsak'),
                'display_quantity' => '4',
                'display_measure_code' => 'diş',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Biberiye'),
                'display_quantity' => '2',
                'display_measure_code' => 'dal',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "İncikleri kızgın yağda mühürleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Fırın kabına alınan inciklerin yanına doğranmış sebzeleri ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Zeytinyağı, sarımsak, tuz ve biberiye ilave edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Üzerini ıslak yağlı kağıt ve folyo ile kapatın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Önceden ısıtılmış 180 derece fırında 2 saat pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kuzu-incik");
        } else {
            $this->command->line("SKIP (exists): kuzu-incik");
        }

        if (!Recipe::where('slug', 'tavuk-kanat')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Baharatlı Tavuk Kanat',
                'slug' => 'tavuk-kanat',
                'excerpt' => 'Fırında çıtır çıtır tavuk kanat.',
                'description' => 'Özel sosuyla fırında nar gibi kızaran kanatlar.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 45,
                'rest_minutes' => 30,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tavuk Kanat'),
                'display_quantity' => '1',
                'display_measure_code' => 'kilogram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yoğurt'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates Salçası'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kekik'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Toz Kırmızı Biber'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kanatları yıkayıp kurulayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Geniş bir kapta yoğurt, salça, yağ ve baharatları karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kanatları sosa bulayıp 30 dakika dinlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Fırın tepsisine dizip 200 derece fırında kızarana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Sıcak servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: tavuk-kanat");
        } else {
            $this->command->line("SKIP (exists): tavuk-kanat");
        }

        if (!Recipe::where('slug', 'sac-kavurma')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Saç Kavurma',
                'slug' => 'sac-kavurma',
                'excerpt' => 'Sacda enfes et kavurma.',
                'description' => 'Sac tavada hızlıca pişen lokum gibi kavurma.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 30,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuzu Kuşbaşı'),
                'display_quantity' => '600',
                'display_measure_code' => 'gram (küçük doğranmış)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuyruk Yağı'),
                'display_quantity' => '50',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yeşil Biber'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Sac tavayı ısıtıp kuyruk yağını eritin, kıkırdaklarını alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Etleri ekleyip yüksek ateşte suyunu salıp çekene kadar kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Soğanları ve biberleri ekleyip kavurmaya devam edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kabukları soyulmuş domatesleri ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Tuz ve baharatları ilave edip domatesler suyunu çekene kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: sac-kavurma");
        } else {
            $this->command->line("SKIP (exists): sac-kavurma");
        }

        if (!Recipe::where('slug', 'kibris-molohiya')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Kıbrıs Molohiyası',
                'slug' => 'kibris-molohiya',
                'excerpt' => 'Kıbrıs\'ın geleneksel lezzeti Molohiya.',
                'description' => 'Kuzu eti ve kurutulmuş molohiya yapraklarıyla yapılan yöresel yemek.',
                'country' => 'Kıbrıs',
                'prep_minutes' => 30,
                'cook_minutes' => 60,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'zor',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Molohiya'),
                'display_quantity' => '250',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuzu Kemikli Et'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates Salçası'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Limon Suyu'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '4',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Molohiya yapraklarını sıcak suda bekletip yıkayın ve süzün.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Tencerede etleri mühürleyip soğanları ekleyin ve kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Salçayı ilave edip kokusu çıkana kadar kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Molohiyaları ve suyu ekleyip kısık ateşte pişmeye bırakın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "İnmeye yakın limon suyunu ekleyip 10 dakika daha pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kibris-molohiya");
        } else {
            $this->command->line("SKIP (exists): kibris-molohiya");
        }

        if (!Recipe::where('slug', 'kibris-kolokas')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Kıbrıs Kolokası',
                'slug' => 'kibris-kolokas',
                'excerpt' => 'Etli Kıbrıs kolokası.',
                'description' => 'Kolokas kökü ve kuzu eti ile hazırlanan kışlık yemek.',
                'country' => 'Kıbrıs',
                'prep_minutes' => 20,
                'cook_minutes' => 50,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kolokas'),
                'display_quantity' => '1',
                'display_measure_code' => 'kilogram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuzu Eti'),
                'display_quantity' => '400',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Limon'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kereviz Sapı'),
                'display_quantity' => '2',
                'display_measure_code' => 'dal',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kolokası soymadan önce iyice silin (yıkanmaz, silinerek temizlenir) ve kırarak doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Eti zeytinyağında kavurup kereviz saplarını ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kolokas parçalarını ekleyip hafifçe soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Üzerini geçecek kadar su ve limon suyu ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Yumuşayana kadar pişirip servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kibris-kolokas");
        } else {
            $this->command->line("SKIP (exists): kibris-kolokas");
        }

        if (!Recipe::where('slug', 'tavuk-tantuni')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Tavuk Tantuni',
                'slug' => 'tavuk-tantuni',
                'excerpt' => 'Mersin usulü tavuk tantuni.',
                'description' => 'Lavaş arası enfes baharatlı tavuk sote.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tavuk Göğsü'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Toz Kırmızı Biber'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'çay bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Lavaş'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Tavuk göğsünü çok minik küpler halinde doğrayıp haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Tantuni tavasına sıvı yağ, toz biber ve az miktarda su ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Haşlanmış tavukları tavaya alıp karıştırarak soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Lavaşı tavadaki yağa batırarak ısıtın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "İçine tavuk, soğan, maydanoz koyup dürüm yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: tavuk-tantuni");
        } else {
            $this->command->line("SKIP (exists): tavuk-tantuni");
        }

        if (!Recipe::where('slug', 'pideli-kofte')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['ana-yemek']->id,
                'title' => 'Pideli Köfte',
                'slug' => 'pideli-kofte',
                'excerpt' => 'Bursa\'nın meşhur pideli köftesi.',
                'description' => 'Pide yatağında soslu ve tereyağlı enfes köfteler.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 20,
                'rest_minutes' => 30,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kıyma'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tırnak Pide'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates Salçası'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yoğurt'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kıymayı tuz, karabiber ve az miktarda su ile yoğurup yassı köfteler yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Köfteleri tavada veya ızgarada pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Pideleri doğrayıp ısıtın ve servis tabağına alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Salçalı sos hazırlayıp pidelerin üzerine gezdirin, ardından köfteleri yerleştirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Yanına yoğurt ekleyip üzerine kızgın tereyağı dökün.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: pideli-kofte");
        } else {
            $this->command->line("SKIP (exists): pideli-kofte");
        }

        if (!Recipe::where('slug', 'zeytinyagli-bamya')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Zeytinyağlı Bamya',
                'slug' => 'zeytinyagli-bamya',
                'excerpt' => 'Limonlu taze bamya.',
                'description' => 'Salyalanmadan pişen nefis zeytinyağlı bamya.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 30,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Bamya'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Limon'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Bamyaların tepelerini külah şeklinde keserek temizleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Zeytinyağında doğranmış soğanları soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Küp doğranmış domatesleri ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Bamyaları ve limon suyunu ilave edip su ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Karıştırmadan, kısık ateşte bamyalar yumuşayana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: zeytinyagli-bamya");
        } else {
            $this->command->line("SKIP (exists): zeytinyagli-bamya");
        }

        if (!Recipe::where('slug', 'kabak-dolmasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Kabak Dolması',
                'slug' => 'kabak-dolmasi',
                'excerpt' => 'Zeytinyağlı veya etli kabak dolması.',
                'description' => 'Taze kabaklarla hazırlanan nefis dolma.',
                'country' => 'Türkiye',
                'prep_minutes' => 25,
                'cook_minutes' => 40,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kabak'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Pirinç'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dereotu'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kabakların içlerini oyup hazırlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "İç harcı için pirinç, soğan, dereotu, zeytinyağı ve baharatları karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kabakları iç harçla çok sıkı olmayacak şekilde doldurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Tencereye dizip yarısına kadar su ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kısık ateşte pirinçler yumuşayana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kabak-dolmasi");
        } else {
            $this->command->line("SKIP (exists): kabak-dolmasi");
        }

        if (!Recipe::where('slug', 'patlican-musakka')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Patlıcan Musakka',
                'slug' => 'patlican-musakka',
                'excerpt' => 'Kıymalı patlıcan yemeği.',
                'description' => 'Kızarmış patlıcan ve kıyma soslu fırın yemeği.',
                'country' => 'Türkiye',
                'prep_minutes' => 25,
                'cook_minutes' => 30,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patlıcan'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kıyma'),
                'display_quantity' => '300',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sivri Biber'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Patlıcanları alacalı soyup halka halka doğrayın ve kızartın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kıymayı soğan, biber ve domatesle soteleyip harç hazırlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kızaran patlıcanları fırın tepsisine dizin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Üzerine kıymalı harcı yayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Bir miktar sıcak su ekleyip 180 derece fırında 20 dakika pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: patlican-musakka");
        } else {
            $this->command->line("SKIP (exists): patlican-musakka");
        }

        if (!Recipe::where('slug', 'mercimek-kofte')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Mercimek Köftesi',
                'slug' => 'mercimek-kofte',
                'excerpt' => 'Çay saatlerinin vazgeçilmezi.',
                'description' => 'İnce bulgur ve kırmızı mercimekli nefis köfte.',
                'country' => 'Türkiye',
                'prep_minutes' => 30,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 6,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kırmızı Mercimek'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('İnce Bulgur'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Taze Soğan'),
                'display_quantity' => '4',
                'display_measure_code' => 'dal',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Maydanoz'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Mercimeği iyice yıkayıp 3 bardak suyla haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Suyunu çekmeden ocaktan alıp içine bulguru ekleyin, kapağını kapatıp demlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Soğanı yağda salçayla kavurup karışıma ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Yeşillikleri doğrayıp soğuyan harca ilave edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Yoğurup köfte şekli vererek marul ile servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: mercimek-kofte");
        } else {
            $this->command->line("SKIP (exists): mercimek-kofte");
        }

        if (!Recipe::where('slug', 'ispanak-yemegi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Ispanaklı Yumurta',
                'slug' => 'ispanak-yemegi',
                'excerpt' => 'Protein dolu ıspanak yemeği.',
                'description' => 'Kavrulmuş ıspanak üzerine kırılmış yumurtalar.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 15,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Ispanak'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yumurta'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Ispanakları temizleyip doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Soğanı zeytinyağında kavurup ıspanakları ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Ispanaklar suyunu salıp çekene kadar soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Yuvalar açıp yumurtaları kırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kapağı kapatıp yumurtalar pişene kadar bekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: ispanak-yemegi");
        } else {
            $this->command->line("SKIP (exists): ispanak-yemegi");
        }

        if (!Recipe::where('slug', 'kabak-mucver')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Kabak Mücveri',
                'slug' => 'kabak-mucver',
                'excerpt' => 'Kızarmış nefis kabak mücver.',
                'description' => 'Dışı çıtır içi yumuşacık kabak köftesi.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 15,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kabak'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yumurta'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dereotu'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (kızartmak için)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kabakları rendeleyip suyunu iyice sıkın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Yumurta, un, ince doğranmış dereotu ve baharatları ekleyip karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kızgın yağa kaşık yardımıyla harçtan dökün.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Arkalı önlü altın sarısı olana kadar kızartın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kağıt havlu üzerine alıp yoğurtla servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kabak-mucver");
        } else {
            $this->command->line("SKIP (exists): kabak-mucver");
        }

        if (!Recipe::where('slug', 'havuclu-lahana-salatasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Zeytinyağlı Lahana Sarması',
                'slug' => 'havuclu-lahana-salatasi',
                'excerpt' => 'Hafif ve lezzetli lahana sarması.',
                'description' => 'Kuş üzümlü, fıstıklı zeytinyağlı sarma.',
                'country' => 'Türkiye',
                'prep_minutes' => 40,
                'cook_minutes' => 40,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'zor',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Beyaz Lahana'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet (orta boy)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Pirinç'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuş Üzümü'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dolmalık Fıstık'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Lahananın yapraklarını ayırıp kaynar suda hafifçe haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Soğanları bol yağda fıstıklarla kavurup pirinci ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kuş üzümü, yenibahar, tarçın ve su ekleyip iç harcı demlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Lahana yapraklarına harçtan koyup sarın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Tencereye dizip zeytinyağı ve su ekleyerek kısık ateşte pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: havuclu-lahana-salatasi");
        } else {
            $this->command->line("SKIP (exists): havuclu-lahana-salatasi");
        }

        if (!Recipe::where('slug', 'bezelye-yemegi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Etli Bezelye',
                'slug' => 'bezelye-yemegi',
                'excerpt' => 'Klasik ev yemeği etli bezelye.',
                'description' => 'Kuşbaşı et, patates ve havuçlu bezelye.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 40,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Taze Bezelye'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram (veya dondurulmuş)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kuşbaşı'),
                'display_quantity' => '200',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patates'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Havuç'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates Salçası'),
                'display_quantity' => '1',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Eti tencerede kendi suyuyla kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Soğan ve salçayı ekleyip kavurmaya devam edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Küp doğranmış havuç ve patatesleri ilave edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Bezelyeleri ve sıcak suyu ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Sebzeler yumuşayana kadar orta ateşte pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: bezelye-yemegi");
        } else {
            $this->command->line("SKIP (exists): bezelye-yemegi");
        }

        if (!Recipe::where('slug', 'taze-fasulye-sote')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Sote Taze Fasulye',
                'slug' => 'taze-fasulye-sote',
                'excerpt' => 'Pratik taze fasulye sote.',
                'description' => 'Kavrulmuş fasulyenin nefis tadı.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 25,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Taze Fasulye'),
                'display_quantity' => '500',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sarımsak'),
                'display_quantity' => '2',
                'display_measure_code' => 'diş',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Fasulyeleri yıkayıp ortadan ikiye kırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Soğan ve sarımsağı zeytinyağında soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Fasulyeleri ekleyip rengi dönene kadar kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Rendelenmiş domatesleri ilave edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kısık ateşte kendi suyuyla yumuşayana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: taze-fasulye-sote");
        } else {
            $this->command->line("SKIP (exists): taze-fasulye-sote");
        }

        if (!Recipe::where('slug', 'menemen')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['zeytinyagli-sebze']->id,
                'title' => 'Menemen',
                'slug' => 'menemen',
                'excerpt' => 'Kahvaltıların sultanı.',
                'description' => 'Domates, biber ve yumurtanın harika uyumu.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 15,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yeşil Biber'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yumurta'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Biberleri ince doğrayıp yağda kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kabukları soyulmuş, küp doğranmış domatesleri ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Domatesler suyunu çekene kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Yumurtaları kırıp hafifçe karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Yumurtalar istediğiniz kıvama gelince ocaktan alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: menemen");
        } else {
            $this->command->line("SKIP (exists): menemen");
        }

        if (!Recipe::where('slug', 'ezme-salata')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Patlıcan Közlemesi',
                'slug' => 'ezme-salata',
                'excerpt' => 'Köz kokulu patlıcan salatası.',
                'description' => 'Közlenmiş patlıcan ve biberli meze.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patlıcan'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kapya Biber'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sarımsak'),
                'display_quantity' => '2',
                'display_measure_code' => 'diş',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Limon'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Patlıcan ve biberleri ocakta veya fırında közleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kabuklarını soyup ince ince doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Ezilmiş sarımsak, zeytinyağı ve limon suyunu ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Tuzunu ayarlayıp iyice karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Soğuk servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: ezme-salata");
        } else {
            $this->command->line("SKIP (exists): ezme-salata");
        }

        if (!Recipe::where('slug', 'atom-sos')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Acılı Sos (Atom)',
                'slug' => 'atom-sos',
                'excerpt' => 'Yoğurtlu acı meze.',
                'description' => 'Süzme yoğurt ve kızgın yağda acı biber.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 5,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süzme Yoğurt'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sarımsak'),
                'display_quantity' => '2',
                'display_measure_code' => 'diş',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Arnavut Biberi'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '1',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Süzme yoğurdu ezilmiş sarımsak ve tuzla karıştırıp tabağa yayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Tereyağı ve zeytinyağını tavada kızdırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Biberleri doğrayıp kızgın yağda soteleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Cızırdayan yağı yoğurdun üzerine gezdirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Hemen servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: atom-sos");
        } else {
            $this->command->line("SKIP (exists): atom-sos");
        }

        if (!Recipe::where('slug', 'yesil-mercimek-salatasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Yeşil Mercimek Salatası',
                'slug' => 'yesil-mercimek-salatasi',
                'excerpt' => 'Besleyici doyurucu salata.',
                'description' => 'Bol yeşillikli yeşil mercimek salatası.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yeşil Mercimek'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dereotu'),
                'display_quantity' => '0.25',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Maydanoz'),
                'display_quantity' => '0.25',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kırmızı Köz Biber'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Nar Ekşisi'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Mercimekleri dağılmayacak şekilde diri haşlayıp süzün.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Yeşillikleri ve köz biberi ince doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Geniş bir kasede haşlanmış mercimek ve yeşillikleri karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Zeytinyağı, nar ekşisi ve tuz ekleyip harmanlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Soğuk servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: yesil-mercimek-salatasi");
        } else {
            $this->command->line("SKIP (exists): yesil-mercimek-salatasi");
        }

        if (!Recipe::where('slug', 'antep-ezmesi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Antep Ezmesi',
                'slug' => 'antep-ezmesi',
                'excerpt' => 'Kebapçıların vazgeçilmezi.',
                'description' => 'İnce kıyım acılı ezme.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 0,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sivri Biber'),
                'display_quantity' => '3',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Maydanoz'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Biber Salçası'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Domates, soğan, biber ve maydanozu zırhla veya bıçakla çok ince kıyın (rondodan çekmeyin).",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Karışımın fazla suyunu süzün.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Biber salçası, nar ekşisi, zeytinyağı, isot ve tuz ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "İyice karıştırarak servis tabağına alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Soğuk servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: antep-ezmesi");
        } else {
            $this->command->line("SKIP (exists): antep-ezmesi");
        }

        if (!Recipe::where('slug', 'tarator')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Tarator',
                'slug' => 'tarator',
                'excerpt' => 'Kalamarın en iyi dostu.',
                'description' => 'Bayat ekmek, ceviz ve yoğurtlu meze.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 0,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Bayat Ekmek İçi'),
                'display_quantity' => '2',
                'display_measure_code' => 'dilim',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Ceviz İçi'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süzme Yoğurt'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sarımsak'),
                'display_quantity' => '2',
                'display_measure_code' => 'diş',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Ekmek içini ufalayın, cevizleri havanda ezin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Yoğurt, ezilmiş sarımsak, ekmek ve cevizi bir kasede karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Zeytinyağı ve tuz ilave edip kıvam alana kadar çırpın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Kıvam çok koyuysa az su ekleyebilirsiniz.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine zeytinyağı gezdirerek servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: tarator");
        } else {
            $this->command->line("SKIP (exists): tarator");
        }

        if (!Recipe::where('slug', 'gavurdagi-salatasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Gavurdağı Salatası',
                'slug' => 'gavurdagi-salatasi',
                'excerpt' => 'Cevizli ve nar ekşili nefis salata.',
                'description' => 'İnce doğranmış malzemelerle bol cevizli salata.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 0,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Domates'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Ceviz İçi'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (iri dövülmüş)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Nar Ekşisi'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Domatesleri ve soğanı çok küçük küpler halinde doğrayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Soğanı tuzla hafifçe ovup acısını alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Geniş bir kapta domates, soğan ve cevizin yarısını karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Nar ekşisi, zeytinyağı ve tuz ekleyip harmanlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Servis tabağına alıp kalan cevizlerle süsleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: gavurdagi-salatasi");
        } else {
            $this->command->line("SKIP (exists): gavurdagi-salatasi");
        }

        if (!Recipe::where('slug', 'rus-salatasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Rus Salatası',
                'slug' => 'rus-salatasi',
                'excerpt' => 'Klasik mayonezli garnitür salata.',
                'description' => 'Bezelye, havuç, patates ve mayonezin uyumu.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 15,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Patates'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Havuç'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Bezelye'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (haşlanmış)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kornişon Turşu'),
                'display_quantity' => '5',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Mayonez'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Patates ve havuçları çok küçük küpler halinde doğrayıp ayrı ayrı haşlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Soğuyan sebzelere haşlanmış bezelyeyi ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kornişon turşuları küp doğrayıp karışıma ilave edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Mayonez ve tuzu ekleyip iyice karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Buzdolabında dinlendirip servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: rus-salatasi");
        } else {
            $this->command->line("SKIP (exists): rus-salatasi");
        }

        if (!Recipe::where('slug', 'piyaz')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['salata-meze']->id,
                'title' => 'Piyaz',
                'slug' => 'piyaz',
                'excerpt' => 'Köftenin ayrılmaz eşlikçisi.',
                'description' => 'Fasulye, soğan ve sirkeli nefis piyaz.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 0,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Haşlanmış Kuru Fasulye'),
                'display_quantity' => '2',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kırmızı Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Maydanoz'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Elma Sirkesi'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Soğanı piyazlık (yarım ay) doğrayıp sumak ve tuzla ovun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Geniş bir kapta fasulye, soğan ve ince kıyılmış maydanozu birleştirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Zeytinyağı, sirke ve tuzdan oluşan sosu ekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Nazikçe ezmeden karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine dilimlenmiş haşlanmış yumurta ekleyerek servis yapabilirsiniz.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: piyaz");
        } else {
            $this->command->line("SKIP (exists): piyaz");
        }

        if (!Recipe::where('slug', 'cig-borek')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['hamur-isi']->id,
                'title' => 'Çiğ Börek',
                'slug' => 'cig-borek',
                'excerpt' => 'Eskişehir\'in meşhur böreği.',
                'description' => 'Yağda kızaran nefis kıymalı ince börek.',
                'country' => 'Türkiye',
                'prep_minutes' => 30,
                'cook_minutes' => 15,
                'rest_minutes' => 30,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '3',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kıyma'),
                'display_quantity' => '250',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Un, su ve tuz ile orta sertlikte bir hamur yoğurun ve dinlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kıymaya rendelenmiş soğan, karabiber, tuz ve biraz su ekleyip sulu bir harç hazırlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Hamurdan bezeler alıp tabak büyüklüğünde incecik açın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Yarısına kıymalı harçtan sürüp D şeklinde kapatın, kenarlarını ruletle kesin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kızgın bol yağda arkalı önlü kızartın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: cig-borek");
        } else {
            $this->command->line("SKIP (exists): cig-borek");
        }

        if (!Recipe::where('slug', 'kol-boregi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['hamur-isi']->id,
                'title' => 'Kol Böreği',
                'slug' => 'kol-boregi',
                'excerpt' => 'El açması peynirli kol böreği.',
                'description' => 'Çıtır çıtır ev yapımı börek.',
                'country' => 'Türkiye',
                'prep_minutes' => 45,
                'cook_minutes' => 40,
                'rest_minutes' => 30,
                'servings' => 6,
                'difficulty' => 'zor',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '4',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay bardağı (hamuru yağlamak için)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Beyaz Peynir'),
                'display_quantity' => '300',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Maydanoz'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'demet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Hamuru yoğurup bezeler oluşturun ve tabak büyüklüğünde açarak aralarını yağlayın, yarım saat bekletin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Dinlenen hamuru elinizle çekerek incecik zar gibi açın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Uç kısmına peynirli iç harçtan koyup rulo şeklinde sarın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Ruloları gül şeklinde veya U şeklinde fırın tepsisine dizin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine yağ sürüp 200 derece fırında kızarana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kol-boregi");
        } else {
            $this->command->line("SKIP (exists): kol-boregi");
        }

        if (!Recipe::where('slug', 'acma')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['hamur-isi']->id,
                'title' => 'Açma',
                'slug' => 'acma',
                'excerpt' => 'Pastane usulü tel tel açma.',
                'description' => 'Yumuşacık dokusuyla nefis açma tarifi.',
                'country' => 'Türkiye',
                'prep_minutes' => 30,
                'cook_minutes' => 20,
                'rest_minutes' => 60,
                'servings' => 8,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süt'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (ılık)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Sıvı Yağ'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yaş Maya'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'paket',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '4.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '50',
                'display_measure_code' => 'gram (arasına sürmek için)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Ilık süt, maya ve şekeri karıştırıp aktive edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Yağ ve tuzu ekleyip, unu azar azar ilave ederek yumuşak bir hamur yoğurun ve mayalandırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Bezeler koparıp üzerlerine yumuşak tereyağı sürerek çay tabağı kadar açın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Rulo yapıp uçlarını birleştirerek açma şekli verin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Üzerine yumurta sarısı sürüp fırında pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: acma");
        } else {
            $this->command->line("SKIP (exists): acma");
        }

        if (!Recipe::where('slug', 'simit')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['hamur-isi']->id,
                'title' => 'Ev Simidi',
                'slug' => 'simit',
                'excerpt' => 'Çıtır çıtır ev yapımı simit.',
                'description' => 'Susamlı sokak simidi tadında.',
                'country' => 'Türkiye',
                'prep_minutes' => 25,
                'cook_minutes' => 20,
                'rest_minutes' => 45,
                'servings' => 6,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '4',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı (ılık)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Maya'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Pekmez'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'çay bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kavrulmuş Susam'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Hamur malzemeleriyle kulak memesi kıvamında hamur yoğurup mayalandırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Pekmezi yarım çay bardağı su ile açarak geniş bir tabağa alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Mayalanan hamurdan fitiller yapıp iki fitili birbirine örerek simit halkası yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Halkaları önce pekmezli suya sonra susama bulayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Tepsiye dizip 200 derece fırında kızarana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: simit");
        } else {
            $this->command->line("SKIP (exists): simit");
        }

        if (!Recipe::where('slug', 'bazlama')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['hamur-isi']->id,
                'title' => 'Bazlama',
                'slug' => 'bazlama',
                'excerpt' => 'Tavada pişen yumuşacık bazlama.',
                'description' => 'Balon gibi kabaran kahvaltılık ekmek.',
                'country' => 'Türkiye',
                'prep_minutes' => 20,
                'cook_minutes' => 15,
                'rest_minutes' => 60,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '3',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (ılık)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Maya'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuz'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Zeytinyağı'),
                'display_quantity' => '1',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Tüm malzemelerle ele yapışmayan yumuşak bir hamur yoğurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Hamurun üzerini kapatıp 1 saat mayalandırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Mandalina büyüklüğünde bezeler alıp merdaneyle tatlı tabağı büyüklüğünde açın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Isıtılmış yapışmaz tavada arkalı önlü sık sık çevirerek pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Pişen bazlamaların üzerine sıcakken tereyağı sürebilirsiniz.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: bazlama");
        } else {
            $this->command->line("SKIP (exists): bazlama");
        }

        if (!Recipe::where('slug', 'kiymali-pide')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['hamur-isi']->id,
                'title' => 'Kıymalı Pide',
                'slug' => 'kiymali-pide',
                'excerpt' => 'Evde çıtır kıymalı pide.',
                'description' => 'Pidecileri aratmayacak lezzette.',
                'country' => 'Türkiye',
                'prep_minutes' => 30,
                'cook_minutes' => 15,
                'rest_minutes' => 45,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '3',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Maya'),
                'display_quantity' => '1',
                'display_measure_code' => 'tatlı kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (ılık)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Dana Kıyma'),
                'display_quantity' => '300',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kuru Soğan'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Hamuru yoğurup mayalanmaya bırakın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "İç harcı için kıyma, ince doğranmış soğan, domates, biber ve baharatları yoğurun (çiğden).",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Mayalanan hamurdan bezeler alıp uzunlamasına oval açın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Ortasına kıymalı harçtan yayıp kenarlarını içe doğru kıvırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Önceden ısıtılmış en yüksek dereceli fırında kenarları kızarana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kiymali-pide");
        } else {
            $this->command->line("SKIP (exists): kiymali-pide");
        }

        if (!Recipe::where('slug', 'sambali')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['tatli']->id,
                'title' => 'Şambali',
                'slug' => 'sambali',
                'excerpt' => 'İzmir\'in meşhur sokak tatlısı.',
                'description' => 'Yoğurtlu, irmikli ve şerbetli enfes tatlı.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 40,
                'rest_minutes' => 60,
                'servings' => 8,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('İrmik'),
                'display_quantity' => '2.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Yoğurt'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Karbonat'),
                'display_quantity' => '1',
                'display_measure_code' => 'çay kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tuzsuz Yer Fıstığı'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "İrmik, yoğurt, şeker ve karbonatı karıştırıp koyu bir hamur elde edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Yağlanmış tepsiye bastırarak yayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Üzerine fıstıkları dizip fırına vermeden dilim izlerini belli edin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Önceden ısıtılmış 180 derece fırında üzeri iyice kızarana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Fırından çıkar çıkmaz soğuk şerbeti döküp çekmesini bekleyin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: sambali");
        } else {
            $this->command->line("SKIP (exists): sambali");
        }

        if (!Recipe::where('slug', 'irmik-helvasi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['tatli']->id,
                'title' => 'İrmik Helvası',
                'slug' => 'irmik-helvasi',
                'excerpt' => 'Sütlü ve çam fıstıklı irmik helvası.',
                'description' => 'Tam ölçülü klasik irmik helvası.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 25,
                'rest_minutes' => 15,
                'servings' => 6,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('İrmik'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Çam Fıstığı'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süt'),
                'display_quantity' => '2',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Şeker ve sütü bir tencerede ısıtıp şekeri eritin (kaynamasına gerek yok).",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Ayrı bir tencerede tereyağını eritip fıstıkları hafif pembeleşene kadar kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "İrmiği ekleyip rengi dönene kadar kısık ateşte sürekli karıştırarak kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Sıcak sütlü şerbeti dikkatlice irmiğin üzerine dökün ve karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kapağını kapatıp suyunu çekene kadar demlendirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: irmik-helvasi");
        } else {
            $this->command->line("SKIP (exists): irmik-helvasi");
        }

        if (!Recipe::where('slug', 'cennet-camuru')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['tatli']->id,
                'title' => 'Cennet Çamuru',
                'slug' => 'cennet-camuru',
                'excerpt' => 'Kilis\'in meşhur fıstıklı tatlısı.',
                'description' => 'Kadayıf ve bol Antep fıstıklı yoğun tatlı.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Çiğ Kadayıf'),
                'display_quantity' => '250',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Toz Antep Fıstığı'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Şeker ve suyu kaynatarak şerbet hazırlayın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kadayıfları ince ince kıyıp geniş bir tavada tereyağı ile kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Kadayıflar kızarmadan hafif pembeleştiğinde Antep fıstığını ekleyip 1-2 dakika daha kavurun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Ocaktan almadan önce sıcak şerbeti ekleyip karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Helva kıvamına gelince tabaklara alıp üzerine kaymak ile servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: cennet-camuru");
        } else {
            $this->command->line("SKIP (exists): cennet-camuru");
        }

        if (!Recipe::where('slug', 'supangle')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['tatli']->id,
                'title' => 'Supangle',
                'slug' => 'supangle',
                'excerpt' => 'Kakaolu sütlü tatlı.',
                'description' => 'Pastane usulü orijinal supangle tarifi.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 15,
                'rest_minutes' => 120,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Süt'),
                'display_quantity' => '4',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '0.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Un'),
                'display_quantity' => '2',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kakao'),
                'display_quantity' => '3',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Bitter Çikolata'),
                'display_quantity' => '80',
                'display_measure_code' => 'gram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tereyağı'),
                'display_quantity' => '1',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Süt, şeker, un ve kakaoyu tencerede pürüzsüz olana kadar çırpın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Orta ateşte sürekli karıştırarak muhallebi kıvamına gelene kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Ocaktan alıp içine çikolata ve tereyağını ekleyerek eriyene kadar karıştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Dilerseniz kaselerin dibine kek parçaları koyup üzerine tatlıyı paylaştırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Buzdolabında soğuttuktan sonra servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: supangle");
        } else {
            $this->command->line("SKIP (exists): supangle");
        }

        if (!Recipe::where('slug', 'ayva-tatlisi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['tatli']->id,
                'title' => 'Ayva Tatlısı',
                'slug' => 'ayva-tatlisi',
                'excerpt' => 'Kış aylarının vazgeçilmezi.',
                'description' => 'Rengini çekirdeğinden alan nefis tatlı.',
                'country' => 'Türkiye',
                'prep_minutes' => 15,
                'cook_minutes' => 60,
                'rest_minutes' => 60,
                'servings' => 4,
                'difficulty' => 'orta',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Ayva'),
                'display_quantity' => '2',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '8',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Çubuk Tarçın'),
                'display_quantity' => '1',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Karanfil'),
                'display_quantity' => '4',
                'display_measure_code' => 'adet',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Su'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Kaymak'),
                'display_quantity' => '1',
                'display_measure_code' => 'paket (servis için)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Ayvaları soyup ortadan ikiye bölün ve çekirdeklerini ayırın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Kararmaması için limonlu suda bekletin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Geniş bir tencereye ayvaları dizin, her yarımın üzerine 2 kaşık şeker koyun.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Ayva çekirdeklerini, tarçın ve karanfilleri tencereye ekleyip suyunu verin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Kısık ateşte ayvalar yumuşayıp jölemsi bir renk alana kadar yaklaşık 1 saat pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: ayva-tatlisi");
        } else {
            $this->command->line("SKIP (exists): ayva-tatlisi");
        }

        if (!Recipe::where('slug', 'kabak-tatlisi')->exists()) {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $categories['tatli']->id,
                'title' => 'Kabak Tatlısı',
                'slug' => 'kabak-tatlisi',
                'excerpt' => 'Tahinli cevizli balkabağı tatlısı.',
                'description' => 'Kendi suyuyla pişen nefis kabak tatlısı.',
                'country' => 'Türkiye',
                'prep_minutes' => 10,
                'cook_minutes' => 40,
                'rest_minutes' => 120,
                'servings' => 4,
                'difficulty' => 'kolay',
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

            $sort = 1;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Balkabağı'),
                'display_quantity' => '1',
                'display_measure_code' => 'kilogram',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Şeker'),
                'display_quantity' => '1.5',
                'display_measure_code' => 'su bardağı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Ceviz İçi'),
                'display_quantity' => '1',
                'display_measure_code' => 'su bardağı (iri dövülmüş)',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing('Tahin'),
                'display_quantity' => '4',
                'display_measure_code' => 'yemek kaşığı',
                'sort_order' => $sort++,
                'canonical_quantity' => null,
                'canonical_unit' => null,
                'scaling_behavior' => 'scalable',
                'is_optional' => false,
                'conversion_confidence' => 'unknown',
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 1,
                'title' => 'Adım 1',
                'instruction' => "Kabakları dilimleyip geniş bir tencereye dizin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 2,
                'title' => 'Adım 2',
                'instruction' => "Üzerine şekeri döküp kapağını kapatın ve en az 2 saat (vaktiniz varsa bir gece) sulanması için bekletin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 3,
                'title' => 'Adım 3',
                'instruction' => "Sulanan kabakları kısık ateşte kendi suyuyla yumuşayana kadar pişirin.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 4,
                'title' => 'Adım 4',
                'instruction' => "Suyunu çekip şerbeti koyulaştığında ocaktan alın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'position' => 5,
                'title' => 'Adım 5',
                'instruction' => "Soğuduktan sonra üzerine tahin ve ceviz gezdirerek servis yapın.",
                'chef_tip' => null,
                'duration_seconds' => 0,
                'timer_required' => false,
                'is_optional' => false,
            ]);

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

            $this->command->line("✓ Eklendi: kabak-tatlisi");
        } else {
            $this->command->line("SKIP (exists): kabak-tatlisi");
        }

        $total = Recipe::where('status', 'published')->count();
        $this->command->info("✓ MegaTarifPaketi2Seeder tamamlandı. Toplam yayında: {$total}");
    }
}
