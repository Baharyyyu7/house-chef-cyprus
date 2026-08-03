<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class GoogleSearchConsoleVerificationTest extends TestCase
{
    public function test_google_search_console_html_verification_file_is_in_the_public_web_root(): void
    {
        $path = public_path('google5a6bbbf57c0b2023.html');

        $this->assertFileExists($path);
        $this->assertSame(
            'google-site-verification: google5a6bbbf57c0b2023.html',
            trim((string) file_get_contents($path)),
        );
    }
}
