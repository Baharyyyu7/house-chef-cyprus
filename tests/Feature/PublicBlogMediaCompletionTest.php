<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Media;
use App\Services\Media\PublicBlogMediaCompletionService;
use Database\Seeders\HouseChefSeeder;
use Database\Seeders\ProductionBlogCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PublicBlogMediaCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([HouseChefSeeder::class, ProductionBlogCatalogueSeeder::class]);
    }

    public function test_all_published_seed_blogs_are_truthfully_classified_as_gradient_placeholders(): void
    {
        $report = app(PublicBlogMediaCompletionService::class)->audit();

        $this->assertSame(20, $report['published_blogs_audited']);
        $this->assertSame(20, $report['gradient_placeholders']);
        $this->assertSame(0, $report['approved_media']);
        $this->assertFalse($report['auto_publish']);
        $this->assertSame('NOT_RECORDED', $report['blogs'][0]['identity_confirmation']['state']);
    }

    public function test_only_complete_approved_blog_media_is_ready(): void
    {
        Storage::fake('public');
        $post = BlogPost::query()->publiclyVisible()->firstOrFail();
        Storage::disk('public')->put('media/blog/approved.png', 'image-bytes');
        $media = $post->media()->create($this->approvedAttributes('media/blog/approved.png'));
        $post->update(['hero_media_id' => $media->id]);

        $row = collect(app(PublicBlogMediaCompletionService::class)->audit()['blogs'])
            ->firstWhere('blog_public_id', $post->public_id);

        $this->assertSame('READY', $row['classification']);
        $this->assertSame([], $row['variants']['missing']);
        $this->assertFalse($row['duplicate_image']);
    }

    /** @return array<string, mixed> */
    private function approvedAttributes(string $path): array
    {
        $profiles = [
            'hero' => [1600, 900], 'card' => [800, 600], 'square' => [1080, 1080],
            'portrait' => [1080, 1350], 'open_graph' => [1200, 630], 'thumbnail' => [400, 300],
        ];
        $variants = [];
        foreach ($profiles as $name => [$width, $height]) {
            $variants[$name] = ['name' => $name, 'width' => $width, 'height' => $height, 'jpeg' => "media/blog/{$name}.jpg", 'webp' => "media/blog/{$name}.webp"];
        }

        return [
            'kind' => 'editorial_synthetic_illustration', 'disk' => 'public', 'path' => $path, 'mime_type' => 'image/png', 'size' => 100,
            'visibility' => 'public', 'media_status' => 'approved_real_media', 'verification_status' => 'verified',
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'license' => 'House Chef Cyprus approved synthetic editorial illustration',
            'source_name' => 'House Chef Cyprus approved synthetic editorial media', 'attribution' => 'Sentetik editoryal illüstrasyon',
            'fidelity_status' => 'strong_match', 'alt_text' => 'Konuya özel editoryal görsel', 'width' => 1600, 'height' => 900,
            'checksum_sha256' => hash('sha256', $path), 'responsive_variants' => $variants,
        ];
    }
}
