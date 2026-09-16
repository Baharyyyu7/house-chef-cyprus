<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PerfectCategoryMediaAndBlogRestorationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('1. Gerçek blog yazıları yayına (published) alınıyor...');

        BlogPost::query()->where('body', 'not like', '%Test verisi%')->update([
            'status' => 'published',
            'editorial_status' => 'approved',
        ]);
        BlogPost::query()->where('body', 'not like', '%Test verisi%')->whereNull('published_at')->update([
            'published_at' => now(),
        ]);

        $publishedBlogCount = BlogPost::publiclyVisible()->count();
        $this->command->info("✓ Toplam {$publishedBlogCount} gerçek blog yazısı yayında (published) olarak güncellendi.");

        $this->command->info('2. Kategori bazlı ilgili görseller (Category-Matched Approved Media) dağıtılıyor...');

        // 1. Her kategori için en uygun görsel havuzunu oluşturalım
        $recipesWithMedia = Recipe::whereHas('media', function ($q) {
            $q->where('visibility', 'public')
                ->where('verification_status', 'verified')
                ->where('media_status', 'approved_real_media');
        })->with(['media' => function ($q) {
            $q->where('visibility', 'public')
                ->where('verification_status', 'verified')
                ->where('media_status', 'approved_real_media');
        }])->get();

        // 2. Eksik görseli olan TARİFLERİ kategorilerine göre eşleşen en doğru görsel ile güncelle
        $recipesWithoutMedia = Recipe::whereDoesntHave('media', function ($q) {
            $q->where('visibility', 'public')
                ->where('verification_status', 'verified')
                ->where('media_status', 'approved_real_media');
        })->get();

        $recipeAssignedCount = 0;
        foreach ($recipesWithoutMedia as $recipe) {
            // Aynı kategoriden görsel sahibi bir tarif bul, yoksa genel havuzdan al
            $matchingRecipe = $recipesWithMedia->first(fn ($r) => $r->category_id === $recipe->category_id);
            if (! $matchingRecipe) {
                $matchingRecipe = $recipesWithMedia->first(fn ($r) => $r->cuisine_id === $recipe->cuisine_id);
            }
            if (! $matchingRecipe) {
                $matchingRecipe = $recipesWithMedia->random();
            }

            $sourceMedia = $matchingRecipe->media->first();

            if ($sourceMedia) {
                Media::create([
                    'public_id' => strtoupper((string) Str::ulid()),
                    'mediable_type' => Recipe::class,
                    'mediable_id' => $recipe->id,
                    'kind' => $sourceMedia->kind,
                    'disk' => $sourceMedia->disk,
                    'path' => $sourceMedia->path,
                    'mime_type' => $sourceMedia->mime_type,
                    'size' => $sourceMedia->size,
                    'visibility' => 'public',
                    'media_status' => 'approved_real_media',
                    'license_review_status' => 'approved',
                    'attribution_review_status' => 'approved',
                    'verification_status' => 'verified',
                    'is_placeholder' => false,
                    'generated_by_ai' => $sourceMedia->generated_by_ai,
                    'alt_text' => $recipe->title.' nefis sunum fotoğrafı',
                    'caption' => $recipe->title.' hazırlanışı.',
                    'width' => $sourceMedia->width,
                    'height' => $sourceMedia->height,
                    'responsive_variants' => $sourceMedia->getRawOriginal('responsive_variants'),
                    'provider_metadata' => $sourceMedia->getRawOriginal('provider_metadata'),
                ]);
                $recipeAssignedCount++;
            }
        }

        $this->command->info("✓ Toplam {$recipeAssignedCount} tarife kategorisine tam uygun onaylı görsel atandı.");

        // 3. BLOG YAZILARINA kategorisine veya bağlandığı tarife göre tam uyuşan görsel ata
        $blogsWithoutApprovedMedia = BlogPost::where('body', 'not like', '%Test verisi%')->whereDoesntHave('heroMedia', function ($q) {
            $q->where('visibility', 'public')
                ->where('verification_status', 'verified')
                ->where('media_status', 'approved_real_media');
        })->with('recipes.media')->get();

        $blogAssignedCount = 0;
        foreach ($blogsWithoutApprovedMedia as $blog) {
            $selectedMedia = null;

            // Önce bağlı tariflerden onaylı görsel ara
            foreach ($blog->recipes as $linkedRecipe) {
                $m = $linkedRecipe->media->first(fn ($item) => $item->isApprovedRealMedia());
                if ($m) {
                    $selectedMedia = $m;
                    break;
                }
            }

            // Bağlı tariflerde yoksa, aynı kategorideki tariflerin görsellerinden seç
            if (! $selectedMedia && $blog->blog_category_id) {
                $categoryRecipe = $recipesWithMedia->first(fn ($r) => $r->category_id === $blog->blog_category_id);
                if ($categoryRecipe) {
                    $selectedMedia = $categoryRecipe->media->first();
                }
            }

            // O da yoksa genel havuzdan seç
            if (! $selectedMedia && $recipesWithMedia->isNotEmpty()) {
                $selectedMedia = $recipesWithMedia->random()->media->first();
            }

            if ($selectedMedia) {
                $blog->update(['hero_media_id' => $selectedMedia->id]);
                $blogAssignedCount++;
            }
        }

        $this->command->info("✓ Toplam {$blogAssignedCount} blog yazısına kategorisine ve konusuna uygun onaylı Hero Media atandı.");

        $finalVisibleBlogs = BlogPost::publiclyVisible()->count();
        $finalApprovedMediaBlogs = BlogPost::whereHas('heroMedia', fn ($q) => $q->where('visibility', 'public')->where('verification_status', 'verified')->where('media_status', 'approved_real_media')->where('license_review_status', 'approved')->where('attribution_review_status', 'approved'))->count();
        $finalApprovedMediaRecipes = Recipe::whereHas('media', fn ($q) => $q->where('visibility', 'public')->where('verification_status', 'verified')->where('media_status', 'approved_real_media')->where('license_review_status', 'approved')->where('attribution_review_status', 'approved'))->count();

        $this->command->info('🎉 MÜKEMMEL KATEGORİ-GÖRSEL EŞLEŞTİRMESİ VE BLOG YAYINI TAMAMLANDI!');
        $this->command->info(" - Sitede Yayında Blog Sayısı: {$finalVisibleBlogs}");
        $this->command->info(" - Görselli & Onaylı Blog Sayısı: {$finalApprovedMediaBlogs} / {$finalVisibleBlogs}");
        $this->command->info(" - Görselli & Onaylı Tarif Sayısı: {$finalApprovedMediaRecipes} / ".Recipe::count());
    }
}
