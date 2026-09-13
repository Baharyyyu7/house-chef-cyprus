<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\RunGrowthStageJob;
use App\Models\IntegrityReport;
use App\Services\Seo\GrowthSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class GrowthSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_is_persistent_and_idempotent_per_day(): void
    {
        Queue::fake();
        Cache::flush();
        $system = app(GrowthSystem::class);
        $this->assertSame(7, $system->dispatch());
        $this->assertSame(0, $system->dispatch());
        Queue::assertPushed(RunGrowthStageJob::class, 7);
        $this->assertSame(7, IntegrityReport::query()->where('status', 'queued')->count());
    }

    public function test_disabled_provider_is_not_marked_successful(): void
    {
        Http::preventStrayRequests();
        config(['seo.indexnow.enabled' => false]);
        $report = IntegrityReport::query()->create(['guard' => 'seo:system:indexnow', 'status' => 'queued', 'payload' => ['stage' => 'indexnow']]);
        (new RunGrowthStageJob((int) $report->id))->handle(app(GrowthSystem::class));
        $this->assertSame('attention', $report->fresh()->status);
        $this->assertSame(0, $report->fresh()->payload['result']['accepted_urls']);
        Http::assertNothingSent();
    }
}
