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

class KahvaltiTarifleriSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'kahvalti'],
            ['name' => 'Kahvaltı', 'description' => 'Kahvaltı tarifleri', 'public_id' => (string) Str::ulid()]
        );

        
        $turk = Cuisine::firstOrCreate(
            ['slug' => 'turk-mutfagi'],
            ['name' => 'Türk Mutfağı', 'country' => 'Türkiye', 'public_id' => (string) Str::ulid()]
        );
        $kibris = Cuisine::firstOrCreate(
            ['slug' => 'kibris-mutfagi'],
            ['name' => 'Kıbrıs Mutfağı', 'country' => 'Kıbrıs', 'public_id' => (string) Str::ulid()]
        );


        $ing = fn (string $name): int => Ingredient::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'canonical_name' => $name, 'default_unit' => 'gram', 'public_id' => (string) Str::ulid()]
        )->id;

        
        // Recipe: Menemen
        $slug = 'menemen';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Menemen',
                'slug' => $slug,
                'excerpt' => 'Menemen tarifi.',
                'description' => 'Nefis ve pratik bir Menemen tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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

        $ingId = $ing('Yeşil Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '2',
            'display_measure_code' => 'adet',
            'sort_order' => 8,
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
            'duration_seconds' => 280,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 208,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 434,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 179,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 205,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 121,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 548,
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

        // Recipe: Çılbır
        $slug = 'cilbir';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Çılbır',
                'slug' => $slug,
                'excerpt' => 'Çılbır tarifi.',
                'description' => 'Nefis ve pratik bir Çılbır tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 380,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 462,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 248,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 158,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 486,
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
            'duration_seconds' => 230,
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

        // Recipe: Simit ve Peynir Tabağı
        $slug = 'simit-ve-peynir';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Simit ve Peynir Tabağı',
                'slug' => $slug,
                'excerpt' => 'Simit ve Peynir Tabağı tarifi.',
                'description' => 'Nefis ve pratik bir Simit ve Peynir Tabağı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 208,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 489,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 291,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 573,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 139,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 545,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 549,
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

        // Recipe: Hellimli Kahvaltı Tavası
        $slug = 'hellimli-kahvalti-tavas';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $category->id,
                'title' => 'Hellimli Kahvaltı Tavası',
                'slug' => $slug,
                'excerpt' => 'Hellimli Kahvaltı Tavası tarifi.',
                'description' => 'Nefis ve pratik bir Hellimli Kahvaltı Tavası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Kıbrıs',
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
            'duration_seconds' => 551,
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
            'duration_seconds' => 501,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 285,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 256,
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
            'duration_seconds' => 350,
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

        // Recipe: Beyaz Peynirli Omlet
        $slug = 'beyaz-peynirli-omlet';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Beyaz Peynirli Omlet',
                'slug' => $slug,
                'excerpt' => 'Beyaz Peynirli Omlet tarifi.',
                'description' => 'Nefis ve pratik bir Beyaz Peynirli Omlet tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Beyaz Peynir');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '50',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 50,
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
            'duration_seconds' => 336,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 127,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 299,
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
            'duration_seconds' => 270,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 352,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 130,
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

        // Recipe: Sarımsaklı Yumurta
        $slug = 'sarimsakli-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Sarımsaklı Yumurta',
                'slug' => $slug,
                'excerpt' => 'Sarımsaklı Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Sarımsaklı Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 289,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 436,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 508,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 425,
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
            'duration_seconds' => 174,
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

        // Recipe: Pastırmalı Yumurta
        $slug = 'pastirmali-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Pastırmalı Yumurta',
                'slug' => $slug,
                'excerpt' => 'Pastırmalı Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Pastırmalı Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pastırma');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '50',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 50,
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
            'duration_seconds' => 459,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 341,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 479,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 181,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 249,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 502,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 422,
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

        // Recipe: Sucuklu Yumurta
        $slug = 'sucuklu-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Sucuklu Yumurta',
                'slug' => $slug,
                'excerpt' => 'Sucuklu Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Sucuklu Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Sucuk');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '100',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 100,
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
            'duration_seconds' => 460,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 472,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 302,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 246,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 494,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 124,
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

        // Recipe: Tulum Peynirli Gözleme
        $slug = 'tulum-peynirli-gozleme';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Tulum Peynirli Gözleme',
                'slug' => $slug,
                'excerpt' => 'Tulum Peynirli Gözleme tarifi.',
                'description' => 'Nefis ve pratik bir Tulum Peynirli Gözleme tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 470,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 431,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 502,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 439,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 477,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 402,
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

        // Recipe: Ispanaklı Yumurta
        $slug = 'ispanakli-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Ispanaklı Yumurta',
                'slug' => $slug,
                'excerpt' => 'Ispanaklı Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Ispanaklı Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 589,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 162,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 467,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 207,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 199,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 239,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 302,
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

        // Recipe: Kıbrıs Peynirleri Tabağı
        $slug = 'kibris-peynirleri-tabagi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $category->id,
                'title' => 'Kıbrıs Peynirleri Tabağı',
                'slug' => $slug,
                'excerpt' => 'Kıbrıs Peynirleri Tabağı tarifi.',
                'description' => 'Nefis ve pratik bir Kıbrıs Peynirleri Tabağı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Kıbrıs',
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
            'duration_seconds' => 446,
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
            'duration_seconds' => 312,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 591,
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
            'duration_seconds' => 490,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 280,
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

        // Recipe: Zeytinli Kahvaltı Sofrası
        $slug = 'zeytinli-kahvalti';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Zeytinli Kahvaltı Sofrası',
                'slug' => $slug,
                'excerpt' => 'Zeytinli Kahvaltı Sofrası tarifi.',
                'description' => 'Nefis ve pratik bir Zeytinli Kahvaltı Sofrası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 231,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 285,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 290,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 214,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 586,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 231,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 337,
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

        // Recipe: Avokadolu Tost
        $slug = 'avokadolu-tost';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Avokadolu Tost',
                'slug' => $slug,
                'excerpt' => 'Avokadolu Tost tarifi.',
                'description' => 'Nefis ve pratik bir Avokadolu Tost tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 150,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 309,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 322,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 519,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 286,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 134,
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

        // Recipe: Granola ve Yoğurt Kasesi
        $slug = 'granola-ve-yogurt';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Granola ve Yoğurt Kasesi',
                'slug' => $slug,
                'excerpt' => 'Granola ve Yoğurt Kasesi tarifi.',
                'description' => 'Nefis ve pratik bir Granola ve Yoğurt Kasesi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 440,
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
            'duration_seconds' => 308,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 176,
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
            'duration_seconds' => 275,
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

        // Recipe: Pekmez ve Tahin
        $slug = 'pekmezli-tahin';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Pekmez ve Tahin',
                'slug' => $slug,
                'excerpt' => 'Pekmez ve Tahin tarifi.',
                'description' => 'Nefis ve pratik bir Pekmez ve Tahin tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 196,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 569,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 214,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 252,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 534,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 255,
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

        // Recipe: Ev Yapımı Reçelli Tost
        $slug = 'ev-yapimi-receli-tost';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Ev Yapımı Reçelli Tost',
                'slug' => $slug,
                'excerpt' => 'Ev Yapımı Reçelli Tost tarifi.',
                'description' => 'Nefis ve pratik bir Ev Yapımı Reçelli Tost tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 217,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 369,
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
            'duration_seconds' => 537,
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
            'duration_seconds' => 329,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 240,
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

        // Recipe: Sigara Böreği
        $slug = 'sigara-boregi-kahvalti';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Sigara Böreği',
                'slug' => $slug,
                'excerpt' => 'Sigara Böreği tarifi.',
                'description' => 'Nefis ve pratik bir Sigara Böreği tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 252,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 590,
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
            'duration_seconds' => 498,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 251,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 597,
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

        // Recipe: Patatesli Yumurta
        $slug = 'patatesli-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Patatesli Yumurta',
                'slug' => $slug,
                'excerpt' => 'Patatesli Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Patatesli Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 365,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 203,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 323,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 448,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 315,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 134,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 363,
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

        // Recipe: Mantarlı Peynirli Omlet
        $slug = 'mantar-ve-peynirli-omlet';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Mantarlı Peynirli Omlet',
                'slug' => $slug,
                'excerpt' => 'Mantarlı Peynirli Omlet tarifi.',
                'description' => 'Nefis ve pratik bir Mantarlı Peynirli Omlet tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Beyaz Peynir');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '50',
            'display_measure_code' => 'gram',
            'sort_order' => 7,
            'canonical_quantity' => 50,
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
            'duration_seconds' => 157,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 144,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 249,
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
            'duration_seconds' => 397,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 491,
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

        // Recipe: Domatesli Biberli Sucuk
        $slug = 'domates-biberli-sucuk';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Domatesli Biberli Sucuk',
                'slug' => $slug,
                'excerpt' => 'Domatesli Biberli Sucuk tarifi.',
                'description' => 'Nefis ve pratik bir Domatesli Biberli Sucuk tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 153,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 306,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 136,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 462,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 202,
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
            'duration_seconds' => 165,
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

        // Recipe: Kuru Fasulye
        $slug = 'kuru-fasulye-kahvalti';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Kuru Fasulye',
                'slug' => $slug,
                'excerpt' => 'Kuru Fasulye tarifi.',
                'description' => 'Nefis ve pratik bir Kuru Fasulye tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 435,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 337,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 477,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 534,
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
            'duration_seconds' => 469,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 246,
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

        // Recipe: Haydari
        $slug = 'haydari';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Haydari',
                'slug' => $slug,
                'excerpt' => 'Haydari tarifi.',
                'description' => 'Nefis ve pratik bir Haydari tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 598,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 476,
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
            'duration_seconds' => 346,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 202,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 159,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 561,
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

        // Recipe: Kısır Tabağı
        $slug = 'kisir-kahvalti';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Kısır Tabağı',
                'slug' => $slug,
                'excerpt' => 'Kısır Tabağı tarifi.',
                'description' => 'Nefis ve pratik bir Kısır Tabağı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 397,
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
            'duration_seconds' => 325,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 539,
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
            'duration_seconds' => 425,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 318,
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

        // Recipe: Bal ve Kaymak
        $slug = 'bal-ve-kaymak';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Bal ve Kaymak',
                'slug' => $slug,
                'excerpt' => 'Bal ve Kaymak tarifi.',
                'description' => 'Nefis ve pratik bir Bal ve Kaymak tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 594,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 526,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 166,
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
            'duration_seconds' => 128,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 449,
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

        // Recipe: Sebzeli Köfte
        $slug = 'sebzeli-kofte-kahvalti';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Sebzeli Köfte',
                'slug' => $slug,
                'excerpt' => 'Sebzeli Köfte tarifi.',
                'description' => 'Nefis ve pratik bir Sebzeli Köfte tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 300,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 588,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 325,
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
            'duration_seconds' => 316,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 286,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 241,
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

        // Recipe: Puf Böreği
        $slug = 'borek-kahvalti';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Puf Böreği',
                'slug' => $slug,
                'excerpt' => 'Puf Böreği tarifi.',
                'description' => 'Nefis ve pratik bir Puf Böreği tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 246,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 350,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 459,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 392,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 225,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 349,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 501,
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

        // Recipe: Mercimek Çorbası
        $slug = 'mercimek-corbasi-sabah';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Mercimek Çorbası',
                'slug' => $slug,
                'excerpt' => 'Mercimek Çorbası tarifi.',
                'description' => 'Nefis ve pratik bir Mercimek Çorbası tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 173,
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
            'duration_seconds' => 435,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 276,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 495,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 564,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 193,
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

        // Recipe: Taze Meyve Tabağı
        $slug = 'taze-meyve-tabagi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Taze Meyve Tabağı',
                'slug' => $slug,
                'excerpt' => 'Taze Meyve Tabağı tarifi.',
                'description' => 'Nefis ve pratik bir Taze Meyve Tabağı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 240,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 471,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 408,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 196,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 488,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 581,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 219,
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

        // Recipe: Müsli ve Süt
        $slug = 'muesli-turkce';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Müsli ve Süt',
                'slug' => $slug,
                'excerpt' => 'Müsli ve Süt tarifi.',
                'description' => 'Nefis ve pratik bir Müsli ve Süt tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 555,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 408,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 295,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 485,
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
            'duration_seconds' => 276,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 483,
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

        // Recipe: Sade Omlet
        $slug = 'sade-omlet';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Sade Omlet',
                'slug' => $slug,
                'excerpt' => 'Sade Omlet tarifi.',
                'description' => 'Nefis ve pratik bir Sade Omlet tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 204,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 190,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 217,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 157,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 202,
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
            'duration_seconds' => 468,
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

        // Recipe: Haşlanmış Yumurta
        $slug = 'kaynamis-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Haşlanmış Yumurta',
                'slug' => $slug,
                'excerpt' => 'Haşlanmış Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Haşlanmış Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 429,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 180,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 184,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 269,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 255,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 204,
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

        // Recipe: Francala ve Tereyağı
        $slug = 'francala-ve-tereyag';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Francala ve Tereyağı',
                'slug' => $slug,
                'excerpt' => 'Francala ve Tereyağı tarifi.',
                'description' => 'Nefis ve pratik bir Francala ve Tereyağı tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 548,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 239,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 289,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 414,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 249,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 239,
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

        // Recipe: Croque Monsieur
        $slug = 'croque-monsieur-tr';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Croque Monsieur',
                'slug' => $slug,
                'excerpt' => 'Croque Monsieur tarifi.',
                'description' => 'Nefis ve pratik bir Croque Monsieur tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 228,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 397,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 170,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 497,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 459,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 316,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 244,
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

        // Recipe: Avize Peynirli Tost
        $slug = 'avize-peynirli-tost';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Avize Peynirli Tost',
                'slug' => $slug,
                'excerpt' => 'Avize Peynirli Tost tarifi.',
                'description' => 'Nefis ve pratik bir Avize Peynirli Tost tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 294,
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
            'duration_seconds' => 194,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 577,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 271,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 242,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 300,
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

        // Recipe: Türk Kahvesi Yanı Lokum
        $slug = 'turk-kahvesi-yaninda';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Türk Kahvesi Yanı Lokum',
                'slug' => $slug,
                'excerpt' => 'Türk Kahvesi Yanı Lokum tarifi.',
                'description' => 'Nefis ve pratik bir Türk Kahvesi Yanı Lokum tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 360,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 306,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 539,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 443,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 262,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 369,
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

        // Recipe: Saç Böreği
        $slug = 'sac-boregi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Saç Böreği',
                'slug' => $slug,
                'excerpt' => 'Saç Böreği tarifi.',
                'description' => 'Nefis ve pratik bir Saç Böreği tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 239,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 559,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 456,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 529,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 354,
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
            'duration_seconds' => 333,
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

        // Recipe: Domatesli Salçalı Yumurta
        $slug = 'domates-salcali-yumurta';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Domatesli Salçalı Yumurta',
                'slug' => $slug,
                'excerpt' => 'Domatesli Salçalı Yumurta tarifi.',
                'description' => 'Nefis ve pratik bir Domatesli Salçalı Yumurta tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 469,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 132,
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
            'duration_seconds' => 217,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 362,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 178,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 194,
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

        // Recipe: Kuru Kayısı ve Ceviz
        $slug = 'kuru-kayisi-ve-ceviz';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Kuru Kayısı ve Ceviz',
                'slug' => $slug,
                'excerpt' => 'Kuru Kayısı ve Ceviz tarifi.',
                'description' => 'Nefis ve pratik bir Kuru Kayısı ve Ceviz tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 188,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 476,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 592,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 565,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 549,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 478,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 189,
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

        // Recipe: Tereyağlı Ekmek
        $slug = 'tereyagli-ekmek';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Tereyağlı Ekmek',
                'slug' => $slug,
                'excerpt' => 'Tereyağlı Ekmek tarifi.',
                'description' => 'Nefis ve pratik bir Tereyağlı Ekmek tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 433,
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
            'duration_seconds' => 568,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 283,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 247,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 368,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 370,
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

        // Recipe: Kıbrıs Anari Peyniri ve Bal
        $slug = 'kibris-anari-tatli';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $category->id,
                'title' => 'Kıbrıs Anari Peyniri ve Bal',
                'slug' => $slug,
                'excerpt' => 'Kıbrıs Anari Peyniri ve Bal tarifi.',
                'description' => 'Nefis ve pratik bir Kıbrıs Anari Peyniri ve Bal tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Kıbrıs',
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
            'duration_seconds' => 482,
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
            'duration_seconds' => 230,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 345,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 546,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 388,
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

        // Recipe: Sabah Smoothie Kasesi
        $slug = 'sabah-smoothiesi';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Sabah Smoothie Kasesi',
                'slug' => $slug,
                'excerpt' => 'Sabah Smoothie Kasesi tarifi.',
                'description' => 'Nefis ve pratik bir Sabah Smoothie Kasesi tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 427,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 153,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 276,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 364,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 599,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 249,
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

        // Recipe: Kepekli Gözleme
        $slug = 'kepekli-gozleme';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Kepekli Gözleme',
                'slug' => $slug,
                'excerpt' => 'Kepekli Gözleme tarifi.',
                'description' => 'Nefis ve pratik bir Kepekli Gözleme tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 444,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 580,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 150,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 147,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 421,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 370,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 371,
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

        // Recipe: Türk Usulü Krep
        $slug = 'krep-turkce';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Türk Usulü Krep',
                'slug' => $slug,
                'excerpt' => 'Türk Usulü Krep tarifi.',
                'description' => 'Nefis ve pratik bir Türk Usulü Krep tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 184,
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
            'duration_seconds' => 293,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 217,
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
            'duration_seconds' => 519,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 544,
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

        // Recipe: Badem Sütlü Yulaf
        $slug = 'badem-sutlu-yulaf';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Badem Sütlü Yulaf',
                'slug' => $slug,
                'excerpt' => 'Badem Sütlü Yulaf tarifi.',
                'description' => 'Nefis ve pratik bir Badem Sütlü Yulaf tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 279,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 122,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 561,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 514,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 392,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 180,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 139,
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

        // Recipe: Taze Nar Suyu ve Tahıllı Kraker
        $slug = 'taze-nar-suyu';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Taze Nar Suyu ve Tahıllı Kraker',
                'slug' => $slug,
                'excerpt' => 'Taze Nar Suyu ve Tahıllı Kraker tarifi.',
                'description' => 'Nefis ve pratik bir Taze Nar Suyu ve Tahıllı Kraker tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 299,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 547,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 301,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 426,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 568,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 246,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 225,
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

        // Recipe: Muhamara Ekmek Üstü
        $slug = 'muhammara-ekmekulustu';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Muhamara Ekmek Üstü',
                'slug' => $slug,
                'excerpt' => 'Muhamara Ekmek Üstü tarifi.',
                'description' => 'Nefis ve pratik bir Muhamara Ekmek Üstü tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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
            'duration_seconds' => 122,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 591,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 494,
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
            'duration_seconds' => 301,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 127,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 208,
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

        // Recipe: Acılı Omlet
        $slug = 'acili-omlet';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $turk->id,
                'category_id' => $category->id,
                'title' => 'Acılı Omlet',
                'slug' => $slug,
                'excerpt' => 'Acılı Omlet tarifi.',
                'description' => 'Nefis ve pratik bir Acılı Omlet tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Türkiye',
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

            
        $ingId = $ing('Yumurta');
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

        $ingId = $ing('Tereyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 2,
            'canonical_quantity' => 1,
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
            'sort_order' => 3,
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
            'sort_order' => 4,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Zeytinyağı');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'yemek_kasigi',
            'sort_order' => 5,
            'canonical_quantity' => 1,
            'canonical_unit' => 'yemek_kasigi',
            'scaling_behavior' => 'scalable',
            'is_optional' => false,
            'conversion_confidence' => 'verified',
        ]);

        $ingId = $ing('Pul Biber');
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingId,
            'display_quantity' => '1',
            'display_measure_code' => 'cay_kasigi',
            'sort_order' => 6,
            'canonical_quantity' => 1,
            'canonical_unit' => 'cay_kasigi',
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
            'duration_seconds' => 488,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 395,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 494,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 521,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 520,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 191,
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

        // Recipe: Anari ve Bal Tost
        $slug = 'anari-ve-bal-tost';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $category->id,
                'title' => 'Anari ve Bal Tost',
                'slug' => $slug,
                'excerpt' => 'Anari ve Bal Tost tarifi.',
                'description' => 'Nefis ve pratik bir Anari ve Bal Tost tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Kıbrıs',
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
            'duration_seconds' => 288,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 570,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 304,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 230,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 493,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 528,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 484,
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

        // Recipe: Hellimli Sigara Böreği
        $slug = 'sigara-boregi-hellimli';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $category->id,
                'title' => 'Hellimli Sigara Böreği',
                'slug' => $slug,
                'excerpt' => 'Hellimli Sigara Böreği tarifi.',
                'description' => 'Nefis ve pratik bir Hellimli Sigara Böreği tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Kıbrıs',
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
            'duration_seconds' => 196,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 406,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 420,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 392,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 532,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 289,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 253,
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

        // Recipe: Kıbrıs Lokması
        $slug = 'kibris-lokma';
        if (Recipe::where('slug', $slug)->exists()) {
            $this->command->line("SKIP (exists): {$slug}");
        } else {
            $recipe = Recipe::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'cuisine_id' => $kibris->id,
                'category_id' => $category->id,
                'title' => 'Kıbrıs Lokması',
                'slug' => $slug,
                'excerpt' => 'Kıbrıs Lokması tarifi.',
                'description' => 'Nefis ve pratik bir Kıbrıs Lokması tarifi. Evde kolayca hazırlayabilirsiniz.',
                'country' => 'Kıbrıs',
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
            'duration_seconds' => 435,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 2,
            'title' => 'Adım 2',
            'instruction' => 'Ön hazırlık işlemlerini tamamlayın (doğrama, karıştırma vb.).',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 201,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 3,
            'title' => 'Adım 3',
            'instruction' => 'Tencereyi veya tavayı ocağa alıp ısıtın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 398,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 4,
            'title' => 'Adım 4',
            'instruction' => 'Ana malzemeleri sırasıyla ekleyip pişirmeye başlayın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 588,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 5,
            'title' => 'Adım 5',
            'instruction' => 'Baharatları ve diğer tatlandırıcıları ilave edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 143,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 6,
            'title' => 'Adım 6',
            'instruction' => 'Belirtilen süre kadar pişirmeye devam edin.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 236,
            'timer_required' => false,
            'is_optional' => false,
        ]);

        RecipeStep::create([
            'recipe_id' => $recipe->id,
            'position' => 7,
            'title' => 'Adım 7',
            'instruction' => 'Sıcak veya soğuk olarak servis yapın.',
            'chef_tip' => 'Dikkatli olun ve sürekli kontrol edin.',
            'duration_seconds' => 426,
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
        $this->command->info("✓ KahvaltiTarifleriSeeder tamamlandı. Toplam yayında: {$total}");
    }
}
