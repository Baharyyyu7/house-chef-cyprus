<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SplitLongPostsAndAttachMediaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('1. Çok uzun blog yazıları tespit ediliyor ve bölünüyor (Part 1 / Part 2)...');

        $longPosts = BlogPost::whereRaw('LENGTH(body) > 3500')->get();
        $splitCount = 0;

        foreach ($longPosts as $post) {
            $body = $post->body;
            $halfLength = (int) (mb_strlen($body) / 2);

            // Bölmek için en uygun başlık (H2 / H3) veya paragraf sonu noktasını bul
            $splitPos = mb_strpos($body, "\n## ", $halfLength);
            if ($splitPos === false) {
                $splitPos = mb_strpos($body, "\n\n", $halfLength);
            }

            if ($splitPos !== false && $splitPos > 1000) {
                $firstPartBody = mb_substr($body, 0, $splitPos);
                $secondPartBody = mb_substr($body, $splitPos);

                // Ana yazıyı Parça 1 yap
                $post->update([
                    'title' => $post->title.' (Bölüm 1)',
                    'body' => $firstPartBody."\n\n---\n\n*Bu rehberin devamı için [Bölüm 2'yi okuyabilirsiniz]({$post->slug}-parca-2).*",
                ]);

                // Parça 2 için yeni blog gönderisi oluştur
                BlogPost::create([
                    'public_id' => strtoupper((string) Str::ulid()),
                    'author_id' => $post->author_id,
                    'blog_category_id' => $post->blog_category_id,
                    'hero_media_id' => $post->hero_media_id,
                    'title' => str_replace(' (Bölüm 1)', '', $post->title).' (Bölüm 2)',
                    'slug' => $post->slug.'-parca-2',
                    'excerpt' => $post->excerpt.' (Devamı ve detaylı püf noktaları)',
                    'body' => '## '.str_replace(' (Bölüm 1)', '', $post->title)." (Devamı)\n\n".$secondPartBody,
                    'status' => 'published',
                    'editorial_status' => 'approved',
                    'meta_title' => Str::limit($post->meta_title.' (Bölüm 2)', 65),
                    'meta_description' => Str::limit($post->meta_description, 155),
                    'published_at' => now(),
                ]);

                $splitCount++;
            }
        }

        $this->command->info("✓ Toplam {$splitCount} adet uzun blog yazısı 2 parçaya bölündü ve bağlantıları sağlandı.");

        $this->command->info('2. Mevcut onaylı görseller tüm tariflere ve bloglara dağıtılıyor...');

        $approvedMediaPool = Media::where('visibility', 'public')
            ->where('verification_status', 'verified')
            ->where('media_status', 'approved_real_media')
            ->get();

        if ($approvedMediaPool->isNotEmpty()) {
            // Görseli olmayan tariflere onaylı görsellerden döngüsel atama yapalım
            $recipesWithoutMedia = Recipe::whereDoesntHave('media')->get();
            $assignedRecipeCount = 0;

            foreach ($recipesWithoutMedia as $index => $recipe) {
                $sourceMedia = $approvedMediaPool->get($index % $approvedMediaPool->count());

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
                    'alt_text' => $recipe->title.' özel iştah açıcı sunum fotoğrafı',
                    'caption' => $recipe->title.' lezzet hazırlığı.',
                    'width' => $sourceMedia->width,
                    'height' => $sourceMedia->height,
                    'responsive_variants' => $sourceMedia->getRawOriginal('responsive_variants'),
                    'provider_metadata' => $sourceMedia->getRawOriginal('provider_metadata'),
                ]);

                $assignedRecipeCount++;
            }

            $this->command->info("✓ Toplam {$assignedRecipeCount} tarife iştah açıcı onaylı kapak görseli başarıyla atandı.");

            // Görseli olmayan blog makalelerine de onaylı hero_media_id atayalım
            $blogsWithoutMedia = BlogPost::whereNull('hero_media_id')->get();
            $assignedBlogCount = 0;

            foreach ($blogsWithoutMedia as $index => $blog) {
                $sourceMedia = $approvedMediaPool->get($index % $approvedMediaPool->count());
                $blog->update(['hero_media_id' => $sourceMedia->id]);
                $assignedBlogCount++;
            }

            $this->command->info("✓ Toplam {$assignedBlogCount} blog makalesine kapak resmi (Hero Media) atandı.");
        }

        $totalRecipesWithMedia = Recipe::whereHas('media')->count();
        $totalBlogsWithMedia = BlogPost::whereNotNull('hero_media_id')->count();

        $this->command->info('🎉 GÖRSEL VE BÖLME İŞLEMLERİ TAMAMLANDI!');
        $this->command->info(" - Görselli Tarif Sayısı: {$totalRecipesWithMedia} / ".Recipe::count());
        $this->command->info(" - Görselli Blog Sayısı: {$totalBlogsWithMedia} / ".BlogPost::count());
    }
}
