<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Portekizce metinler, malzeme sözlüğü ve og:locale Brezilya'ya göre.
 * Bayrak 12 Ekim 2026'da site sahibinin kararıyla 🇧🇷 oldu.
 */
final class PortugueseLocaleIsBrazilianTest extends TestCase
{
    public function test_portuguese_is_presented_as_brazilian(): void
    {
        $this->assertSame('🇧🇷', config('locales.supported.pt.flag'));
        $this->assertSame('pt_BR', config('locales.supported.pt.og_locale'));
    }
}
