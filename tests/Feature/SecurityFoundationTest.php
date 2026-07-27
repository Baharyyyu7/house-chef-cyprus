<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Media\MediaStorageInterface;
use App\Services\Security\SafeUrlValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class SecurityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied_to_web_responses(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_safe_url_validator_blocks_private_and_unsafe_urls(): void
    {
        $validator = app(SafeUrlValidator::class);
        $this->assertSame('https://example.com/image.jpg', $validator->validate('https://example.com/image.jpg'));
        foreach (['http://127.0.0.1/', 'http://localhost/', 'file:///etc/passwd', 'https://user:pass@example.com/', 'https://example.com:8080/'] as $url) {
            try {
                $validator->validate($url);
                $this->fail('Unsafe URL was accepted: '.$url);
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_local_storage_rejects_non_images_and_uses_generated_paths(): void
    {
        Storage::fake('public');
        $storage = app(MediaStorageInterface::class);
        $stored = $storage->storePublicImage(UploadedFile::fake()->image('photo.jpg', 200, 100));
        Storage::disk('public')->assertExists($stored['path']);
        $this->assertStringStartsWith('media/', $stored['path']);

        $this->expectException(ValidationException::class);
        $storage->storePublicImage(UploadedFile::fake()->create('shell.php', 10, 'application/x-php'));
    }

    public function test_media_maintenance_commands_are_safe_dry_runs(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $this->artisan('media:scan-orphans', ['--json' => true])->assertSuccessful();
        $this->artisan('media:cleanup-temporary', ['--json' => true])->assertSuccessful();
    }
}
