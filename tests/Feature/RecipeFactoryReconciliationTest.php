<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentFactoryReview;
use App\Models\Recipe;
use App\Services\RecipeFactoryReconciliationService;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeFactoryReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_audits_the_catalogue_without_writing_or_publishing_by_default(): void
    {
        $this->seed(HouseChefSeeder::class);

        $report = app(RecipeFactoryReconciliationService::class)->reconcile();

        $this->assertSame(50, $report['audited']);
        $this->assertSame(0, ContentFactoryReview::query()->where('stage', 'recipe_factory_reconciliation')->count());
        $this->assertFalse($report['auto_publish']);
        $this->assertTrue($report['human_approval_required']);
        $this->assertSame(50, Recipe::query()->where('status', 'published')->count());
    }

    public function test_reconciliation_writes_idempotent_review_evidence_without_mutating_recipe_publication(): void
    {
        $this->seed(HouseChefSeeder::class);

        $first = app(RecipeFactoryReconciliationService::class)->reconcile(true);
        $reviewIds = ContentFactoryReview::query()
            ->where('stage', 'recipe_factory_reconciliation')
            ->pluck('public_id', 'entity_public_id')
            ->all();
        $second = app(RecipeFactoryReconciliationService::class)->reconcile(true);

        $this->assertSame(50, $first['audited']);
        $this->assertSame(50, $second['audited']);
        $this->assertCount(50, $reviewIds);
        $this->assertSame($reviewIds, ContentFactoryReview::query()
            ->where('stage', 'recipe_factory_reconciliation')
            ->pluck('public_id', 'entity_public_id')
            ->all());
        $this->assertSame(50, Recipe::query()->where('status', 'published')->count());
        $this->assertSame(50, Recipe::query()->where('verification_status', 'verified')->count());
    }
}
