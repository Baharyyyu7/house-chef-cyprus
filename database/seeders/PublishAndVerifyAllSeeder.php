<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\RecipeProvenance;
use Illuminate\Database\Seeder;

class PublishAndVerifyAllSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('1. Tüm tariflerin doğrulama, editoryal onay ve yayın durumları güncelleniyor...');

        $updatedRecipes = Recipe::query()->update([
            'status' => 'published',
            'verification_status' => 'verified',
            'editorial_status' => 'approved',
            'completeness_status' => 'complete',
        ]);
        
        Recipe::query()->whereNull('published_at')->update([
            'published_at' => now(),
        ]);

        $this->command->info("✓ Toplam {$updatedRecipes} tarif yayınlandı ve doğrulandı.");

        $this->command->info('2. Tarif kanıt ve doğrulama (Provenance) kayıtları güncelleniyor...');

        $updatedProvenances = RecipeProvenance::query()->update([
            'verification_status' => 'verified',
            'confidence' => 90,
            'is_active' => true,
        ]);

        $this->command->info("✓ Toplam {$updatedProvenances} tarif kanıt kaydı doğrulandı.");

        $this->command->info('3. Tüm SEO blog makalelerinin yayın ve onay durumları güncelleniyor...');

        $updatedBlogs = BlogPost::query()->update([
            'status' => 'published',
            'editorial_status' => 'approved',
        ]);

        BlogPost::query()->whereNull('published_at')->update([
            'published_at' => now(),
        ]);

        $this->command->info("✓ Toplam {$updatedBlogs} blog makalesi yayına alındı ve görünür yapıldı.");

        $publishedRecipesCount = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count();
        $publishedBlogsCount = BlogPost::query()->publiclyVisible()->count();

        $this->command->info("🎉 İŞLEM TAMAMLANDI!");
        $this->command->info(" - Sitede Canlı Yayında Tarif Sayısı: {$publishedRecipesCount}");
        $this->command->info(" - Sitede Canlı Yayında Blog Sayısı: {$publishedBlogsCount}");
    }
}
