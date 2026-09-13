<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class GeoSeoBacklink100BlogsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('100 yeni GEO, SEO ve Backlink odakli blog makalesi yukleniyor...');

        $author = User::first();
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'kibris-mutfagi'],
            ['name' => 'Kıbrıs Mutfağı', 'public_id' => strtoupper((string) Str::ulid())]
        );

        $seedsPath = __DIR__ . '/100_geo_blogs_seeds.json';
        $articles = json_decode((string) file_get_contents($seedsPath), true) ?: [];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($articles as $art) {
            if (BlogPost::where('slug', $art['slug'])->exists()) {
                $skippedCount++;
                continue;
            }

            BlogPost::create([
                'public_id' => strtoupper((string) Str::ulid()),
                'author_id' => $author?->id,
                'blog_category_id' => $category->id,
                'title' => $art['title'],
                'slug' => $art['slug'],
                'primary_keyword' => $art['primary_keyword'],
                'secondary_keywords' => $art['secondary_keywords'],
                'excerpt' => $art['excerpt'],
                'body' => $art['body'],
                'status' => 'published',
                'editorial_status' => 'verified',
                'fact_check_status' => 'verified',
                'originality_status' => 'original',
                'freshness_class' => 'evergreen',
                'published_at' => now(),
                'meta_title' => Str::limit((string) $art['title'], 68, ''),
                'meta_description' => Str::limit((string) $art['excerpt'], 155, ''),
            ]);
            $createdCount++;
        }

        $this->command->info("100 Blog Seeder Tamamlandi! {$createdCount} yeni eklendi, {$skippedCount} atlandi.");
    }
}
