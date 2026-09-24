<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * İçe aktarıcı, kaynak görseli olmayan her yayındaki yazıda FAILURE dönüyordu.
 * Deploy scripti bu koda baktığı için admin panelinden yayınlanan görselsiz tek
 * bir yazı bütün sürümleri bloke ediyordu; oysa site zaten onaylı görsel yoksa
 * yer tutucu gösteriyor. Eksik kaynak artık uyarı, bozuk işleme ise hata.
 */
final class BlogMediaImportDeployGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_published_post_without_a_source_image_does_not_block_the_deploy(): void
    {
        $this->publish('gorseli-olmayan-yazi');

        $this->artisan('content:import-approved-synthetic-blog-media', [
            '--source-directory' => $this->emptySourceDirectory(),
        ])->assertSuccessful();
    }

    public function test_the_missing_post_is_still_reported(): void
    {
        $this->publish('gorseli-olmayan-yazi');

        $this->artisan('content:import-approved-synthetic-blog-media', [
            '--source-directory' => $this->emptySourceDirectory(),
        ])->expectsOutputToContain('gorseli-olmayan-yazi')->assertSuccessful();
    }

    public function test_a_missing_source_directory_still_fails(): void
    {
        $this->artisan('content:import-approved-synthetic-blog-media', [
            '--source-directory' => '/tmp/hc-olmayan-dizin-'.uniqid(),
        ])->assertFailed();
    }

    private function publish(string $slug): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Kapı Testi',
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    private function emptySourceDirectory(): string
    {
        $directory = storage_path('framework/testing/blog-synthetic-'.uniqid());
        mkdir($directory, 0o755, true);

        return $directory;
    }
}
