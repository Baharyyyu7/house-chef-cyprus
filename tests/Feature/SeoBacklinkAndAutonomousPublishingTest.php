<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\User;
use App\Services\Media\AutonomousMediaGateService;
use App\Services\Seo\AutonomousPublishingGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoBacklinkAndAutonomousPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_giris_redirects(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/yonetim');
        $this->assertEquals(301, $response->getStatusCode());

        $responseGiris = $this->get('/giris');
        $responseGiris->assertRedirect('/login');
        $this->assertEquals(301, $responseGiris->getStatusCode());
    }

    public function test_admin_can_view_and_manage_backlink_opportunities(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $opportunity = ContentOpportunity::query()->create([
            'kind' => 'backlink_prospect',
            'title' => 'Test Cyprus Food Heritage Prospect',
            'normalized_key' => 'test-cyprus-prospect-123',
            'priority' => 90,
            'status' => 'open',
            'evidence' => [
                'target_domain' => 'test-culinary-site.com',
                'target_url' => 'https://test-culinary-site.com/cyprus-cuisine',
                'authority_score' => 88,
                'pitch_draft_tr' => 'Merhaba, test mesajıdır.',
                'pitch_draft_en' => 'Hello, this is a test pitch.',
            ],
        ]);

        $response = $this->actingAs($admin)->get('/yonetim/seo/firsatlar?tab=backlinks');
        $response->assertOk();
        $response->assertSee('Test Cyprus Food Heritage Prospect');
        $response->assertSee('test-culinary-site.com');
        $response->assertSee('Otorite Skoru: 88/100');

        $updateResponse = $this->actingAs($admin)->post("/yonetim/seo/firsatlar/{$opportunity->id}/durum", [
            'status' => 'contacted',
        ]);
        $updateResponse->assertRedirect();

        $this->assertEquals('contacted', $opportunity->fresh()->status);
    }

    public function test_autonomous_publishing_gate_runs_safely(): void
    {
        $service = app(AutonomousPublishingGateService::class);
        $result = $service->run(blogLimit: 2, recipeLimit: 2, dryRun: true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('published_blogs', $result);
        $this->assertArrayHasKey('published_recipes', $result);
    }

    public function test_autonomous_media_gate_runs_safely(): void
    {
        $service = app(AutonomousMediaGateService::class);
        $result = $service->processEligibleCandidates(limit: 5);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('processed', $result);
        $this->assertArrayHasKey('approved', $result);
    }
}
