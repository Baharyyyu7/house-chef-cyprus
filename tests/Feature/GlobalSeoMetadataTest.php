<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentTranslation;
use App\Models\Recipe;
use App\Models\SeoMetadata;
use App\Models\User;
use App\Services\Seo\BlogIndexability;
use App\Services\Seo\LocaleUrlService;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Http\Request;
use Tests\TestCase;

final class GlobalSeoMetadataTest extends TestCase
{
    public function test_record_metadata_cannot_reopen_a_query_page_to_indexing(): void
    {
        config()->set('seo.indexing_enabled', true);
        $recipe = (new Recipe)->setRelation('seoMetadata', new SeoMetadata(['robots' => 'index,follow']));
        $metadata = app(SeoMetadataService::class)->for($recipe, 'Title', 'Description', url('/tarifler/example'), 'noindex,follow');

        $this->assertNotContains('index', explode(',', $metadata['robots']));
        $this->assertContains('noindex', explode(',', $metadata['robots']));
    }

    public function test_blog_quality_gate_preserves_editorial_robots_and_global_switch(): void
    {
        config()->set('seo.indexing_enabled', true);
        $post = new BlogPost(['body' => str_repeat('Visible article text. ', 120), 'fact_check_status' => 'passed', 'originality_status' => 'passed']);
        $post->setRelation('seoMetadata', new SeoMetadata(['robots' => 'noindex,nofollow']));
        $this->assertSame('noindex,nofollow', app(BlogIndexability::class)->robots($post));

        $post->setRelation('seoMetadata', null);
        config()->set('seo.indexing_enabled', false);
        $this->assertSame('noindex,nofollow', app(BlogIndexability::class)->robots($post));
    }

    public function test_translated_recipe_description_uses_the_full_localized_text(): void
    {
        app()->setLocale('en');
        $recipe = (new Recipe)->setRelation('seoMetadata', null)->setRelation('translations', collect([
            new ContentTranslation(['locale' => 'en', 'field' => 'title', 'value' => 'Rice']),
            new ContentTranslation(['locale' => 'en', 'field' => 'excerpt', 'value' => 'Short teaser.']),
            new ContentTranslation(['locale' => 'en', 'field' => 'description', 'value' => 'Cook the rice gently and leave it covered to rest before serving.']),
        ]));
        $metadata = app(SeoMetadataService::class)->for($recipe, 'Turkish title', 'Turkish description', url('/tarifler/rice'));

        $this->assertSame('Cook the rice gently and leave it covered to rest before serving.', $metadata['description']);
        $this->assertSame(url('/en/recipes/rice'), $metadata['canonical']);
    }

    public function test_canonical_overrides_require_an_absolute_http_url_without_credentials_or_fragment(): void
    {
        $fallback = url('/blog/example');
        foreach (['ftp://localhost/blog/example', '//localhost/blog/example', 'https://user:pass@localhost/blog/example', 'http://localhost/blog/example#section', 'https://elsewhere.example/blog/example'] as $candidate) {
            $post = (new BlogPost)->setRelation('seoMetadata', new SeoMetadata(['canonical_override' => $candidate]));
            $this->assertSame($fallback, app(SeoMetadataService::class)->for($post, 'Title', 'Description', $fallback)['canonical']);
        }
    }

    public function test_blog_gate_measures_visible_text_in_the_requested_language(): void
    {
        $post = new BlogPost(['body' => str_repeat('Visible article text. ', 120), 'fact_check_status' => 'passed', 'originality_status' => 'passed']);
        $post->setRelation('translations', collect([new ContentTranslation(['locale' => 'en', 'field' => 'body', 'value' => 'Short translation.'])]));
        app()->setLocale('en');
        $this->assertContains('ince', app(BlogIndexability::class)->engeller($post));

        app()->setLocale('tr');
        $post->body = '<p data-padding="'.str_repeat('x', 2200).'">Short text.</p>';
        $this->assertContains('ince', app(BlogIndexability::class)->engeller($post));
    }

    public function test_article_schema_matches_the_visible_person_author(): void
    {
        $post = (new BlogPost(['title' => 'Cooking', 'slug' => 'cooking']))->setRelation('author', new User(['name' => 'Actual Editor']));
        $article = app(SeoSchemaService::class)->article($post)['@graph'][0];
        $this->assertSame(['@type' => 'Person', 'name' => 'Actual Editor'], $article['author']);
        $this->assertSame('Organization', $article['publisher']['@type']);
    }

    public function test_recipe_is_not_identified_as_an_encyclopedia_article_about_an_ingredient(): void
    {
        $recipe = (new Recipe(['title' => 'Halloumi toast', 'slug' => 'hellim-tost']))
            ->setRelation('steps', collect())->setRelation('cuisine', null)->setRelation('category', null);
        $node = app(SeoSchemaService::class)->recipe($recipe, ['Halloumi'], 'https://example.com/toast.jpg')['@graph'][0];
        $this->assertArrayNotHasKey('sameAs', $node);
        $this->assertSame('Halloumi', $node['about']['name']);
    }

    public function test_hreflang_sets_are_reciprocal_and_omit_incomplete_translations(): void
    {
        config()->set('locales.supported.en.enabled', true);
        $record = new class {
            public function hasTranslation(string $locale): bool
            {
                return in_array($locale, ['tr', 'en'], true);
            }
        };
        $service = app(LocaleUrlService::class);
        $turkish = $service->alternates(Request::create('/blog/example'), $record);
        $this->assertSame($turkish, $service->alternates(Request::create('/en/blog/example'), $record));
        $this->assertCount(3, $turkish);
        $this->assertSame(['tr', 'en', 'x-default'], array_column($turkish, 'hreflang'));
    }

    public function test_recipe_ctr_hook_prepends_time_and_servings_to_meta_description(): void
    {
        app()->setLocale('tr');
        $recipe = new Recipe([
            'title' => 'Kıbrıs Köftesi',
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'rest_minutes' => 0,
            'servings' => 4,
        ]);
        $recipe->setRelation('seoMetadata', null);

        $metadata = app(SeoMetadataService::class)->for($recipe, 'Kıbrıs Köftesi', 'Geleneksel lezzetli Kıbrıs köftesi tarifi.', url('/tarifler/kibris-koftesi'));

        $this->assertStringStartsWith('⏱ 45 dk • 4 Kişilik • Geleneksel lezzetli Kıbrıs köftesi tarifi.', $metadata['description']);
    }
}
