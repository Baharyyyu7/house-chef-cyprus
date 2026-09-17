<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class LlmsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_returns_ok_with_plain_text(): void
    {
        Recipe::query()->create([
            'title' => 'Kıbrıs Şeftali Kebabı',
            'slug' => 'kibris-seftali-kebabi',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'servings' => 4,
            'description' => 'Geleneksel Kıbrıs lezzeti.',
        ]);

        $response = $this->get('/llms.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus')
            ->assertSee('Kıbrıs Şeftali Kebabı')
            ->assertSee('/feed')
            ->assertSee('/llms-full.txt');
    }

    public function test_llms_full_txt_returns_ok_with_complete_corpus(): void
    {
        Recipe::query()->create([
            'title' => 'Geleneksel Molehiya',
            'slug' => 'geleneksel-molehiya',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 30,
            'cook_minutes' => 50,
            'servings' => 6,
            'description' => 'Molehiya otu ve kuzu eti ile.',
        ]);

        $response = $this->get('/llms-full.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus - Kapsamlı İçerik Kataloğu')
            ->assertSee('Geleneksel Molehiya');
    }

    #[DataProvider('englishCatalogues')]
    public function test_english_catalogue_matches_translated_public_pages(string $path): void
    {
        $cuisine = Cuisine::query()->create(['name' => 'Kibris Mutfagi', 'slug' => 'kibris-mutfagi']);
        $recipe = Recipe::query()->create([
            'cuisine_id' => $cuisine->id, 'title' => 'Turkce tarif', 'slug' => 'kibris-seftali-kebabi',
            'excerpt' => 'Turkce ozet', 'description' => 'Turkce aciklama',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        foreach (['title' => 'Editor recipe title', 'excerpt' => 'Editor recipe summary', 'description' => 'Editor recipe description'] as $field => $value) {
            $recipe->translations()->create(['locale' => 'en', 'field' => $field, 'value' => $value]);
        }

        $post = BlogPost::query()->create([
            'title' => 'Turkce rehber', 'slug' => 'editor-guide', 'excerpt' => 'Turkce ozet', 'body' => 'Turkce govde',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        foreach (['title' => 'Editor guide title', 'excerpt' => 'Editor guide summary', 'body' => 'Editor guide body'] as $field => $value) {
            $post->translations()->create(['locale' => 'en', 'field' => $field, 'value' => $value]);
        }

        $this->get($path)->assertOk()
            ->assertSee('Editor recipe title', false)
            ->assertSee('Editor recipe description', false)
            ->assertSee(url('/en/recipes/kibris-seftali-kebabi'), false)
            ->assertDontSee(url('/tarifler/kibris-seftali-kebabi'), false)
            ->assertSee('Editor guide title', false)
            ->assertSee('Editor guide summary', false)
            ->assertSee(url('/en/blog/editor-guide'), false)
            ->assertDontSee(url('/blog/editor-guide'), false);

        $this->get('/en/recipes/kibris-seftali-kebabi')->assertOk()->assertSee('Editor recipe title');
        $this->get('/en/blog/editor-guide')->assertOk()->assertSee('Editor guide title');
    }

    #[DataProvider('englishCatalogues')]
    public function test_english_catalogue_excludes_unavailable_content(string $path): void
    {
        $cuisine = Cuisine::query()->create(['name' => 'Kibris Mutfagi', 'slug' => 'kibris-mutfagi']);
        foreach (['untranslated', 'partial-step', 'draft', 'unverified'] as $slug) {
            $recipe = Recipe::query()->create([
                'cuisine_id' => $cuisine->id, 'title' => 'Turkce tarif', 'slug' => $slug,
                'excerpt' => 'Turkce ozet', 'description' => 'Turkce aciklama',
                'status' => $slug === 'draft' ? 'draft' : 'published',
                'verification_status' => $slug === 'unverified' ? 'pending' : 'verified',
            ]);
            if ($slug !== 'untranslated') {
                foreach (['title', 'excerpt', 'description'] as $field) {
                    $recipe->translations()->create(['locale' => 'en', 'field' => $field, 'value' => 'English '.$field]);
                }
            }
            if ($slug === 'partial-step') {
                $recipe->steps()->create(['position' => 1, 'title' => 'Pisirme', 'instruction' => 'Turkce pisirme adimi']);
            }
        }

        foreach (['untranslated', 'partial', 'draft', 'future'] as $slug) {
            $post = BlogPost::query()->create([
                'title' => 'Turkce rehber', 'slug' => $slug, 'excerpt' => 'Turkce ozet', 'body' => 'Turkce govde',
                'status' => $slug === 'draft' ? 'draft' : 'published',
                'published_at' => $slug === 'future' ? now()->addDay() : now()->subDay(),
            ]);
            if ($slug !== 'untranslated') {
                foreach ($slug === 'partial' ? ['title', 'excerpt'] : ['title', 'excerpt', 'body'] as $field) {
                    $post->translations()->create(['locale' => 'en', 'field' => $field, 'value' => 'English '.$field]);
                }
            }
        }

        $this->get($path)->assertOk()
            ->assertDontSee('/tarifler/', false)
            ->assertDontSee('/blog/', false);
    }

    #[DataProvider('englishCatalogues')]
    public function test_disabled_english_catalogue_is_unavailable(string $path): void
    {
        config()->set('locales.supported.en.enabled', false);

        $this->get($path)->assertNotFound();
    }

    public static function englishCatalogues(): array
    {
        return [['/en/llms.txt'], ['/en/llms-full.txt']];
    }
}
