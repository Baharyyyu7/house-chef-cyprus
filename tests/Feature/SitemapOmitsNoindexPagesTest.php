<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yayındaki 678 tarifin 450'si inceleme beklediği için noindex,nofollow
 * taşıyor -- doğru olan da bu -- ama hepsi sitemap'e giriyordu. Aynı sayfa
 * için Google'a "indeksle" ve "indeksleme" demek Search Console'da
 * "gönderilen URL noindex işaretli" hatası üretir.
 */
final class SitemapOmitsNoindexPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_noindex_recipe_is_left_out_of_the_recipe_sitemap(): void
    {
        $indexlenebilir = $this->recipe('gercek-tarif', 'index,follow');
        $noindex = $this->recipe('inceleme-bekleyen-tarif', 'noindex,nofollow');

        $this->get('/sitemaps/recipes.xml')
            ->assertOk()
            ->assertSee($indexlenebilir->slug, false)
            ->assertDontSee($noindex->slug, false);
    }

    public function test_a_recipe_without_seo_metadata_stays_in_the_sitemap(): void
    {
        // Metadata kaydı hiç yoksa varsayılan indekslenebilirdir; eksik kayıt
        // sayfayı sitemap'ten düşürmemeli.
        $recipe = Recipe::query()->create([
            'title' => 'Metadatasız Tarif',
            'slug' => 'metadatasiz-tarif',
            'status' => 'published',
            'verification_status' => 'verified',
        ]);

        $this->get('/sitemaps/recipes.xml')->assertOk()->assertSee($recipe->slug, false);
    }

    public function test_a_noindex_post_is_left_out_of_the_blog_sitemap(): void
    {
        $gorunur = $this->yazi('yayinlanan-yazi', 'index,follow');
        $gizli = $this->yazi('inceleme-bekleyen-yazi', 'noindex,nofollow');

        $this->get('/sitemaps/blog.xml')
            ->assertOk()
            ->assertSee($gorunur->slug, false)
            ->assertDontSee($gizli->slug, false);
    }

    private function recipe(string $slug, string $robots): Recipe
    {
        $recipe = Recipe::query()->create([
            'title' => str($slug)->headline()->value(),
            'slug' => $slug,
            'status' => 'published',
            'verification_status' => 'verified',
        ]);

        $recipe->seoMetadata()->create(['robots' => $robots]);

        return $recipe;
    }

    private function yazi(string $slug, string $robots): BlogPost
    {
        $post = BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => str($slug)->headline()->value(),
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $post->seoMetadata()->create(['robots' => $robots]);

        return $post;
    }
}
