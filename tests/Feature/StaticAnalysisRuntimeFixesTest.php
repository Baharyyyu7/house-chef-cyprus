<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Media\ApprovedMediaImportService;
use App\Services\Seo\DistribbIntegrationService;
use App\Services\Seo\InternalLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

/**
 * phpstan'ın method.notFound / method.nonObject olarak yakaladığı, çalışma
 * anında fatal hata veren üç yol. Her biri yalnız belirli bir dalda
 * çalıştığı için test takımı onları hiç görmemişti.
 */
final class StaticAnalysisRuntimeFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_suggestions_return_an_array_instead_of_crashing(): void
    {
        $tarif = $this->tarif('hellimli-makarna');
        $this->tarif('hellimli-borek');

        $oneriler = app(InternalLinkService::class)->recipeSuggestionsFor($tarif, 'tr');

        $this->assertIsArray($oneriler);
    }

    public function test_distribb_status_reads_integrations_as_an_array(): void
    {
        config()->set('seo.distribb.api_key', 'test-anahtari');
        config()->set('seo.distribb.enabled', true);
        Http::fake([
            '*/integrations' => Http::response([['id' => 'cms-1']]),
            '*' => Http::response([]),
        ]);

        $durum = (new DistribbIntegrationService('test-anahtari'))->getStatus();

        $this->assertTrue($durum['cms_connected']);
    }

    public function test_media_import_recognises_an_unpublished_blog_post(): void
    {
        $yazi = BlogPost::query()->create([
            'title' => 'Taslak yazı',
            'slug' => 'taslak-yazi',
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'draft',
        ]);

        $metot = new ReflectionMethod(ApprovedMediaImportService::class, 'contentExistsUnpublished');

        $this->assertTrue($metot->invoke(app(ApprovedMediaImportService::class), 'blog', $yazi->public_id, 'blog--taslak-yazi.jpg'));
        $this->assertFalse($metot->invoke(app(ApprovedMediaImportService::class), 'blog', 'yok', 'blog--olmayan-yazi.jpg'));
    }

    private function tarif(string $slug): Recipe
    {
        return Recipe::query()->create([
            'title' => $slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Yeterince uzun bir açıklama metni burada duruyor.',
            'servings' => 4,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
