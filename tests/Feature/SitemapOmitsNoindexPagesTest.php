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
        $this->makeRecipePubliclyEligible($recipe);

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
        $this->makeRecipePubliclyEligible($recipe);

        return $recipe;
    }

    private function yazi(string $slug, string $robots): BlogPost
    {
        $post = BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => str($slug)->headline()->value(),
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $post->seoMetadata()->create(['robots' => $robots]);

        return $post;
    }

    public function test_o_dilde_ince_kalan_ceviri_site_haritasina_girmez(): void
    {
        // Eşik gövdenin o dildeki çevirisine uygulanıyor; makine çevirisi
        // çoğu zaman Türkçesinden kısa çıkıyor. Türkçesi kapıdan geçen bir
        // yazının Farsça adresi noindex dönerken haritada duruyordu.
        $post = $this->yazi('cok-dilli-yazi', 'index,follow');

        $ingilizce = [
            'title' => 'Multilingual Post',
            'excerpt' => 'Summary.',
            'body' => $this->cesitliMetin('The original body of this post, long enough to carry its subject. ', 40),
        ];

        foreach ($ingilizce as $alan => $deger) {
            $post->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        foreach (['title' => 'پست', 'excerpt' => 'خلاصه.', 'body' => 'متن کوتاه.'] as $alan => $deger) {
            $post->translations()->create(['locale' => 'fa', 'field' => $alan, 'value' => $deger]);
        }

        $cevap = $this->get('/sitemaps/blog.xml')->assertOk();

        $cevap->assertSee('/en/blog/cok-dilli-yazi', false);
        $cevap->assertDontSee('/fa/blog/cok-dilli-yazi', false);
    }
}
