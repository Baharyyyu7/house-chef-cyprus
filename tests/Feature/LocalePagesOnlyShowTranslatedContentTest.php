<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocalePagesOnlyShowTranslatedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    public function test_the_turkish_site_is_unaffected(): void
    {
        // Varsayılan dil çeviri satırı istemez; kaynak sütunların kendisi
        // içeriktir. Kapsam Türkçe tarafı daraltmamalı.
        $tarif = $this->tarif();
        $yazi = $this->yazi();

        $this->get('/tarifler/'.$tarif->slug)->assertOk();
        $this->get('/blog/'.$yazi->slug)->assertOk();
        $this->get('/blog')->assertOk()->assertSee($yazi->title);
    }

    public function test_an_untranslated_recipe_has_no_address_in_that_locale(): void
    {
        // Sessizce Türkçe içerik servis etmek, hreflang'in gösterdiği adresin
        // başka dilde içerik döndürmesi demekti.
        $tarif = $this->tarif();

        $this->get('/en/recipes/'.$tarif->slug)->assertNotFound();
    }

    public function test_an_untranslated_post_has_no_address_in_that_locale(): void
    {
        $yazi = $this->yazi();

        $this->get('/en/blog/'.$yazi->slug)->assertNotFound();
    }

    public function test_the_locale_listing_does_not_show_untranslated_records(): void
    {
        $yazi = $this->yazi();

        $this->get('/en/blog')->assertOk()->assertDontSee($yazi->title);
    }

    public function test_a_translated_record_is_reachable_and_listed(): void
    {
        $yazi = $this->yazi();

        foreach (['title' => 'Translated Post', 'excerpt' => 'Summary.', 'body' => 'Body text.'] as $alan => $deger) {
            $yazi->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        $this->get('/en/blog/'.$yazi->slug)->assertOk()
            ->assertSee('Translated Post')->assertSee('Body text.')
            ->assertDontSee('Çevirisiz Yazı Başlığı')->assertDontSee('Gövde.');
        $this->get('/en/blog')->assertOk()->assertSee('Translated Post')->assertDontSee('Çevirisiz Yazı Başlığı');
        $this->assertSame('Çevirisiz Yazı Başlığı', $yazi->fresh()->getRawOriginal('title'));
    }

    public function test_english_blog_pagination_links_stay_under_the_english_prefix(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $yazi = BlogPost::query()->create([
                'title' => 'Sayfa Yazisi '.$i,
                'slug' => 'sayfa-yazisi-'.$i.'-'.uniqid(),
                'excerpt' => 'Ozet.',
                'body' => 'Govde.',
                'status' => 'published',
                'published_at' => now()->subMinutes($i),
            ]);
            foreach (['title' => 'Paged Post '.$i, 'excerpt' => 'Summary.', 'body' => 'Body text.'] as $alan => $deger) {
                $yazi->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
            }
        }

        $this->get('/en/blog')->assertOk()
            ->assertSee('href="'.url('/en/blog?page=2').'"', false)
            ->assertDontSee('href="'.url('/blog?page=2').'"', false);

        $this->get('/blog')->assertOk()
            ->assertSee('href="'.url('/blog?page=2').'"', false);

        // Sayfa sayısını aşan adres boş 200 yerine 404 döner.
        $this->get('/en/blog?page=2')->assertOk();
        $this->get('/en/blog?page=5')->assertNotFound();
        $this->get('/blog?page=10')->assertNotFound();
    }

    public function test_english_blog_category_pagination_links_stay_under_the_english_prefix(): void
    {
        $kategori = BlogCategory::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::ulid(),
            'name' => 'Sayfali Kategori',
            'slug' => 'sayfali-kategori-'.uniqid(),
            'description' => 'Aciklama.',
        ]);

        for ($i = 1; $i <= 13; $i++) {
            $yazi = BlogPost::query()->create([
                'blog_category_id' => $kategori->id,
                'title' => 'Kategori Yazisi '.$i,
                'slug' => 'kategori-yazisi-'.$i.'-'.uniqid(),
                'excerpt' => 'Ozet.',
                'body' => 'Govde.',
                'status' => 'published',
                'published_at' => now()->subMinutes($i),
            ]);
            foreach (['title' => 'Category Post '.$i, 'excerpt' => 'Summary.', 'body' => 'Body text.'] as $alan => $deger) {
                $yazi->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
            }
        }

        $this->get('/en/blog/kategori/'.$kategori->slug)->assertOk()
            ->assertSee('href="'.url('/en/blog/kategori/'.$kategori->slug.'?page=2').'"', false)
            ->assertDontSee('href="'.url('/blog/kategori/'.$kategori->slug.'?page=2').'"', false);
        $this->get('/en/blog/kategori/'.$kategori->slug.'?page=3')->assertNotFound();
    }

    public function test_recipe_search_uses_translated_titles_and_excludes_untranslated_records(): void
    {
        $translated = $this->tarif();
        $hidden = $this->tarif();
        foreach (['title' => 'Grilled Halloumi', 'excerpt' => 'Crisp cheese.', 'description' => 'Cook until golden.'] as $field => $value) {
            $translated->translations()->create(['locale' => 'en', 'field' => $field, 'value' => $value]);
        }
        $this->get('/en/recipes?q=Halloumi')->assertOk()->assertSee('Grilled Halloumi')
            ->assertDontSee($hidden->slug)->assertDontSee('Hellim Izgara');
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Hellim Izgara',
            'slug' => 'hellim-izgara-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    private function yazi(): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Çevirisiz Yazı Başlığı',
            'slug' => 'cevirisiz-yazi-'.uniqid(),
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
