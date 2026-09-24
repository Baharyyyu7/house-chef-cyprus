<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\AuditContentGrowthJob;
use App\Models\Recipe;
use App\Services\Integrity\IntegrityReportLog;
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

    public function test_audit_records_missing_translations_without_publishing(): void
    {
        Queue::fake();
        config(['locales.default' => 'tr', 'locales.supported' => [
            'tr' => ['enabled' => true], 'en' => ['enabled' => false],
        ]]);
        $recipe = Recipe::query()->create(['title' => 'Recipe', 'slug' => 'growth-draft', 'status' => 'draft']);
        (new AuditContentGrowthJob('recipe', (int) $recipe->id))->handle(app(IntegrityReportLog::class));

        $this->assertDatabaseHas('integrity_reports', ['guard' => 'seo:content-growth', 'status' => 'incomplete', 'headline' => 1]);
        $this->assertSame('draft', $recipe->fresh()->status);
        $this->assertFalse(config('locales.supported.en.enabled'));
    }
}
