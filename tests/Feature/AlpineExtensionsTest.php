<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class AlpineExtensionsTest extends TestCase
{
    public function test_the_app_registers_the_collapse_extension_used_by_blade_views(): void
    {
        $source = (string) file_get_contents(base_path('resources/js/app.js'));

        $this->assertStringContainsString("from '@alpinejs/collapse'", $source);
        $this->assertStringContainsString('Alpine.plugin(collapse)', $source);
    }
}
