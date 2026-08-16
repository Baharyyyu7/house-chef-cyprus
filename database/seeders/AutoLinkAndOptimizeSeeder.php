<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AutoLinkAndOptimizeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('1. Blog yazıları ile Tarifler arasında otomatik SEO İç Bağlantıları (Internal Links) kuruluyor...');

        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->get(['id', 'title', 'slug', 'category_id', 'cuisine_id']);

        $posts = BlogPost::query()->publiclyVisible()->get(['id', 'title', 'slug', 'blog_category_id', 'hero_media_id']);

        $linkedCount = 0;
        $heroAssignedCount = 0;

        foreach ($posts as $post) {
            $matchedIds = [];

            // 1. Başlık kelime eşleşmesi
            $titleWords = array_filter(
                explode(' ', Str::lower(preg_replace('/[^\w\s]/u', '', $post->title))),
                fn ($w) => mb_strlen($w) > 3
            );

            if (!empty($titleWords)) {
                $matchedRecipes = $recipes->filter(function ($recipe) use ($titleWords) {
                    $recipeTitle = Str::lower($recipe->title);
                    foreach ($titleWords as $word) {
                        if (str_contains($recipeTitle, $word)) {
                            return true;
                        }
                    }
                    return false;
                })->take(3);

                $matchedIds = $matchedRecipes->pluck('id')->all();
            }

            // 2. Eğer kelime eşleşmesi yetersizse, rastgele 2 popüler tarifle bağla
            if (count($matchedIds) < 2) {
                $fallbackIds = $recipes->random(min(3, $recipes->count()))->pluck('id')->all();
                $matchedIds = array_unique(array_merge($matchedIds, $fallbackIds));
            }

            if (!empty($matchedIds)) {
                $post->recipes()->syncWithoutDetaching($matchedIds);
                $linkedCount++;

                // Eğer blog'un hero_media_id'si yoksa, bağlı tariflerden ilk medya sahibi olanın görselini ata
                if (!$post->hero_media_id) {
                    $recipeWithMedia = Recipe::whereIn('id', $matchedIds)
                        ->whereHas('media')
                        ->first();

                    if ($recipeWithMedia) {
                        $media = $recipeWithMedia->media()->first();
                        if ($media) {
                            $post->update(['hero_media_id' => $media->id]);
                            $heroAssignedCount++;
                        }
                    }
                }
            }
        }

        $this->command->info("✓ Toplam {$linkedCount} blog gönderisi ilgili tariflerle çapraz bağlandı (Internal Link).");
        $this->command->info("✓ Toplam {$heroAssignedCount} blog gönderisine tarif görselleri kapak resmi (Hero Media) olarak atandı.");
    }
}
