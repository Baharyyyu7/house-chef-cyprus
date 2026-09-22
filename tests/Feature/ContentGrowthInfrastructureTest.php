<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\AuditContentGrowthJob;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class ContentGrowthInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_change_dispatches_persistent_application_job(): void
    {
        Queue::fake();
        $recipe = Recipe::query()->create(['title' => 'Recipe', 'slug' => 'growth-recipe', 'status' => 'draft']);

        Queue::assertPushed(AuditContentGrowthJob::class, fn ($job) =>
            $job->contentId === $recipe->id && $job->connection === 'database' && $job->afterCommit
        );
    }

    public function test_audit_opens_a_translation_task_without_publishing(): void
    {
        Queue::fake();
        config(['locales.default' => 'tr', 'locales.supported' => [
            'tr' => ['enabled' => true], 'en' => ['enabled' => false],
        ]]);
        $recipe = Recipe::query()->create(['title' => 'Recipe', 'slug' => 'growth-draft', 'status' => 'draft']);
        (new AuditContentGrowthJob('recipe', (int) $recipe->id))->handle();

        $this->assertDatabaseHas('content_opportunities', [
            'normalized_key' => 'translation-recipe-'.$recipe->id.'-en',
            'kind' => 'translation_gap',
            'status' => 'open',
        ]);
        $this->assertSame('draft', $recipe->fresh()->status);
    }

    public function test_audit_writes_no_integrity_report_per_content_item(): void
    {
        // Her içerik kaydı için rapor yazılınca tek günde 9.899 satır birikti
        // ve muhafız günlüğünü boğdu. Manşet sayı artık
        // content:translation-readiness muhafızında, tek satır olarak duruyor.
        Queue::fake();
        $recipe = Recipe::query()->create(['title' => 'Recipe', 'slug' => 'growth-report', 'status' => 'draft']);

        (new AuditContentGrowthJob('recipe', (int) $recipe->id))->handle();

        $this->assertDatabaseMissing('integrity_reports', ['guard' => 'seo:content-growth']);
    }

    public function test_closed_content_gets_no_translation_task_and_open_ones_resolve(): void
    {
        Queue::fake();
        config(['locales.default' => 'tr', 'locales.supported' => [
            'tr' => ['enabled' => true], 'en' => ['enabled' => true],
        ]]);
        $recipe = Recipe::query()->create(['title' => 'Recipe', 'slug' => 'growth-closed', 'status' => 'published']);
        (new AuditContentGrowthJob('recipe', (int) $recipe->id))->handle();
        $this->assertDatabaseHas('content_opportunities', ['normalized_key' => 'translation-recipe-'.$recipe->id.'-en', 'status' => 'open']);

        $recipe->seoMetadata()->create(['robots' => 'noindex,follow']);
        (new AuditContentGrowthJob('recipe', (int) $recipe->fresh()->id))->handle();

        $this->assertDatabaseHas('content_opportunities', [
            'normalized_key' => 'translation-recipe-'.$recipe->id.'-en',
            'status' => 'resolved',
        ]);
    }
}
