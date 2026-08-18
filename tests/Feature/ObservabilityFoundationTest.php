<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ObservabilityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoints_are_safe_and_requests_receive_a_correlation_id(): void
    {
        $this->get('/health')->assertOk()->assertHeader('X-Request-ID')->assertJsonStructure(['status', 'checked_at']);
        $this->getJson('/api/v1/health')->assertOk()->assertHeader('X-Request-ID')->assertJsonStructure(['data' => ['status', 'checked_at'], 'request_id'])->assertJsonMissing(['components']);
        $this->getJson('/api/v1/version')->assertOk()->assertJsonPath('data.locale', 'tr');
    }
}

