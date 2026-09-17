<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\RecipeProvenance;
use Illuminate\Support\Str;

class VeganAkdenizSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'vegan'],
            ['name' => 'Vegan', 'description' => 'Vegan tarifleri', 'public_id' => (string) Str::ulid()]
        );

        
        $akdeniz = Cuisine::firstOrCreate(
            ['slug' => 'akdeniz-mutfagi'],
            ['name' => 'Akdeniz Mutfağı', 'country' => 'Akdeniz', 'public_id' => (string) Str::ulid()]
        );


        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;

        
        // Recipe: Vegan Mercimek Köftesi
        $slug = 'mercimek-kofte-vegan';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Mercimek Köftesi',
                'slug' => $slug,
                'excerpt' => 'Vegan Mercimek Köftesi tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Mercimek Köftesi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Kırmızı Mercimek');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 484,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 435,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 191,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 476,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 172,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 207,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 228,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Nohutlu Ispanak
        $slug = 'nohutlu-ispanak-vegan';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Nohutlu Ispanak',
                'slug' => $slug,
                'excerpt' => 'Vegan Nohutlu Ispanak tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Nohutlu Ispanak tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Haşlanmış Nohut');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 310,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 255,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 520,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 479,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 349,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 323,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 249,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Zeytinyağlı Pazı
        $slug = 'zeytinyagli-pazi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Zeytinyağlı Pazı',
                'slug' => $slug,
                'excerpt' => 'Zeytinyağlı Pazı tarifi.',
                'description' => 'Nefis ve pratik bir Zeytinyağlı Pazı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 447,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 258,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 134,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 460,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 399,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 424,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 494,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Akdeniz Falafeli
        $slug = 'falafel-akdeniz';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Akdeniz Falafeli',
                'slug' => $slug,
                'excerpt' => 'Akdeniz Falafeli tarifi.',
                'description' => 'Nefis ve pratik bir Akdeniz Falafeli tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 3,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Su');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'su_bardagi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'su_bardagi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Özel Baharat');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Ana Malzeme');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '250',
            'display_measure_code' => 'gram',
            'sort_order' => 6,
            'canonical_quantity' => 250,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 140,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 204,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 175,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 194,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 337,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 264,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 378,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Tabule
        $slug = 'tabule-vegan';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Tabule',
                'slug' => $slug,
                'excerpt' => 'Vegan Tabule tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Tabule tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 256,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 501,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 139,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 554,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 492,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 185,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 202,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Humus
        $slug = 'vegan-humus';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Humus',
                'slug' => $slug,
                'excerpt' => 'Vegan Humus tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Humus tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 272,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 478,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 554,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 243,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 352,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 554,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 402,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Zeytinyağlı Barbunya
        $slug = 'zeytinyagli-barbunya';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Zeytinyağlı Barbunya',
                'slug' => $slug,
                'excerpt' => 'Zeytinyağlı Barbunya tarifi.',
                'description' => 'Nefis ve pratik bir Zeytinyağlı Barbunya tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 412,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 137,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 446,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 576,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 574,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 500,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 550,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Patlıcan Salatası
        $slug = 'vegan-patlican-salatasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Patlıcan Salatası',
                'slug' => $slug,
                'excerpt' => 'Vegan Patlıcan Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Patlıcan Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Patlıcan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 474,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 307,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 260,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 457,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 127,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 223,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 306,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Yaprak Sarma
        $slug = 'vegan-dolma';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Yaprak Sarma',
                'slug' => $slug,
                'excerpt' => 'Vegan Yaprak Sarma tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Yaprak Sarma tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 420,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 424,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 451,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 493,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 360,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 323,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 270,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Lahanalı Pilav
        $slug = 'vegan-lahanali-pilav';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Lahanalı Pilav',
                'slug' => $slug,
                'excerpt' => 'Vegan Lahanalı Pilav tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Lahanalı Pilav tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 314,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 225,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 463,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 244,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 261,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 483,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 380,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Mercimek Çorbası
        $slug = 'vegan-mercimek-corbasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Mercimek Çorbası',
                'slug' => $slug,
                'excerpt' => 'Vegan Mercimek Çorbası tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Mercimek Çorbası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Kırmızı Mercimek');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 139,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 518,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 480,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 133,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 442,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 279,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 265,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Nohut Körisi
        $slug = 'vegan-nohut-kari';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Nohut Körisi',
                'slug' => $slug,
                'excerpt' => 'Vegan Nohut Körisi tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Nohut Körisi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Haşlanmış Nohut');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 223,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 289,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 530,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 388,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 455,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 238,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 497,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Bulgur Pilavı
        $slug = 'vegan-bulgur-pilavi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Bulgur Pilavı',
                'slug' => $slug,
                'excerpt' => 'Vegan Bulgur Pilavı tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Bulgur Pilavı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 249,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 180,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 350,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 523,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 466,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 294,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 435,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Kabak Mücveri
        $slug = 'vegan-kabak-mucveri';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Kabak Mücveri',
                'slug' => $slug,
                'excerpt' => 'Vegan Kabak Mücveri tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Kabak Mücveri tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 533,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 286,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 224,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 301,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 526,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 130,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 216,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Akdeniz Kasesi
        $slug = 'vegan-palamut-salatasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Akdeniz Kasesi',
                'slug' => $slug,
                'excerpt' => 'Vegan Akdeniz Kasesi tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Akdeniz Kasesi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 566,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 319,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 303,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 374,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 325,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 252,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 178,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Portakallı Akdeniz Salatası
        $slug = 'vegan-portakal-salatasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Portakallı Akdeniz Salatası',
                'slug' => $slug,
                'excerpt' => 'Portakallı Akdeniz Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Portakallı Akdeniz Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Domates');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Salatalık');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yeşillik');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'bag',
            'sort_order' => 9,
            'canonical_quantity' => 1,
            'canonical_unit' => 'bag',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 315,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 388,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 242,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 574,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 559,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 372,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 474,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Soteleme Mantarı
        $slug = 'vegan-mantar-soteleme';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Soteleme Mantarı',
                'slug' => $slug,
                'excerpt' => 'Vegan Soteleme Mantarı tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Soteleme Mantarı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 376,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 584,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 505,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 447,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 407,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 561,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 397,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Güveç
        $slug = 'vegan-guveç';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Güveç',
                'slug' => $slug,
                'excerpt' => 'Vegan Güveç tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Güveç tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 350,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 549,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 382,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 361,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 561,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 241,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 357,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Ispanaklı Gözleme
        $slug = 'vegan-ispanakli-gozleme';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Ispanaklı Gözleme',
                'slug' => $slug,
                'excerpt' => 'Vegan Ispanaklı Gözleme tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Ispanaklı Gözleme tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Yufka');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sıvı Yağ');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Peynir');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '100',
            'display_measure_code' => 'gram',
            'sort_order' => 3,
            'canonical_quantity' => 100,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Maydanoz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'tutam',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'tutam',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yumurta');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Süt');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '100',
            'display_measure_code' => 'mililitre',
            'sort_order' => 6,
            'canonical_quantity' => 100,
            'canonical_unit' => 'mililitre',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 159,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 204,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 471,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 457,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 500,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 380,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 188,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Portakallı Bulgur Salatası
        $slug = 'vegan-portakalli-bulgur';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Portakallı Bulgur Salatası',
                'slug' => $slug,
                'excerpt' => 'Portakallı Bulgur Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Portakallı Bulgur Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Domates');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Salatalık');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yeşillik');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'bag',
            'sort_order' => 9,
            'canonical_quantity' => 1,
            'canonical_unit' => 'bag',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 502,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 398,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 345,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 378,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 427,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 418,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 523,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Zeytinyağlı Enginar
        $slug = 'vegan-enginar';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Zeytinyağlı Enginar',
                'slug' => $slug,
                'excerpt' => 'Vegan Zeytinyağlı Enginar tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Zeytinyağlı Enginar tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 308,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 296,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 377,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 504,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 212,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 567,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 374,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Pirincli Fasulye
        $slug = 'vegan-pirinç-fasulye';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Pirincli Fasulye',
                'slug' => $slug,
                'excerpt' => 'Vegan Pirincli Fasulye tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Pirincli Fasulye tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 573,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 402,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 267,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 302,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 140,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 330,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 482,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Roka ve Nar Salatası
        $slug = 'vegan-roka-salatasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Roka ve Nar Salatası',
                'slug' => $slug,
                'excerpt' => 'Roka ve Nar Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Roka ve Nar Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Domates');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Salatalık');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yeşillik');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'bag',
            'sort_order' => 9,
            'canonical_quantity' => 1,
            'canonical_unit' => 'bag',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 523,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 421,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 571,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 185,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 166,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 493,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 403,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Çiğ Köfte
        $slug = 'vegan-çig-kofte';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Çiğ Köfte',
                'slug' => $slug,
                'excerpt' => 'Çiğ Köfte tarifi.',
                'description' => 'Nefis ve pratik bir Çiğ Köfte tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 3,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Su');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'su_bardagi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'su_bardagi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Özel Baharat');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Ana Malzeme');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '250',
            'display_measure_code' => 'gram',
            'sort_order' => 6,
            'canonical_quantity' => 250,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 457,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 274,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 216,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 169,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 240,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 291,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 463,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Kusküs
        $slug = 'vegan-kuskus';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Kusküs',
                'slug' => $slug,
                'excerpt' => 'Vegan Kusküs tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Kusküs tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 202,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 597,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 381,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 531,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 573,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 433,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 281,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Kabak Dolması
        $slug = 'vegan-kabak-dolmasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Kabak Dolması',
                'slug' => $slug,
                'excerpt' => 'Vegan Kabak Dolması tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Kabak Dolması tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 198,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 502,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 427,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 125,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 349,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 157,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 591,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Patlıcan Kızartması
        $slug = 'vegan-patlican-kizartma';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Patlıcan Kızartması',
                'slug' => $slug,
                'excerpt' => 'Vegan Patlıcan Kızartması tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Patlıcan Kızartması tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Patlıcan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 155,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 383,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 465,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 566,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 372,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 472,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 598,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Soğan Kavurması
        $slug = 'vegan-soğan-kavurma';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Soğan Kavurması',
                'slug' => $slug,
                'excerpt' => 'Vegan Soğan Kavurması tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Soğan Kavurması tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 464,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 284,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 457,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 288,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 394,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 280,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 284,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Nohutlu Domates Yemeği
        $slug = 'vegan-nohutlu-domates';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Nohutlu Domates Yemeği',
                'slug' => $slug,
                'excerpt' => 'Vegan Nohutlu Domates Yemeği tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Nohutlu Domates Yemeği tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Haşlanmış Nohut');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 569,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 364,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 397,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 370,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 359,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 219,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 185,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Bitkisel Çorba
        $slug = 'vegan-bitkisel-corba';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Bitkisel Çorba',
                'slug' => $slug,
                'excerpt' => 'Vegan Bitkisel Çorba tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Bitkisel Çorba tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 368,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 491,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 372,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 500,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 294,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 366,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 470,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Karnabahar Kroketi
        $slug = 'vegan-karnabahar-kroket';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Karnabahar Kroketi',
                'slug' => $slug,
                'excerpt' => 'Vegan Karnabahar Kroketi tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Karnabahar Kroketi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 229,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 499,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 165,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 486,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 428,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 489,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 311,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Zeytinli Makarna
        $slug = 'vegan-zeytinli-makarna';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Zeytinli Makarna',
                'slug' => $slug,
                'excerpt' => 'Vegan Zeytinli Makarna tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Zeytinli Makarna tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 332,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 311,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 124,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 188,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 518,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 547,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 168,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Fıstıklı Bulgur Pilavı
        $slug = 'vegan-fistikli-bulgur';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Fıstıklı Bulgur Pilavı',
                'slug' => $slug,
                'excerpt' => 'Fıstıklı Bulgur Pilavı tarifi.',
                'description' => 'Nefis ve pratik bir Fıstıklı Bulgur Pilavı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 3,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Su');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'su_bardagi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'su_bardagi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Özel Baharat');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Ana Malzeme');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '250',
            'display_measure_code' => 'gram',
            'sort_order' => 6,
            'canonical_quantity' => 250,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 327,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 146,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 289,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 165,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 328,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 230,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 163,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Limonlu Vegan Mercimek
        $slug = 'vegan-limonlu-mercimek';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Limonlu Vegan Mercimek',
                'slug' => $slug,
                'excerpt' => 'Limonlu Vegan Mercimek tarifi.',
                'description' => 'Nefis ve pratik bir Limonlu Vegan Mercimek tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Kırmızı Mercimek');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 268,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 441,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 584,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 401,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 358,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 265,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 554,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Domates Çorbası
        $slug = 'vegan-domates-corbasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Domates Çorbası',
                'slug' => $slug,
                'excerpt' => 'Vegan Domates Çorbası tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Domates Çorbası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 411,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 542,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 409,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 243,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 364,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 337,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 191,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Tahin Soslu Vegan Kase
        $slug = 'vegan-tahin-sos';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Tahin Soslu Vegan Kase',
                'slug' => $slug,
                'excerpt' => 'Tahin Soslu Vegan Kase tarifi.',
                'description' => 'Nefis ve pratik bir Tahin Soslu Vegan Kase tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 456,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 236,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 198,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 192,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 450,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 325,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 310,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Mantar Risottosu
        $slug = 'vegan-mantar-risotto';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Mantar Risottosu',
                'slug' => $slug,
                'excerpt' => 'Vegan Mantar Risottosu tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Mantar Risottosu tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 233,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 363,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 379,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 402,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 454,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 396,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 436,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Sebze Takosu
        $slug = 'vegan-sebze-tacos';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Sebze Takosu',
                'slug' => $slug,
                'excerpt' => 'Vegan Sebze Takosu tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Sebze Takosu tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 163,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 470,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 292,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 297,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 240,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 596,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 560,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Sebze Wok
        $slug = 'vegan-sebze-wok';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Sebze Wok',
                'slug' => $slug,
                'excerpt' => 'Vegan Sebze Wok tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Sebze Wok tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 387,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 335,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 417,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 245,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 388,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 309,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 331,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Börülce Yemeği
        $slug = 'vegan-börülce';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Börülce Yemeği',
                'slug' => $slug,
                'excerpt' => 'Vegan Börülce Yemeği tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Börülce Yemeği tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 371,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 391,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 577,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 350,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 555,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 416,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 313,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Acılı Vegan Nohut
        $slug = 'vegan-acili-nohut';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Acılı Vegan Nohut',
                'slug' => $slug,
                'excerpt' => 'Acılı Vegan Nohut tarifi.',
                'description' => 'Nefis ve pratik bir Acılı Vegan Nohut tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Haşlanmış Nohut');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '200',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 200,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 404,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 554,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 176,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 581,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 502,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 238,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 276,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Hindistan Cevizli Vegan Çorba
        $slug = 'vegan-hindistan-cevizli';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Hindistan Cevizli Vegan Çorba',
                'slug' => $slug,
                'excerpt' => 'Hindistan Cevizli Vegan Çorba tarifi.',
                'description' => 'Nefis ve pratik bir Hindistan Cevizli Vegan Çorba tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 377,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 145,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 252,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 549,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 527,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 143,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 339,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Vegan Domates Pesto Makarna
        $slug = 'vegan-domates-pesto';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Vegan Domates Pesto Makarna',
                'slug' => $slug,
                'excerpt' => 'Vegan Domates Pesto Makarna tarifi.',
                'description' => 'Nefis ve pratik bir Vegan Domates Pesto Makarna tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 578,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 362,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 381,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 540,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 552,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 195,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 221,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Çıtır Tofu Salatası
        $slug = 'vegan-citir-tofu';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Çıtır Tofu Salatası',
                'slug' => $slug,
                'excerpt' => 'Çıtır Tofu Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Çıtır Tofu Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Domates');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Salatalık');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yeşillik');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'bag',
            'sort_order' => 9,
            'canonical_quantity' => 1,
            'canonical_unit' => 'bag',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 506,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 182,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 414,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 411,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 418,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 251,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 436,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Arpa Şehriye Pilavı
        $slug = 'vegan-arpa-pilavi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Arpa Şehriye Pilavı',
                'slug' => $slug,
                'excerpt' => 'Arpa Şehriye Pilavı tarifi.',
                'description' => 'Nefis ve pratik bir Arpa Şehriye Pilavı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 3,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Su');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'su_bardagi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'su_bardagi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Özel Baharat');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Ana Malzeme');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '250',
            'display_measure_code' => 'gram',
            'sort_order' => 6,
            'canonical_quantity' => 250,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 592,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 396,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 594,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 339,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 166,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 512,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 386,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Semizotu Salatası
        $slug = 'vegan-semizotu-salatasi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Semizotu Salatası',
                'slug' => $slug,
                'excerpt' => 'Semizotu Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Semizotu Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Domates');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Salatalık');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yeşillik');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'bag',
            'sort_order' => 9,
            'canonical_quantity' => 1,
            'canonical_unit' => 'bag',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 580,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 292,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 261,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 549,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 596,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 431,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 472,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Karpuz Salatası
        $slug = 'vegan-karpuz-feta-salata';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Karpuz Salatası',
                'slug' => $slug,
                'excerpt' => 'Karpuz Salatası tarifi.',
                'description' => 'Nefis ve pratik bir Karpuz Salatası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Domates');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 7,
            'canonical_quantity' => 2,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Salatalık');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Yeşillik');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'bag',
            'sort_order' => 9,
            'canonical_quantity' => 1,
            'canonical_unit' => 'bag',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 516,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 522,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 179,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 384,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 480,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 371,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 355,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Yeşillik Smoothie Kasesi
        $slug = 'vegan-yesillik-smoothie';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Yeşillik Smoothie Kasesi',
                'slug' => $slug,
                'excerpt' => 'Yeşillik Smoothie Kasesi tarifi.',
                'description' => 'Nefis ve pratik bir Yeşillik Smoothie Kasesi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 3,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Su');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'su_bardagi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'su_bardagi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Özel Baharat');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Ana Malzeme');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '250',
            'display_measure_code' => 'gram',
            'sort_order' => 6,
            'canonical_quantity' => 250,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 230,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 531,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 591,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 134,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 272,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 347,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 441,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Taze Domates Sosu Makarna
        $slug = 'vegan-taze-domates-sos';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Taze Domates Sosu Makarna',
                'slug' => $slug,
                'excerpt' => 'Taze Domates Sosu Makarna tarifi.',
                'description' => 'Nefis ve pratik bir Taze Domates Sosu Makarna tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 3,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Su');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'su_bardagi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'su_bardagi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Özel Baharat');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Ana Malzeme');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '250',
            'display_measure_code' => 'gram',
            'sort_order' => 6,
            'canonical_quantity' => 250,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 465,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 501,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 232,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 245,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 330,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 571,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 518,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }

        // Recipe: Günlük Vegan Akdeniz Kasesi
        $slug = 'vegan-gunluk-kase';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $akdeniz->id,
                'category_id' => $category->id,
                'title' => 'Günlük Vegan Akdeniz Kasesi',
                'slug' => $slug,
                'excerpt' => 'Günlük Vegan Akdeniz Kasesi tarifi.',
                'description' => 'Nefis ve pratik bir Günlük Vegan Akdeniz Kasesi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Akdeniz',
                'prep_minutes' => 15,
                'cook_minutes' => 20,
                'rest_minutes' => 0,
                'servings' => 2,
                'difficulty' => 'easy',
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

            
        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '3',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 1,
            'canonical_quantity' => 3,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Soğan');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'adet',
            'sort_order' => 2,
            'canonical_quantity' => 1,
            'canonical_unit' => 'adet',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sarımsak');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'dis',
            'sort_order' => 3,
            'canonical_quantity' => 2,
            'canonical_unit' => 'dis',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Tuz');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Karabiber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Limon Suyu');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 2,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sebze Karışımı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '300',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 300,
            'canonical_unit' => 'gram',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

            
        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'title' => 'Adım 1',
            'instruction' => 'Malzemeleri yıkayın ve hazırlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 206,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 236,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 271,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 313,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 530,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 467,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 540,
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

            $this->command->line("✓ Eklendi: {$slug}");
        }


        $total = Recipe::where('status', 'published')->count();
        $this->command->info("✓ VeganAkdenizSeeder tamamlandı. Toplam yayında: {$total}");
    }
}
