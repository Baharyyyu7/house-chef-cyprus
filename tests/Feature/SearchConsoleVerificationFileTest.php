<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SearchConsoleVerificationFileTest extends TestCase
{
    private const DOSYA = 'google715e7aec24977e0f.html';

    public function test_the_verification_file_is_present(): void
    {
        // Google: "doğrulama başarılı olduktan sonra bile dosyayı kaldırmayın."
        // Dosya bir deploy'da düşerse Search Console mülkiyeti sessizce
        // doğrulanmamış duruma geçer ve bütün ölçüm kesilir - hiçbir hata
        // görünmeden. Bu test onu görünür kılıyor.
        $this->assertFileExists(
            public_path(self::DOSYA),
            'Search Console doğrulama dosyası silinmiş. Silinirse mülkiyet doğrulaması düşer.'
        );
    }

    public function test_the_file_still_carries_the_token_google_expects(): void
    {
        // Dosyanın var olması yetmiyor; içeriği de belirli olmak zorunda.
        // Boşaltılmış ya da üzerine yazılmış bir dosya doğrulamayı bozar.
        $this->assertStringContainsString(
            'google-site-verification: '.self::DOSYA,
            (string) file_get_contents(public_path(self::DOSYA))
        );
    }

    public function test_the_web_root_sync_script_copies_verification_files(): void
    {
        // Web kökü release'in public klasörüne bağlı değil. Betik kök
        // dosyaları kopyalamazsa doğrulama dosyası ilk deploy'da kaybolur.
        $betik = (string) file_get_contents(base_path('scripts/sync-web-root-assets.sh'));

        $this->assertStringContainsString('public/google*.html', $betik);
        $this->assertStringContainsString('robots.txt', $betik);
    }
}
