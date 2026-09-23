<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicNavigationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_never_links_to_a_disabled_locale(): void
    {
        config()->set('locales.supported.de.enabled', false);

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('href="'.url('/de'), $html);
    }

    public function test_home_uses_default_locale_urls_for_duration_and_difficulty_links(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('recipes.index', ['max_minutes' => 20]).'"', $html);
        $this->assertStringContainsString('href="'.route('recipes.index', ['difficulty' => 'easy']).'"', $html);
        $this->assertStringNotContainsString('href="'.url('/de/sure/20-dakika').'"', $html);
        $this->assertStringNotContainsString('href="'.url('/de/zorluk/kolay').'"', $html);
    }
}
