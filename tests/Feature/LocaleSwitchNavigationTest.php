<?php

namespace Tests\Feature;

use App\Services\Seo\LocaleUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_languages_preserves_filters_without_duplicate_prefixes(): void
    {
        config(['locales.supported.en.enabled' => true]);
        $this->post('/dil', ['locale' => 'en', 'return_to' => '/tarifler?q=hellim&page=2'])
            ->assertRedirect(url('/en/recipes?q=hellim&page=2'));
        $this->post('/dil', ['locale' => 'tr', 'return_to' => '/en?sort=newest'])
            ->assertRedirect(url('/').'?sort=newest');
    }

    public function test_query_strings_are_not_mistaken_for_locale_prefixes(): void
    {
        $urls = app(LocaleUrlService::class);
        $this->assertSame(url('/en').'?q=test#results', $urls->urlFor('en', '/en?q=test#results'));
        $this->assertSame(url('/').'?q=test', $urls->urlFor('tr', '/en?q=test'));
    }

    public function test_encoded_external_return_paths_cannot_escape_the_site(): void
    {
        $this->post('/dil', ['locale' => 'tr', 'return_to' => '/%5cexample.com'])
            ->assertRedirect(url('/'));
    }

    public function test_public_urls_keep_their_language_regardless_of_session_preferences(): void
    {
        config(['locales.supported.en.enabled' => true]);
        $this->withSession(['hc_locale' => 'en'])->get('/tarifler')
            ->assertOk()->assertSee('lang="tr"', false);
        $this->withSession(['hc_locale' => 'tr'])->get('/en/recipes')
            ->assertOk()->assertSee('lang="en"', false)
            ->assertSee('href="'.url('/en/blog').'"', false)
            ->assertSee('href="'.url('/tarifler').'"', false);
        $this->get('/tarifler')->assertOk()->assertDontSee('href="'.url('/en/blog').'"', false);
    }
}
