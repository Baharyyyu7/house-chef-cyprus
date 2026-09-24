<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Recipe;
use App\Models\RecipeTranslation;
use App\Services\Localization\ContentTranslationImporter;
use Database\Seeders\HouseChefSeeder;
use Database\Seeders\ProductionBlogCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnglishEditionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HouseChefSeeder::class);
        $this->seed(ProductionBlogCatalogueSeeder::class);
    }

    public function test_versioned_english_catalogue_imports_cleanly_and_idempotently(): void
    {
        $importer = app(ContentTranslationImporter::class);

        $first = $importer->import('en');
        $this->assertSame([], $first['recipes']['invalid']);
        $this->assertSame([], $first['recipes']['missing_source']);
        $this->assertSame(50, $first['recipes']['imported']);
        $this->assertSame([], $first['blog']['invalid']);
        $this->assertSame([], $first['blog']['missing_source']);
        $this->assertSame(20, $first['blog']['imported']);

        $second = $importer->import('en');
        $this->assertSame(0, $second['recipes']['imported']);
        $this->assertSame(50, $second['recipes']['unchanged']);
        $this->assertSame(20, $second['blog']['unchanged']);

        $this->artisan('content:import-translations --locale=en --json')->assertSuccessful();
        $this->artisan('content:import-translations --locale=tr')->assertFailed();
    }

    public function test_english_recipe_page_is_localized_and_linked_to_its_turkish_original(): void
    {
        app(ContentTranslationImporter::class)->import('en');
        $recipe = Recipe::query()->where('slug', 'menemen')->firstOrFail();
        $translation = RecipeTranslation::query()->where('recipe_id', $recipe->id)->where('locale', 'en')->firstOrFail();
        $englishUrl = route('en.recipes.show', $translation->slug);
        $turkishUrl = route('recipes.show', $recipe);

        $response = $this->get($englishUrl)
            ->assertOk()
            ->assertHeader('Content-Language', 'en')
            ->assertSee('<html lang="en"', false)
            ->assertSee('<link rel="canonical" href="'.$englishUrl.'">', false)
            ->assertSee('<link rel="alternate" hreflang="tr" href="'.$turkishUrl.'">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="'.$englishUrl.'">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.$englishUrl.'">', false)
            ->assertSee('<meta property="og:locale" content="en_GB">', false)
            ->assertSee('<meta property="og:locale:alternate" content="tr_TR">', false)
            ->assertSee('Menemen (Turkish Scrambled Eggs with Tomatoes and Peppers)')
            ->assertSee('Ingredients')
            ->assertSee('Method');

        $content = (string) $response->getContent();
        $this->assertSame(1, substr_count($content, '<h1'));
        $this->assertStringContainsString('"@type":"Recipe"', $content);
        $this->assertStringContainsString('"inLanguage":"en"', $content);
        $this->assertStringContainsString('"recipeYield":"2 servings"', $content);

        $this->get($turkishUrl)
            ->assertOk()
            ->assertSee('<html lang="tr"', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="'.$englishUrl.'">', false)
            ->assertSee('<link rel="alternate" hreflang="tr" href="'.$turkishUrl.'">', false);
    }

    public function test_english_pages_render_with_reciprocal_static_alternates(): void
    {
        app(ContentTranslationImporter::class)->import('en');

        $this->get('/en')->assertOk()
            ->assertSee('Cypriot &amp; Mediterranean', false)
            ->assertSee('<link rel="alternate" hreflang="tr" href="'.route('home').'">', false)
            ->assertSee('Skip to content');
        $this->get('/en/recipes')->assertOk()->assertSee('50</strong> recipes found', false);
        $this->get('/en/recipes?q=halloumi')->assertOk()->assertSee('noindex,follow', false)->assertDontSee('<link rel="alternate" hreflang', false);
        $this->get('/en/blog')->assertOk()->assertSee('Cooking Step by Step with Cooking Mode');
        $this->get('/en/about')->assertOk()->assertSee('<link rel="alternate" hreflang="tr" href="'.route('information.show', 'hakkimizda').'">', false);

        $this->get('/')->assertOk()->assertSee('<link rel="alternate" hreflang="en" href="'.route('en.home').'">', false);
        $this->get('/tarifler')->assertOk()->assertSee('<link rel="alternate" hreflang="en" href="'.route('en.recipes.index').'">', false);
    }

    public function test_english_blog_post_has_article_and_faq_schema(): void
    {
        app(ContentTranslationImporter::class)->import('en');
        $post = BlogPost::query()->where('slug', 'ev-olculeri-rehberi')->firstOrFail();
        $translation = BlogPostTranslation::query()->where('blog_post_id', $post->id)->where('locale', 'en')->firstOrFail();

        $content = (string) $this->get(route('en.blog.show', $translation->slug))
            ->assertOk()
            ->assertSee('Su bardağı (water glass): about 200 ml')
            ->assertSee('<link rel="alternate" hreflang="tr" href="'.route('blog.show', $post).'">', false)
            ->getContent();

        $this->assertStringContainsString('"@type":"BlogPosting"', $content);
        $this->assertStringContainsString('"@type":"FAQPage"', $content);
        $this->assertStringContainsString('"inLanguage":"en"', $content);
    }

    public function test_translation_never_publishes_an_unpublished_turkish_recipe(): void
    {
        app(ContentTranslationImporter::class)->import('en');
        $recipe = Recipe::query()->where('slug', 'lahmacun')->firstOrFail();
        $translation = RecipeTranslation::query()->where('recipe_id', $recipe->id)->where('locale', 'en')->firstOrFail();
        $recipe->update(['status' => 'draft']);

        $this->get(route('en.recipes.show', $translation->slug))->assertNotFound();
        $this->get('/sitemaps/en.xml')->assertOk()->assertDontSee($translation->slug);
        $this->get('/en/recipes/not-a-real-recipe')->assertNotFound()->assertSee('We couldn’t find that recipe or page')->assertSee('<html lang="en"', false);
    }

    public function test_sitemaps_advertise_the_english_edition_with_hreflang(): void
    {
        app(ContentTranslationImporter::class)->import('en');
        $translation = RecipeTranslation::query()->with('recipe')->where('locale', 'en')->firstOrFail();

        $this->get('/sitemap.xml')->assertOk()->assertSee('/sitemaps/en.xml');
        $this->get('/sitemaps/en.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('xmlns:xhtml="http://www.w3.org/1999/xhtml"', false)
            ->assertSee('<loc>'.route('en.recipes.show', $translation->slug).'</loc>', false)
            ->assertSee('hreflang="tr" href="'.route('recipes.show', (string) $translation->recipe?->slug).'"', false);
        $this->get('/sitemaps/recipes.xml')
            ->assertOk()
            ->assertSee('hreflang="en" href="'.route('en.recipes.show', $translation->slug).'"', false);
    }
}
