<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class IconRegistryTest extends TestCase
{
    #[DataProvider('semanticIcons')]
    public function test_semantic_icons_do_not_fall_back_to_the_sparkle_glyph(string $name, string $expectedMarkup): void
    {
        $svg = Blade::render('<x-ui.icon name="'.$name.'" />');

        $this->assertStringContainsString($expectedMarkup, $svg);
        $this->assertStringNotContainsString('m12 3 1.5 5.5L19 10l-5.5 1.5L12 17l-1.5-5.5L5 10l5.5-1.5L12 3Z', $svg);
    }

    public static function semanticIcons(): array
    {
        return [
            'back' => ['arrow-left', 'M19 12H5'],
            'check circle' => ['check-circle', 'cx="12" cy="12" r="9"'],
            'clock' => ['clock', 'M12 7v5l3 2'],
            'pause' => ['pause', 'width="4" height="14"'],
            'play' => ['play', 'M8 5l11 7-11 7Z'],
            'home' => ['home', 'M3 11l9-8 9 8'],
            'refresh' => ['refresh-cw', 'M20 11A8 8 0 0 0 6.3 6.3L4 8.5'],
            'undo' => ['undo', 'M9 7 4 12l5 5'],
        ];
    }
}
